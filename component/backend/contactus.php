<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

defined('_JEXEC') or die();

define('AKEEBA_COMMON_WRONGPHP', 1);
$minPHPVersion         = '5.4.0';
$recommendedPHPVersion = '7.3';
$softwareName          = 'Contact Us';

if (!require_once(__DIR__ . '/ViewTemplates/Common/wrongphp.php'))
{
	return;
}

// Load FOF 3
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('FOF 3.0 is not installed', 500);
}

// Execute the component
FOF30\Container\Container::getInstance('com_contactus')->dispatcher->dispatch();
