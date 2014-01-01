<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class ContactusControllerItem extends FOFController
{
	protected function onAfterSave()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_contactus&view=thankyou'));
		return true;
	}
}