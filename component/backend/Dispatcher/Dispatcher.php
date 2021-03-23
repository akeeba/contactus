<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Admin\Dispatcher;

defined('_JEXEC') or die();

use FOF40\Database\Installer;
use FOF40\Dispatcher\Dispatcher as BaseDispatcher;
use FOF40\Dispatcher\Mixin\ViewAliases;
use FOF40\Utils\ViewManifestMigration;

class Dispatcher extends BaseDispatcher
{
	use ViewAliases {
		onBeforeDispatch as onBeforeDispatchViewAliases;
	}

	public function onBeforeDispatch()
	{
		$this->onBeforeDispatchViewAliases();
		$this->checkAndFixDatabase();

		ViewManifestMigration::migrateJoomla4MenuXMLFiles($this->container);
		ViewManifestMigration::removeJoomla3LegacyViews($this->container);

		// Load the FOF language
		$lang = $this->container->platform->getLanguage();
		$lang->load('lib_fof40', JPATH_ADMINISTRATOR, 'en-GB', true, true);
		$lang->load('lib_fof40', JPATH_ADMINISTRATOR, null, true, false);

		// Renderer options (0=none, 1=frontend, 2=backend, 3=both)
		$useFEF   = in_array($this->container->params->get('load_fef', 3), [2, 3]);
		$fefReset = $useFEF && in_array($this->container->params->get('fef_reset', 3), [2, 3]);

		if (!$useFEF)
		{
			$this->container->rendererClass = '\\FOF40\\Render\\Joomla3';
		}

		$darkMode = $this->container->params->get('dark_mode_backend', -1);

		$customCss = 'media://com_contactus/css/backend.css';

		if ($darkMode != 0)
		{
			$customCss .= ', media://com_contactus/css/backend_dark.css';
		}

		$this->container->renderer->setOptions([
			'load_fef'      => $useFEF,
			'fef_reset'     => $fefReset,
			'fef_dark'      => $useFEF ? $darkMode : 0,
			'custom_css'    => $customCss,
			// Render submenus as drop-down navigation bars powered by Bootstrap
			'linkbar_style' => 'classic',
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