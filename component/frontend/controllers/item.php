<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class ContactusControllerItem extends FOFController
{
	protected function onAfterSave()
	{
		$msg = JText::_('COM_CONTACTUS_ITEM_MSG_SENT');
		$this->setRedirect(JURI::getInstance(), $msg, 'info');
		return true;
	}
}