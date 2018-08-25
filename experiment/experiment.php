<?php
/**
 * @package        contactus
 * @copyright      Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

use Akeeba\ContactUs\Admin\Helper\PGP;

if ($argc != 2)
{
	echo <<< END
You must give the path to the root of a Joomla! site as the first and only argument of this script.

The Joomla! installation will be used to send the email. Make sure email sending is configured correctly on the Joomla!
installation before proceeding.

END;

}

$joomlaPath = $argv[1];
$minphp     = '5.6.0';
$curdir     = $joomlaPath . '/cli';
require_once $joomlaPath . '/libraries/fof30/Cli/Application.php';

class ExperimentWithPGPEncryption extends FOFCliApplication
{
	public function execute()
	{
		$this->importAutoloader();

		$publicKeyASCII = file_get_contents('encryption_key.asc');
		$secretKeyASCII = file_get_contents('signing_key.asc');
		$message        = file_get_contents('email.txt');

		echo PGP::signAndEncrypt($message, $publicKeyASCII, $secretKeyASCII);
	}

	private function importAutoloader()
	{
		/** @var \Composer\Autoload\ClassLoader $autoloader */
		$autoloader = (require_once '../component/backend/vendor/autoload.php');
		$autoloader->addPsr4('Akeeba\\ContactUs\\Admin\\Helper\\', __DIR__ . '/Helper');
	}
}

FOFCliApplication::getInstance('ExperimentWithPGPEncryption')->execute();