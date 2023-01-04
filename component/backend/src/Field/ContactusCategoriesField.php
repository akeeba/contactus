<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

class ContactusCategoriesField extends ListField
{
	protected $type = 'ContactusCategories';

	protected function getInput()
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select([
				$db->qn('contactus_category_id'),
				$db->qn('title'),
			])->from($db->qn('#__contactus_categories'));
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