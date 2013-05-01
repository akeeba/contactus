<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class ContactusDispatcher extends FOFDispatcher
{
	public $defaultView = 'item';

	public function onBeforeDispatch()
	{
		// Only allow access to the item view
		$view = $this->input->getCmd('view', 'item');

		if ($view != 'item')
		{
			return false;
		}

		return true;
	}
}