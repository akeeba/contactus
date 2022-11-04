<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Model;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

#[\AllowDynamicProperties]
class ItemModel extends AdminModel
{
	/**
	 * @inheritDoc
	 */
	public function getForm($data = [], $loadData = true)
	{
		return $this->loadForm(
			'com_contactus.item',
			'item',
			[
				'control'   => 'jform',
				'load_data' => $loadData,
			]
		) ?: false;
	}

	protected function loadFormData()
	{
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_contactus.edit.item.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_contactus.item', $data);

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

}