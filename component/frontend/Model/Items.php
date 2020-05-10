<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Site\Model;

defined('_JEXEC') or die();

use Akeeba\ContactUs\Site\Helper\Akismet;
use Exception;
use FOF30\Model\DataModel;
use FOF30\Model\Mixin\Assertions;
use gnupg;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use OpenPGP;
use OpenPGP_Crypt_Symmetric;
use OpenPGP_LiteralDataPacket;
use OpenPGP_Message;

/**
 * Model Akeeba\ContactUs\Admin\Model\Items
 *
 * Fields:
 *
 * @property  int        $contactus_item_id
 * @property  int        $contactus_category_id
 * @property  string     $fromname
 * @property  string     $fromemail
 * @property  string     $subject
 * @property  string     $body
 * @property  string     $token
 *
 * Filters:
 *
 * @method  $this  contactus_item_id()      contactus_item_id(int $v)
 * @method  $this  contactus_category_id()  contactus_category_id(int $v)
 * @method  $this  fromname()               fromname(string $v)
 * @method  $this  fromemail()              fromemail(string $v)
 * @method  $this  subject()                subject(string $v)
 * @method  $this  body()                   body(string $v)
 * @method  $this  enabled()                enabled(bool $v)
 * @method  $this  token()                  token(string $v)
 * @method  $this  created_on()             created_on(string $v)
 * @method  $this  created_by()             created_by(int $v)
 * @method  $this  modified_on()            modified_on(string $v)
 * @method  $this  modified_by()            modified_by(int $v)
 * @method  $this  locked_on()              locked_on(string $v)
 * @method  $this  locked_by()              locked_by(int $v)
 *
 * Relations:
 *
 * @property  Categories $category
 *
 **/
class Items extends DataModel
{
	use Assertions;

	/** @var   bool  Did we save the record successfully? Used by the controller for conditional redirection to the Thank You page. */
	public $saveSuccessful = false;

	/**
	 * Was the message believed to be spam when saving it?
	 *
	 * @var bool
	 */
	public $isSpam = false;

	/**
	 * Get the Joomla! CAPTCHA object
	 *
	 * @param   string  $namespace
	 *
	 * @return  Captcha|null
	 */
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

	/**
	 * Returns the IDs of categories with encrypted keys support
	 *
	 * @param   bool  $strict  True to only return categories where ALL recipients support encrypted emails.
	 *
	 * @return  array
	 */
	public function getEncryptedCategories(bool $strict = true): array
	{
		/** @var Keys $keysModel */
		$keysModel      = $this->container->factory->model('Keys')->tmpInstance();
		$emailsWithKeys = $keysModel->enabled(1)->get(true)->fetch('email')->toArray();

		if (empty($emailsWithKeys))
		{
			return [];
		}

		/** @var Categories $catModel */
		$catModel      = $this->container->factory->model('Categories')->tmpInstance();
		$allCategories = $catModel->enabled(1)
			->get(true)->filter(function (Categories $cat) use ($emailsWithKeys, $strict) {
				$emails = explode(',', $cat->email);
				$emails = array_map('trim', $emails);

				$commonEmails = array_intersect($emails, $emailsWithKeys);

				if ($strict)
				{
					return count($commonEmails) === count($emails);
				}

				return count($commonEmails) > 0;
			});

		return $allCategories->fetch($catModel->getKeyName())->toArray();
	}


	/**
	 * This method is only called after a record is saved. We will hook on it
	 * to send an email to the address specified in the category.
	 *
	 * @return  void
	 */
	protected function onAfterSave()
	{
		$apiKey = $this->container->params->get('akismet_api_key', '');

		$this->saveSuccessful = true;
		$this->isSpam         = Akismet::isSpamContent($apiKey, $this->fromname, $this->fromemail, $this->body);

		// Don't email the admins if it's spam.
		if (!$this->isSpam)
		{
			$this->_sendEmailToAdministrators();
		}

		$this->_sendEmailToUser();
	}

	/**
	 * Perform the form validation checks
	 */
	protected function onBeforeCheck()
	{
		$this->assertNotEmpty($this->contactus_category_id, 'COM_CONTACTUS_ITEM_ERR_CATEGORY_EMPTY');
		$this->assertNotEmpty($this->fromname, 'COM_CONTACTUS_ITEM_ERR_FROMNAME_EMPTY');
		$this->assertNotEmpty($this->fromemail, 'COM_CONTACTUS_ITEM_ERR_FROMEMAIL_EMPTY');
		$this->assertNotEmpty($this->subject, 'COM_CONTACTUS_ITEM_ERR_SUBJECT_EMPTY');
		$this->assertNotEmpty($this->body, 'COM_CONTACTUS_ITEM_ERR_BODY_EMPTY');

		$captcha = $this->getCaptchaObject();

		if (is_null($captcha))
		{
			return;
		}

		$this->assert($captcha->checkAnswer($this->input->get('captcha', '', 'raw')), 'COM_CONTACTUS_ITEM_ERR_CAPTCHA');
	}

	/**
	 * Sends an email to all contact category administrators.
	 */
	private function _sendEmailToAdministrators()
	{
		// Get a reference to the Joomla! mailer object
		$mailer = \JFactory::getMailer();

		// Set up the sender
		$fromemail = \JFactory::getConfig()->get('mailfrom');
		$fromname  = \JFactory::getConfig()->get('fromname');
		$mailer->setFrom($fromemail, $fromname);

		// Set up the reply to address
		$mailer->addReplyTo($this->fromemail, $this->fromname);

		// Load the category and set the recipient to this category's email address

		/** @var DataModel $category */
		$category = $this->container->factory->model('Categories')->tmpInstance();
		$category->findOrFail($this->contactus_category_id);

		$emails = explode(',', $category->email);
		$emails = array_map('trim', $emails);

		if (empty($emails))
		{
			return;
		}

		// Set the subject
		$subject = Text::sprintf('COM_CONTACTUS_ITEMS_MSG_EMAIL_SUBJECT',
			\JFactory::getConfig()->get('sitename'),
			$category->title,
			$this->subject);

		$mailer->setSubject($subject);

		// On fully unencrypted categories we simply send out emails with a CC to all recipients
		$encryptedCats = $this->getEncryptedCategories(false);

		if (!in_array($category->getId(), $encryptedCats))
		{
			foreach ($emails as $email)
			{
				$mailer->addRecipient($email);
			}

			// Set the body
			$mailer->msgHTML($this->body);

			// Send the email
			$mailer->Send();

			return;
		}

		foreach ($emails as $email)
		{
			$thisMailer = clone $mailer;
			$thisMailer->addRecipient($email);

			try
			{
				$encrypted = $this->encryptBody($this->body, $email);
				$thisMailer->setBody('This is a PGP encrypted message');
				$thisMailer->addStringAttachment($encrypted, 'encrypted.asc', '8bit', 'application/pgp-encrypted', 'attachment');
			}
			catch (Exception $e)
			{
				$thisMailer->msgHTML($this->body);
			}

			$thisMailer->Send();
		}
	}

	/**
	 * Sends an email to the user who filed the contact message
	 */
	private function _sendEmailToUser()
	{
		// Load the category and check the autoreply status
		/** @var DataModel $category */
		$category = $this->container->factory->model('Categories')->tmpInstance();
		$category->findOrFail($this->contactus_category_id);

		if (!$category->sendautoreply)
		{
			return false;
		}

		$autoReply = $category->autoreply;
		$autoReply = $this->_preProcessAutoreply($autoReply, $category);

		// Get a reference to the Joomla! mailer object
		$mailer = \JFactory::getMailer();

		// Set up the sender
		$fromemail = \JFactory::getConfig()->get('mailfrom');
		$fromname  = \JFactory::getConfig()->get('fromname');
		$mailer->setFrom($fromemail, $fromname);

		$mailer->addRecipient($this->fromemail);

		// Set the subject
		$subject = Text::sprintf('COM_CONTACTUS_ITEMS_MSG_AUTOREPLY_SUBJECT', \JFactory::getConfig()->get('sitename'));

		$mailer->setSubject($subject);

		// Set the body
		$mailer->msgHTML($autoReply);

		// Send the email
		$mailer->Send();
	}

	/**
	 * Pre-processes the text of the automatic reply, replacing variables in it.
	 *
	 * @param   string     $text      The original text
	 * @param   DataModel  $category  The contact category of the received contact message
	 *
	 * @return  string  The processed message
	 */
	private function _preProcessAutoreply($text, DataModel $category)
	{
		$replacements = [
			'[SITENAME]' => \JFactory::getConfig()->get('sitename'),
			'[CATEGORY]' => $category->title,
		];

		$rawData = $this->getData();

		foreach ($rawData as $key => $value)
		{
			$replacements['[' . strtoupper($key) . ']'] = $value;
		}

		foreach ($replacements as $find => $changeTo)
		{
			$text = str_replace($find, $changeTo, $text);
		}

		return $text;
	}

	/**
	 * Encrypts the message body with the GunPG key
	 *
	 * @param   string  $body   The string to encrypt.
	 * @param   string  $email  The email recipient. They must have a corresponding Key entry.
	 *
	 * @return  string  The encrypted message.
	 *
	 * @throws  Exception  Thrown if encryption is not possible.
	 */
	private function encryptBody(string $body, string $email): string
	{
		/** @var Keys $keysModel */
		$keysModel = $this->container->factory->model('Keys')->tmpInstance();
		$keyRecord = $keysModel->enabled(1)->email($email)->firstOrFail();

		// Key unarmor and parsing
		preg_match('/-----BEGIN ([A-Za-z ]+)-----/', $keyRecord->pubkey, $matches);
		$marker     = (empty($matches[1])) ? 'MESSAGE' : $matches[1];
		$key        = OpenPGP::unarmor($keyRecord->pubkey, $marker);
		$openpgpKey = OpenPGP_Message::parse($key);

		// Encrypt into a PGP message
		$plain_data = new OpenPGP_LiteralDataPacket($body, [
			'format' => 'u', 'filename' => tempnam(sys_get_temp_dir(), 'cspgp'),
		]);

		$encrypted = OpenPGP_Crypt_Symmetric::encrypt($openpgpKey, new OpenPGP_Message([$plain_data]));

		return OpenPGP::enarmor($encrypted->to_bytes(), 'PGP MESSAGE', []);
	}
}
