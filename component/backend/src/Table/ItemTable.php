<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Table;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Mixin\TableCreateModifyTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * ContactUs Item table
 *
 * @property int    $contactus_item_id
 * @property int    $contactus_category_id
 * @property string $fromname
 * @property string $fromemail
 * @property string $subject
 * @property string $body
 * @property int    $enabled
 * @property string $token
 * @property string $created_on
 * @property int    $created_by
 * @property string $modified_on
 * @property int    $modified_by
 * @property string $locked_on
 * @property int    $locked_by
 */
class ItemTable extends Table
{
	use TableCreateModifyTrait;

	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__contactus_items', 'contactus_item_id', $db);

		$this->setColumnAlias('id', 'contactus_item_id');
		$this->setColumnAlias('published', 'enabled');
		$this->setColumnAlias('checked_out', 'locked_by');
		$this->setColumnAlias('checked_out_time', 'locked_on');
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

		if (empty($this->contactus_category_id))
		{
			$this->setError(Text::_('COM_CONTACTUS_ITEM_ERR_CATEGORY_EMPTY'));

			return false;
		}

		if (empty($this->fromname))
		{
			$this->setError(Text::_('COM_CONTACTUS_ITEM_ERR_FROMNAME_EMPTY'));

			return false;
		}

		if (empty($this->fromemail))
		{
			$this->setError(Text::_('COM_CONTACTUS_ITEM_ERR_FROMEMAIL_EMPTY'));

			return false;
		}

		if (empty($this->subject))
		{
			$this->setError(Text::_('COM_CONTACTUS_ITEM_ERR_SUBJECT_EMPTY'));

			return false;
		}

		if (empty($this->body))
		{
			$this->setError(Text::_('COM_CONTACTUS_ITEM_ERR_BODY_EMPTY'));

			return false;
		}

		return true;
	}


	public function store($updateNulls = false)
	{
		$this->onBeforeStore();

		return parent::store($updateNulls);
	}
}