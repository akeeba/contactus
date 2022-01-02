<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Table;

defined('_JEXEC') or die;

use Akeeba\Component\ContactUs\Administrator\Table\Mixin\CreateModifyAware;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 *
 * @property int    $contactus_category_id
 * @property string $title
 * @property string $email
 * @property int    $sendautoreply
 * @property string $autoreply
 * @property int    $access
 * @property string $language
 * @property int    $ordering
 * @property int    $enabled
 * @property string $created_on
 * @property int    $created_by
 * @property string $modified_on
 * @property int    $modified_by
 * @property string $locked_on
 * @property int    $locked_by
 */
class CategoryTable extends Table
{
	use CreateModifyAware;

	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__contactus_categories', 'contactus_category_id', $db);

		$this->setColumnAlias('id', 'contactus_category_id');
		$this->setColumnAlias('published', 'enabled');
		$this->setColumnAlias('checked_out', 'locked_by');
		$this->setColumnAlias('checked_out_time', 'locked_on');

		$this->created_on = Factory::getDate()->toSql();
		$this->autoreply = 0;
		$this->access = 1;
		$this->language = '*';
	}

	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if (empty($this->title))
		{
			$this->setError(Text::_('COM_CONTACTUS_CATEGORY_ERR_TITLE_EMPTY'));

			return false;
		}

		// Set created date if not set.
		if (!(int) $this->created_on)
		{
			$this->created_on = Factory::getDate()->toSql();
		}

		// Set ordering
		if (empty($this->ordering))
		{
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder();
		}

		// Set modified to created if not set
		if (!$this->modified_on)
		{
			$this->modified_on = $this->created_on;
		}

		// Set modified_by to created_by if not set
		if (empty($this->modified_by))
		{
			$this->modified_by = $this->created_by;
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		$this->onBeforeStore();

		return parent::store($updateNulls);
	}
}