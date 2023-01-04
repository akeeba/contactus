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
use Joomla\Utilities\ArrayHelper;

#[\AllowDynamicProperties]
class CategoriesModel extends ListModel
{
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = [
				'search',
				'enabled',
				'autoreply',
				'access',
				'language',
				'created_on',
				'contactus_category_id',
				'ordering',
			];
		}

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = 'contactus_category_id', $direction = 'asc')
	{
		$app = Factory::getApplication();

		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		$enabled = $app->getUserStateFromRequest($this->context . 'filter.enabled', 'filter_enabled', '', 'string');
		$this->setState('filter.enabled', ($enabled === '') ? $enabled : (int) $enabled);

		$autoReply = $app->getUserStateFromRequest($this->context . 'filter.autoreply', 'filter_autoreply', '', 'string');
		$this->setState('filter.autoreply', ($autoReply === '') ? $autoReply : (int) $autoReply);

		$access = $app->getUserStateFromRequest($this->context . 'filter.access', 'filter_access', '', 'string');
		$this->setState('filter.access', ($access === '') ? $access : (int) $access);

		$language = $app->getUserStateFromRequest($this->context . 'filter.language', 'filter_language', '', 'string');
		$this->setState('filter.language', $language);

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.enabled');
		$id .= ':' . $this->getState('filter.autoreply');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . serialize($this->getState('filter.access'));

		return parent::getStoreId($id);
	}


	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
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

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('contactus_category_id') . ' = :id')
					->bind(':id', $ids, ParameterType::INTEGER);
			}
			else
			{
				$search = '%' . $search . '%';
				$query->where(
					'(' .
					$db->qn('title') . ' LIKE :search1' . ' OR ' .
					$db->qn('email') . ' LIKE :search2'
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

		// Auto-reply filter
		$autoReply = $this->getState('filter.autoreply');

		if (is_numeric($autoReply))
		{
			$query->where($db->quoteName('autoreply') . ' = :autoreply')
				->bind(':autoreply', $autoReply);
		}

		// Access filter
		$access = $this->getState('filter.access');

		if (is_numeric($access))
		{
			$query->where($db->quoteName('access') . ' = :access')
				->bind(':access', $access);
		}
		elseif (is_array($access))
		{
			$access = ArrayHelper::toInteger($access);
			$query->whereIn($db->quoteName('access'), $access);
		}

		// Language filter
		$language = $this->getState('filter.language');

		if (!empty($language))
		{
			$query->where($db->quoteName('language') . ' = :language')
				->bind(':language', $language);
		}

		// List ordering clause
		$orderCol  = $this->state->get('list.ordering', 'contactus_category_id');
		$orderDirn = $this->state->get('list.direction', 'ASC');
		$ordering  = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);

		$query->order($ordering);

		return $query;

	}
}