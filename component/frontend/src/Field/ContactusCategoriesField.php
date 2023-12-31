<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

class ContactusCategoriesField extends ListField
{
	protected $type = 'ContactusCategories';

	protected function getInput()
	{
		/** @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true)
			->select([
				$db->qn('contactus_category_id'),
				$db->qn('title'),
			])->from($db->qn('#__contactus_categories'))
			->where($db->quoteName('enabled') . ' = 1');

		// Access filtering
		$user       = Factory::getApplication()->getIdentity() ?: Factory::getUser();
		$viewLevels = $user->getAuthorisedViewLevels();
		$query->whereIn($db->quoteName('access'), $viewLevels);

		// Multiple language filter
		if (Multilanguage::isEnabled())
		{
			$languages = ['', '*', Factory::getApplication()->getLanguage()->getTag()];
			$query->whereIn($db->quoteName('language'), $languages, ParameterType::STRING);
		}

		// Get the categories
		$db->setQuery($query);
		$objectList = $db->loadObjectList() ?? [];

		foreach ($objectList as $o)
		{
			$this->addOption($o->title, [
				'value' => $o->contactus_category_id,
			]);
		}

		return parent::getInput();
	}
}