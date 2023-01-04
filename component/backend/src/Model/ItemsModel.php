<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Model;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

#[\AllowDynamicProperties]
class ItemsModel extends ListModel
{
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'search',
				'enabled',
				'category_id',
				'subject',
				'contactus_item_id',
				'created_on',
			];
		}

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = 'created_on', $direction = 'desc')
	{
		$app = Factory::getApplication();

		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		$enabled = $app->getUserStateFromRequest($this->context . 'filter.enabled', 'filter_enabled', '', 'string');
		$this->setState('filter.enabled', ($enabled === '') ? $enabled : (int) $enabled);

		$catId = $app->getUserStateFromRequest($this->context . 'filter.category_id', 'filter_category_id', '', 'string');
		$this->setState('filter.category_id', ($catId === '') ? $catId : (int) $catId);

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.enabled');
		$id .= ':' . $this->getState('filter.category_id');

		return parent::getStoreId($id);
	}


	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__contactus_items'));

		// ID / From / Subject / Body search filter
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('contactus_item_id') . ' = :id')
					->bind(':id', $ids, ParameterType::INTEGER);
			}
			elseif (stripos($search, 'from:') === 0)
			{
				$search = '%' . substr($search, 5) . '%';
				$query->where(
					'(' .
					$db->qn('fromname') . ' LIKE :search1' . ' OR ' .
					$db->qn('fromemail') . ' LIKE :search2'
					. ')'
				)
					->bind(':search1', $search)
					->bind(':search2', $search);
			}
			else
			{
				$search = '%' . $search . '%';
				$query->where(
					'(' .
					$db->qn('subject') . ' LIKE :search1' . ' OR ' .
					$db->qn('body') . ' LIKE :search2'
					. ')'
				)
					->bind(':search1', $search)
					->bind(':search2', $search);
			}
		}

		// Enabled filter
		$enabled = $this->getState('filter.enabled');

		if (is_numeric($enabled))
		{
			$query->where($db->quoteName('enabled') . ' = :enabled')
				->bind(':enabled', $enabled);
		}

		// Category filter
		$catId = $this->getState('filter.category_id');

		if (is_numeric($catId))
		{
			$query->where($db->quoteName('contactus_category_id') . ' = :catid')
				->bind(':catid', $catId);
		}

		// List ordering clause
		$orderCol  = $this->state->get('list.ordering', 'created_on');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$ordering  = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);

		$query->order($ordering);

		return $query;
	}


}