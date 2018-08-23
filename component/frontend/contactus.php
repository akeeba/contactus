<?php
/**
 * @package		contactus
 * @copyright   Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

// Load FOF 3
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('FOF 3.0 is not installed', 500);
}

// Execute the component
FOF30\Container\Container::getInstance('com_contactus')->dispatcher->dispatch();
