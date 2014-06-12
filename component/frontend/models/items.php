<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

class ContactusModelItems extends F0FModel
{
	/**
	 * This method is only called after a record is saved. We will hook on it
	 * to send an email to the address specified in the category.
	 *
	 * @param   F0FTable  $table  The F0FTable which was just saved
	 */
	protected function onAfterSave(&$table)
	{
		$result = parent::onAfterSave($table);

		if ($result !== false)
		{
			$this->_sendEmailToAdministrators($table);
			$this->_sendEmailToUser($table);
		}

		return $result;
	}

	private function _sendEmailToAdministrators($table)
	{
		// Get a reference to the Joomla! mailer object
		$mailer = JFactory::getMailer();

		// Set up the sender
		$mailer->SetFrom($table->fromemail, $table->fromname);

		// Load the category and set the recipient to this category's
		// email address
		$category = F0FModel::getTmpInstance('Categories', 'ContactusModel')
			->getItem($table->contactus_category_id);
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
		$subject = JText::sprintf('COM_CONTACTUS_ITEMS_MSG_EMAIL_SUBJECT',
			JFactory::getConfig()->get('sitename'),
			$category->title,
			$table->subject);

		$mailer->setSubject($subject);

		// Set the body
		$mailer->MsgHTML($table->body);

		// Send the email
		$mailer->Send();
	}

	private function _sendEmailToUser($table)
	{
		// Load the category and check the autoreply status
		$category = F0FModel::getTmpInstance('Categories', 'ContactusModel')
			->getItem($table->contactus_category_id);

		if (!$category->sendautoreply)
		{
			return false;
		}

		$autoReply = $category->autoreply;
		$autoReply = $this->_preProcessAutoreply($autoReply, $table, $category);

		// Get a reference to the Joomla! mailer object
		$mailer = JFactory::getMailer();

		// Set up the sender
		$fromemail = JFactory::getConfig()->get('mailfrom');
		$fromname = JFactory::getConfig()->get('fromname');
		$mailer->SetFrom($fromemail, $fromname);

		$mailer->addRecipient($table->fromemail);

		// Set the subject
		$subject = JText::sprintf('COM_CONTACTUS_ITEMS_MSG_AUTOREPLY_SUBJECT', JFactory::getConfig()->get('sitename'));

		$mailer->setSubject($subject);

		// Set the body
		$mailer->MsgHTML($autoReply);

		// Send the email
		$mailer->Send();
	}

	private function _preProcessAutoreply($text, F0FTable $item, F0FTable $category)
	{
		$replacements = array(
			'[SITENAME]'		=> JFactory::getConfig()->get('sitename'),
			'[CATEGORY]'		=> $category->title,
		);

		$rawData = $item->getData();

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