<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Model;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;

#[\AllowDynamicProperties]
class CategoryModel extends AdminModel
{
	public function getForm($data = [], $loadData = true)
	{
		return $this->loadForm(
			'com_contactus.category',
			'category',
			[
				'control'   => 'jform',
				'load_data' => $loadData,
			]
		) ?: false;
	}

	protected function loadFormData()
	{
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_contactus.edit.category.data', []);

		if (empty($data))
		{
			$data = (object) $this->getItem()->getProperties();
		}

		$this->preprocessData('com_contactus.category', $data);

		return $data;
	}

	protected function prepareTable($table)
	{
		$date = Factory::getDate();
		$user = Factory::getApplication()->getIdentity() ?: Factory::getUser();

		if (empty($table->getId()))
		{
			// Set the values
			$table->created_on = $date->toSql();
			$table->created_by = $user->id;
		}
		else
		{
			// Set the values
			$table->modified_on = $date->toSql();
			$table->modified_by = $user->id;
		}
	}

	protected function preprocessData($context, &$data, $group = 'content')
	{
		parent::preprocessData($context, $data, $group);

		if (!is_object($data) || !$data->email instanceof CMSObject)
		{
			return;
		}

		$data->email = $data->email->getProperties();
	}


}