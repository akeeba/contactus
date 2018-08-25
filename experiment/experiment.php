<?php
/**
 * @package        contactus
 * @copyright      Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

use Akeeba\ContactUs\Admin\Helper\PGP;

define('_JEXEC', 1);

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = (require_once '../component/backend/vendor/autoload.php');
$autoloader->addPsr4('Akeeba\\ContactUs\\Admin\\Helper\\', __DIR__ . '/Helper');

$publicKeyASCII = file_get_contents('encryption_key.asc');
$secretKeyASCII = file_get_contents('signing_key.asc');
$message        = file_get_contents('email.txt');

echo PGP::signAndEncrypt($message, $publicKeyASCII, $secretKeyASCII);
