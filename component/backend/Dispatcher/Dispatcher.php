<?php
/**
 * @package        contactus
 * @copyright      Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Admin\Dispatcher;

defined('_JEXEC') or die();

use FOF30\Database\Installer;
use FOF30\Dispatcher\Dispatcher as BaseDispatcher;
use FOF30\Dispatcher\Mixin\ViewAliases;

class Dispatcher extends BaseDispatcher
{
	use ViewAliases {
		onBeforeDispatch as onBeforeDispatchViewAliases;
	}

	public function onBeforeDispatch()
	{
		$this->onBeforeDispatchViewAliases();
		// $this->checkAndFixDatabase();

		// Load the FOF language
		$lang = $this->container->platform->getLanguage();
		$lang->load('lib_fof30', JPATH_ADMINISTRATOR, 'en-GB', true, true);
		$lang->load('lib_fof30', JPATH_ADMINISTRATOR, null, true, false);

		// Renderer options (0=none, 1=frontend, 2=backend, 3=both)
		$useFEF   = $this->container->params->get('load_fef', 3);
		$fefReset = $this->container->params->get('fef_reset', 3);

		// FEF Renderer options. Used to load the common CSS file.
		$this->container->renderer->setOptions([
			// Classic linkbar for drop-down menu display
			'linkbar_style' => 'classic',
			// Load custom CSS file, comma separated list
			'custom_css'    => 'media://com_contactus/css/backend.css',
			'load_fef'      => in_array( $useFEF, [ 2, 3 ] ),
			'fef_reset'     => in_array( $fefReset, [ 2, 3 ] )
		]);
	}

	/**
	 * Checks the database for missing / outdated tables using the $dbChecks
	 * data and runs the appropriate SQL scripts if necessary.
	 *
	 * @return  void
	 */
	private function checkAndFixDatabase()
	{
		$db = $this->container->platform->getDbo();

		$dbInstaller = new Installer($db, JPATH_ADMINISTRATOR . '/components/com_contactus/sql/xml');

		try
		{
			$dbInstaller->updateSchema();
		}
		catch (\Exception $e)
		{
		}
	}

}