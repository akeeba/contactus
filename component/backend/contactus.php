<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

// Load FOF
require_once JPATH_LIBRARIES.'/f0f/include.php';

// Execute the component
F0FDispatcher::getTmpInstance('com_contactus')->dispatch();