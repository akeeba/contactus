<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2013-2017 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

namespace Akeeba\ContactUs\Site\Model;

use Akeeba\TicketSystem\Admin\Helper\Permissions;
use Akeeba\TicketSystem\Site\Model\Posts;
use Akeeba\TicketSystem\Site\Model\Tickets;
use FOF30\Container\Container;
use FOF30\Model\DataModel;
use JUser;
use RuntimeException;

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

		// Load the category and get the ATS ticket category
		/** @var DataModel $category */
		$category = $this->container->factory->model('Categories')->tmpInstance();
		$category->findOrFail($this->contactus_category_id);
		$atsCategoryID = $category->ticketcategory;

		try
		{
			// Try to file a ticket instead of sending emails
			$this->createTicket($atsCategoryID, $this->subject, $this->body, $this->fromemail);
		}
		catch (\Exception $e)
		{
			// If I couldn't file a ticket, send emails
			$this->_sendEmailToAdministrators();
			$this->_sendEmailToUser();
		}

		return true;
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

	/**
	 * Try to create an ATS ticket from the contact form. Throws an exception if that fails or there is no user with
	 * this email address registered on our site.
	 *
	 * @param $atsCategoryID
	 * @param $subject
	 * @param $body
	 * @param $email
	 */
	private function createTicket($atsCategoryID, $subject, $body, $email)
	{
		if (empty($atsCategoryID))
		{
			throw new RuntimeException("No ATS category specified");
		}

		// Find a user by this email address
		$user = $this->getUserByEmail($email);

		if (empty($user) || !is_object($user) || !($user instanceof JUser))
		{
			throw new RuntimeException("Cannot find a user for email address $email");
		}

		// Get the ATS container
		$atsContainer = Container::getInstance('com_ats');

		// Does the category exist?
		/** @var \Akeeba\TicketSystem\Site\Model\Categories $category */
		$category = $atsContainer->factory->model('Categories')->tmpInstance();
		$category->findOrFail($atsCategoryID);

		// Get ACL permissions
		$perms = Permissions::getAclPrivileges($atsCategoryID, $user->id);

		// Can I post to the category?
		if (!$perms['core.create'])
		{
			throw new RuntimeException("User {$user->username} cannot post to category {$category->title}");
		}

		$ticketData = [
			'title'      => $subject . " [Contact form]",
			'status'     => 'O',
			'origin'     => 'web',
			'priority'   => 5,
			'public'     => 0,
			'created_by' => $user->id,
			'catid'      => $atsCategoryID,
		];

		$postData = [
			'content'      => '',
			'content_html' => $body,
			'origin'       => 'web',
			'enabled'      => 1,
			'created_by'   => $user->id,
		];

		// --- Create the ticket
		/** @var Tickets $ticketModel */
		$ticketModel = $atsContainer->factory->model('Tickets')->tmpInstance();
		/** @var Posts $postModel */
		$postModel = $atsContainer->factory->model('Posts')->tmpInstance();

		$ticketModel->save($ticketData);

		$ats_ticket_id             = $ticketModel->getId();
		$postData['ats_ticket_id'] = $ats_ticket_id;
		$postModel->save($postData);
	}

	private function getUserByEmail($email)
	{
		try
		{
			// Force load the JUser class
			class_exists('JUser', true);

			$email = trim($email);
			$db    = $this->container->db;
			$query = $db->getQuery(true)
				->select($db->qn('id'))
				->from($db->qn('#__users'))
				->where($db->qn('email') . ' = ' . $db->q($email));

			$id = $db->setQuery($query)->loadResult();

			if (empty($id))
			{
				return null;
			}

			return \JFactory::getUser($id);
		}
		catch (\Exception $e)
		{
			return null;
		}
	}
}