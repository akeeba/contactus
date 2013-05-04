<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

class ContactusModelItems extends FOFModel
{
	/**
	 * This method is only called after a record is saved. We will hook on it
	 * to send an email to the address specified in the category.
	 *
	 * @param   FOFTable  $table  The FOFTable which was just saved
	 */
	protected function onAfterSave(&$table)
	{
		$result = parent::onAfterSave($table);

		if ($result !== false)
		{
			// Get a reference to the Joomla! mailer object
			$mailer = JFactory::getMailer();

			// Set up the sender
			$mailer->SetFrom($table->fromemail, $table->fromname);

			// Load the category and set the recepient to this category's
			// email address
			$category = FOFModel::getTmpInstance('Categories', 'ContactusModel')
				->getItem($table->contactus_category_id);
			$mailer->addRecipient($category->email);

			// Set the subject
			$subject = JText::sprintf('COM_CONTACTUS_ITEMS_MSG_EMAIL_SUBJECT',
				JFactory::getConfig()->get('sitename'),
				$category->title,
				$table->subject);

			// Set the body
			$mailer->MsgHTML($table->body);

			// Send the email
			$mailer->Send();
		}

		return $result;
	}
}