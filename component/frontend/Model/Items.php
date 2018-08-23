<?php
/**
 * @package		contactus
 * @copyright   Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Site\Model;

use FOF30\Model\DataModel;

class Items extends DataModel
{
	/** @var   bool  Did we save the record successfully? Used by the controller for conditional redirection to the Thank You page. */
	public $saveSuccessful = false;

	/**
	 * This method is only called after a record is saved. We will hook on it
	 * to send an email to the address specified in the category.
	 *
	 * @return  bool
	 */
	protected function onAfterSave()
	{
		$this->saveSuccessful = true;
		$this->_sendEmailToAdministrators();
		$this->_sendEmailToUser();
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
		$fromname = \JFactory::getConfig()->get('fromname');
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
		$fromname = \JFactory::getConfig()->get('fromname');
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
	 * @param   string     $text      The original text
	 * @param   DataModel  $category  The contact category of the received contact message
	 *
	 * @return  string  The processed message
	 */
	private function _preProcessAutoreply($text, DataModel $category)
	{
		$replacements = array(
			'[SITENAME]'		=> \JFactory::getConfig()->get('sitename'),
			'[CATEGORY]'		=> $category->title,
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
