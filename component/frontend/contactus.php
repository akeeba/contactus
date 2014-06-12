<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

require_once JPATH_LIBRARIES.'/f0f/include.php';

F0FDispatcher::getTmpInstance('com_contactus')->dispatch();