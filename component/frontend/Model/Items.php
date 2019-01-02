<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Site\Model;

defined('_JEXEC') or die();

use FOF30\Model\DataModel;
use FOF30\Model\Mixin\Assertions;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;

/**
 * Model Akeeba\ContactUs\Admin\Model\Items
 *
 * Fields:
 *
 * @property  int     $contactus_item_id
 * @property  int     $contactus_category_id
 * @property  string  $fromname
 * @property  string  $fromemail
 * @property  string  $subject
 * @property  string  $body
 * @property  string  $token
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
 * @property  Categories  $category
 *
 **/
class Items extends DataModel
{
	use Assertions;

	/** @var   bool  Did we save the record successfully? Used by the controller for conditional redirection to the Thank You page. */
	public $saveSuccessful = false;

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
		catch (\Exception $e)
		{
			return null;
		}

		$plugin = $app->getParams()->get('captcha', $app->get('captcha'));

		if ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null)
		{
			return null;
		}

		return Captcha::getInstance($plugin, array('namespace' => $namespace));
	}


	/**
	 * This method is only called after a record is saved. We will hook on it
	 * to send an email to the address specified in the category.
	 *
	 * @return  void
	 */
	protected function onAfterSave()
	{
		$this->saveSuccessful = true;
		$this->_sendEmailToAdministrators();
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

		if (empty($emails))
		{
			return false;
		}

		foreach ($emails as $email)
		{
			$mailer->addRecipient($email);
		}

		// Set the subject
		$subject = \JText::sprintf('COM_CONTACTUS_ITEMS_MSG_EMAIL_SUBJECT',
			\JFactory::getConfig()->get('sitename'),
			$category->title,
			$this->subject);

		$mailer->setSubject($subject);

		// Set the body
		$mailer->msgHTML($this->body);

		// Send the email
		$mailer->Send();
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
		$subject = \JText::sprintf('COM_CONTACTUS_ITEMS_MSG_AUTOREPLY_SUBJECT', \JFactory::getConfig()->get('sitename'));

		$mailer->setSubject($subject);

		// Set the body
		$mailer->msgHTML($autoReply);

		// Send the email
		$mailer->Send();
	}

	/**
	 * Pre-processes the text of the automatic reply, replacing variables in it.
	 *
	 * @param   string    $text     The original text
	 * @param   DataModel $category The contact category of the received contact message
	 *
	 * @return  string  The processed message
	 */
	private function _preProcessAutoreply($text, DataModel $category)
	{
		$replacements = array(
			'[SITENAME]' => \JFactory::getConfig()->get('sitename'),
			'[CATEGORY]' => $category->title,
		);

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
}
