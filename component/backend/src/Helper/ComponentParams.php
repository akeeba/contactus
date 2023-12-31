<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory as JoomlaFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

class ComponentParams
{
	/**
	 * Actually Save the params into the db
	 *
	 * @param   Registry  $params
	 *
	 * @since   9.0.0
	 */
	public static function save(Registry $params): void
	{
		/** @var DatabaseDriver $db */
		$db   = JoomlaFactory::getContainer()->get(DatabaseInterface::class);
		$data = $params->toString('JSON');

		$sql = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('params') . ' = ' . $db->q($data))
			->where($db->qn('element') . ' = ' . $db->q('com_contactus'))
			->where($db->qn('type') . ' = ' . $db->q('component'));

		$db->setQuery($sql);

		try
		{
			$db->execute();

			// The component parameters are cached. We just changed them. Therefore we MUST reset the system cache which holds them.
			CacheCleaner::clearCacheGroups(['_system'], [0, 1]);
		}
		catch (\Exception $e)
		{
			// Don't sweat if it fails
		}

		// Reset ComponentHelper's cache
		$refClass = new \ReflectionClass(ComponentHelper::class);
		$refProp  = $refClass->getProperty('components');

		$refProp->setAccessible(true);

		if (version_compare(PHP_VERSION, '8.3.0', 'ge'))
		{
			$components = $refClass->getStaticPropertyValue('components');
		}
		else
		{
			$components = $refProp->getValue();
		}

		$components['com_contactus']->params = $params;

		if (version_compare(PHP_VERSION, '8.3.0', 'ge'))
		{
			$refClass->setStaticPropertyValue('components', $components);
		}
		else
		{
			$refProp->setValue($components);
		}

	}

}