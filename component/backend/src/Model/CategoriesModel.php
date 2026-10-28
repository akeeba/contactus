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
use Joomla\Utilities\ArrayHelper;

#[\AllowDynamicProperties]
class CategoriesModel extends ListModel
{
	use SanitiseListFiltersTrait;

	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'contactus_category_id',
				'title',
				'email',
				'enabled',
				'sendautoreply',
				'access',
				'language',
				'ordering',
				'created_on',
				'created_by',
				'modified_on',
				'modified_by',
			];
		}

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = 'contactus_category_id', $direction = 'asc')
	{
		$this->sanitiseListFilters(['access']);

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.search'));
		$id .= ':' . serialize($this->getState('filter.enabled'));
		$id .= ':' . serialize($this->getState('filter.autoreply'));
		$id .= ':' . serialize($this->getState('filter.language'));
		$id .= ':' . serialize($this->getState('filter.access'));

		return parent::getStoreId($id);
	}


	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = DbQuery::create($db)
			->select([
				$db->quoteName('c') . '.*',
				$db->quoteName('l.title', 'language_title'),
				$db->quoteName('l.image', 'language_image'),
				$db->quoteName('ag.title', 'access_level'),
			])
			->from($db->qn('#__contactus_categories', 'c'))
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('c.access'))
			->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('c.language'))
		;

		// Search filter
		$search = $this->getState('filter.search');

		if (!empty($search) && is_string($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('c.contactus_category_id') . ' = :id')
					->bind(':id', $ids, ParameterType::INTEGER);
			}
			else
			{
				$search = '%' . $db->escape($search, true) . '%';
				$query->where(
					'(' .
					$db->qn('c.title') . ' LIKE :search1' . ' OR ' .
					$db->qn('c.email') . ' LIKE :search2'
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
			$query->where($db->quoteName('c.enabled') . ' = :enabled')
				->bind(':enabled', $enabled, ParameterType::INTEGER);
		}

		// Auto-reply filter
		$autoReply = $this->getState('filter.autoreply');

		if (is_numeric($autoReply))
		{
			$autoReply = (int) $autoReply;
			$query->where($db->quoteName('c.sendautoreply') . ' = :autoreply')
				->bind(':autoreply', $autoReply, ParameterType::INTEGER);
		}

		// Access filter
		$access = $this->getState('filter.access');

		if (is_numeric($access))
		{
			$access = (int) $access;
			$query->where($db->quoteName('c.access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		}
		elseif (is_array($access))
		{
			$access = ArrayHelper::toInteger($access);
			$query->whereIn($db->quoteName('c.access'), $access);
		}

		// Language filter
		$language = $this->getState('filter.language');

		if (!empty($language) && is_string($language))
		{
			$query->where($db->quoteName('c.language') . ' = :language')
				->bind(':language', $language);
		}

		// List ordering clause
		$orderCol  = $this->getState('list.ordering', 'contactus_category_id');
		$orderDirn = $this->getState('list.direction', 'ASC');
		$ordering  = $db->quoteName('c.' . $orderCol) . ' ' . $db->escape($orderDirn);

		$query->order($ordering);

		return $query;

	}
}