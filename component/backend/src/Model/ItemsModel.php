<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Model;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Helper\DbQuery;
use Akeeba\Component\ContactUs\Administrator\Mixin\SanitiseListFiltersTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

#[\AllowDynamicProperties]
class ItemsModel extends ListModel
{
	use SanitiseListFiltersTrait;

	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'contactus_item_id',
				'contactus_category_id',
				'fromname',
				'fromemail',
				'subject',
				'enabled',
				'created_on',
				'created_by',
				'modified_on',
				'modified_by',
			];
		}

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = 'created_on', $direction = 'desc')
	{
		$this->sanitiseListFilters();

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.search'));
		$id .= ':' . serialize($this->getState('filter.enabled'));
		$id .= ':' . serialize($this->getState('filter.category_id'));

		return parent::getStoreId($id);
	}


	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = DbQuery::create($db)
			->select('*')
			->from($db->qn('#__contactus_items'));

		// ID / From / Subject / Body search filter
		$search = $this->getState('filter.search');

		if (!empty($search) && is_string($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('contactus_item_id') . ' = :id')
					->bind(':id', $ids, ParameterType::INTEGER);
			}
			elseif (stripos($search, 'from:') === 0)
			{
				$search = '%' . $db->escape(substr($search, 5), true) . '%';
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
				$search = '%' . $db->escape($search, true) . '%';
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
			$enabled = (int) $enabled;
			$query->where($db->quoteName('enabled') . ' = :enabled')
				->bind(':enabled', $enabled, ParameterType::INTEGER);
		}

		// Category filter
		$catId = $this->getState('filter.category_id');

		if (is_numeric($catId))
		{
			$catId = (int) $catId;
			$query->where($db->quoteName('contactus_category_id') . ' = :catid')
				->bind(':catid', $catId, ParameterType::INTEGER);
		}

		// List ordering clause
		$orderCol  = $this->getState('list.ordering', 'created_on');
		$orderDirn = $this->getState('list.direction', 'DESC');
		$ordering  = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);

		$query->order($ordering);

		return $query;
	}


}