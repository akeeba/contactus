<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

defined('_JEXEC') or die();

define('AKEEBA_COMMON_WRONGPHP', 1);
$minPHPVersion         = '7.2.0';
$recommendedPHPVersion = '7.4';
$softwareName          = 'Contact Us';

if (!require_once(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/Common/wrongphp.php'))
{
	echo 'Your PHP version is too old for this component.';

	return;
}

try
{
	// Load FOF 3
	if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
	{
		throw new RuntimeException('FOF 4.0 is not installed', 500);
	}

	// Execute the component
	FOF40\Container\Container::getInstance('com_contactus')->dispatcher->dispatch();
}
catch (Throwable $e)
{
	$title = 'Akeeba ContactUs';
	$isPro = false;

	if (!(include_once JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/Common/errorhandler.php'))
	{
		throw $e;
	}
}
