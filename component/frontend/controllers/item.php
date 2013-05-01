<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class ContactusControllerItem extends FOFController
{
	public function execute($task)
	{
		// Only allow the "add", "save" tasks in the front-end
		if (!in_array($task, array('add', 'save')))
		{
			return false;
		}

		return parent::execute($task);
	}

	protected function onBeforeAdd()
	{
		// Always allow people to send contact requests no matter what
		return true;
	}

	protected function onBeforeSave()
	{
		// Always allow people to send contact requests no matter what
		return true;
	}

	protected function onAfterSave()
	{
		$msg = JText::_('COM_CONTACTUS_ITEM_MSG_SENT');
		$this->setRedirect(JURI::getInstance(), $msg, 'info');
		return true;
	}
}