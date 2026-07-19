<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Site\Model;

defined('_JEXEC') or die;

use Akeeba\Component\ContactUs\Administrator\Mixin\CMSObjectWorkaroundTrait;
use Akeeba\Component\ContactUs\Administrator\Model\ItemModel as AdminItemModel;
use Akeeba\Component\ContactUs\Administrator\Table\ItemTable;
use Akeeba\Component\ContactUs\Site\Helper\Akismet;
use Exception;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\ParameterType;
use RuntimeException;
use stdClass;

class ItemModel extends AdminItemModel
{
	use CMSObjectWorkaroundTrait;

	public function getCaptchaObject($namespace = 'contactus')
	{
		try
		{
			/** @var SiteApplication $app */
			$app = Factory::getApplication();
		}
		catch (Exception $e)
		{
			return null;
		}

		$plugin = $app->getParams()->get('captcha', $app->get('captcha'));

		if ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null)
		{
			return null;
		}

		return Captcha::getInstance($plugin, ['namespace' => $namespace]);
	}

	protected function loadFormData()
	{
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_contactus.edit.item.data', []);

		if (empty($data))
		{
			$data = (object) $this->getItem();
		}

		$this->preprocessData('com_contactus.item', $data);

		return $data;
	}

	public function save($data)
	{
		// Make sure we are always saving into a new record by unsetting the ID data field and state.
		$table = $this->getTable();
		$key   = $table->getKeyName();

		if (isset($data[$key]))
		{
			unset($data[$key]);
		}

		$this->setState($this->getName() . '.id', 0);

		// Make sure we have explicit consent and the CAPTCHA is valid
		try
		{
			// Re-validate the selected category server-side. The selection field only *displays* enabled
			// categories the current user may access; without this check a crafted request could submit to a
			// disabled or access-restricted category, bypassing those gates.
			$this->assertValidCategory($data['contactus_category_id'] ?? null);

			$this->assertNotEmpty($data['consent'], 'COM_CONTACTUS_ITEM_ERR_CONSENT');

			$captcha = $this->getCaptchaObject();

			if (!is_null($captcha))
			{
				$this->assert($captcha->checkAnswer(Factory::getApplication()->getInput()->get('captcha', '', 'raw')), 'COM_CONTACTUS_ITEM_ERR_CAPTCHA');
			}
		}
		catch (Exception $e)
		{
			$this->setErrorOrThrow($e->getMessage());

			return false;
		}

		$ret = parent::save($data);

		// Actions after successful save
		if ($ret)
		{
			// Update the table object with the saved data
			$table->bind($data);
			$table->{$table->getKeyName()} = $this->getState($this->getName() . '.id');

			$apiKey = ComponentHelper::getParams('com_contactus')->get('akismet_api_key', '');
			$isSpam = Akismet::isSpamContent($apiKey, $table->fromname, $table->fromemail, $table->body);
			$this->setState('isSpam', $isSpam);

			// Load the category
			$db       = $this->getDatabase();
			$query    = (method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true))
				->select('*')
				->from($db->quoteName('#__contactus_categories'))
				->where($db->quoteName('contactus_category_id') . ' = :catid')
				->bind(':catid', $table->contactus_category_id);
			$category = $db->setQuery($query)->loadObject();

			// Don't email the admins if it's spam.
			if (!$isSpam)
			{
				$this->sendEmailToAdministrators($table, $category);
			}

			$this->sendEmailToUser($table, $category);
		}

		return $ret;
	}

	protected function prepareTable($table)
	{
		/** @var ItemTable $table */
		$this->assertNotEmpty($table->contactus_category_id, 'COM_CONTACTUS_ITEM_ERR_CATEGORY_EMPTY');
		$this->assertNotEmpty($table->fromname, 'COM_CONTACTUS_ITEM_ERR_FROMNAME_EMPTY');
		$this->assertNotEmpty($table->fromemail, 'COM_CONTACTUS_ITEM_ERR_FROMEMAIL_EMPTY');
		$this->assertNotEmpty($table->subject, 'COM_CONTACTUS_ITEM_ERR_SUBJECT_EMPTY');
		$this->assertNotEmpty($table->body, 'COM_CONTACTUS_ITEM_ERR_BODY_EMPTY');
	}

	/**
	 * Asserts that the submitted category exists, is enabled, and is accessible to the current user.
	 *
	 * This mirrors the filtering applied by the category selection field
	 * (\Akeeba\Component\ContactUs\Site\Field\ContactusCategoriesField) so that the access level, enabled
	 * state, and language gates enforced when rendering the form cannot be bypassed by a crafted request.
	 *
	 * @param   mixed  $categoryId  The submitted contact category ID
	 *
	 * @return  void
	 * @throws  RuntimeException  When the category is missing, disabled, or not accessible
	 */
	private function assertValidCategory($categoryId): void
	{
		$categoryId = (int) $categoryId;

		$this->assert($categoryId > 0, 'COM_CONTACTUS_ITEM_ERR_CATEGORY_EMPTY');

		$app        = Factory::getApplication();
		$user       = $app->getIdentity() ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
		$viewLevels = $user->getAuthorisedViewLevels();

		$db    = $this->getDatabase();
		$query = (method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true))
			->select($db->quoteName('contactus_category_id'))
			->from($db->quoteName('#__contactus_categories'))
			->where($db->quoteName('contactus_category_id') . ' = :catid')
			->where($db->quoteName('enabled') . ' = 1')
			->whereIn($db->quoteName('access'), $viewLevels)
			->bind(':catid', $categoryId, ParameterType::INTEGER);

		// Multiple language filter, matching the category selection field
		if (Multilanguage::isEnabled())
		{
			$languages = ['', '*', $app->getLanguage()->getTag()];
			$query->whereIn($db->quoteName('language'), $languages, ParameterType::STRING);
		}

		$exists = (int) $db->setQuery($query)->loadResult();

		$this->assert($exists > 0, 'COM_CONTACTUS_ITEM_ERR_CATEGORY_INVALID');
	}

	private function assert(bool $condition, string $message): void
	{
		if (!$condition)
		{
			throw new RuntimeException(Text::_($message));
		}
	}

	private function assertNotEmpty($value, string $message): void
	{
		$this->assert(!empty($value), $message);
	}

	private function sendEmailToAdministrators($table, $category)
	{
		/** @noinspection PhpDeprecationInspection */
		$mailer = clone (class_exists(MailerFactoryInterface::class) ? Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer() : Factory::getMailer());
		$app    = Factory::getApplication();

		$mailer->setFrom($app->get('mailfrom'), $app->get('fromname'));
		$mailer->addReplyTo($table->fromemail, $table->fromname);

		// Set the recipient to this category's email address
		$emails = explode(',', $category->email);
		$emails = array_map('trim', $emails);

		if (empty($emails))
		{
			return;
		}

		$mailer->setSubject(Text::sprintf('COM_CONTACTUS_ITEMS_MSG_EMAIL_SUBJECT',
			$app->get('sitename'),
			$category->title,
			$table->subject));

		foreach ($emails as $email)
		{
			$mailer->addRecipient($email);
		}

		$mailer->msgHTML($table->body);

		try
		{
			$mailer->Send();
		}
		catch (Exception $e)
		{
			return;
		}
	}

	/**
	 * Sends an email to the user who filed the contact message
	 */
	private function sendEmailToUser($table, $category)
	{
		if (!$category->sendautoreply)
		{
			return false;
		}

		$autoReply = $category->autoreply;
		$autoReply = $this->preProcessAutoreply($autoReply, $table, $category);

		/** @noinspection PhpDeprecationInspection */
		$mailer = clone (class_exists(MailerFactoryInterface::class) ? Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer() : Factory::getMailer());
		$app    = Factory::getApplication();

		$mailer->setFrom($app->get('mailfrom'), $app->get('fromname'));
		$mailer->addRecipient($table->fromemail, $table->fromname);
		$mailer->setSubject(Text::sprintf('COM_CONTACTUS_ITEMS_MSG_AUTOREPLY_SUBJECT', $app->get('sitename')));
		$mailer->msgHTML($autoReply);

		try
		{
			$mailer->Send();
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Pre-processes the text of the automatic reply, replacing variables in it.
	 *
	 * @param   string    $text      The original text
	 * @param   Table     $table
	 * @param   stdClass  $category  The contact category of the received contact message
	 *
	 * @return  string  The processed message
	 */
	private function preProcessAutoreply(string $text, $table, stdClass $category): string
	{
		$app          = Factory::getApplication();
		$replacements = [
			'[SITENAME]' => $app->get('sitename'),
			'[CATEGORY]' => $category->title,
		];

		$rawData = (array) $table;

		foreach ($rawData as $key => $value)
		{
			if (str_starts_with($key, "\0"))
			{
				continue;
			}

			$replacements['[' . strtoupper($key) . ']'] = $value;
		}

		return str_replace(array_keys($replacements), array_values($replacements), $text);
	}
}