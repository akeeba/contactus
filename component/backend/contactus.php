<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

defined('_JEXEC') or die();

define('AKEEBA_COMMON_WRONGPHP', 1);
$minPHPVersion         = '7.2.0';
$recommendedPHPVersion = '7.4';
$softwareName          = 'Contact Us';

if (!require_once(__DIR__ . '/ViewTemplates/Common/wrongphp.php'))
{
	return;
}

// So, FEF is not installed?
if (!@file_exists(JPATH_SITE . '/media/fef/fef.php'))
{
	(include_once __DIR__ . '/ViewTemplates/Common/fef.php') or die('You need to have the Akeeba Frontend Framework (FEF) package installed on your site to display this component. Please visit https://www.akeeba.com/download/official/fef.html to download it and install it on your site.');

	return;
}

try
{
	// Load FOF 3
	if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
	{
		throw new RuntimeException('FOF 3.0 is not installed', 500);
	}

	// Execute the component
	FOF30\Container\Container::getInstance('com_contactus')->dispatcher->dispatch();
}
catch (Throwable $e)
{
	$title = 'Akeeba ContactUs';
	$isPro = false;

	if (!(include_once JPATH_COMPONENT_ADMINISTRATOR . '/ViewTemplates/Common/errorhandler.php'))
	{
		throw $e;
	}
}
