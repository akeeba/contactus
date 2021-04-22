<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\Component\ContactUs\Administrator\View\Items;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\View\Mixin\LoadAnyTemplate;
use Akeeba\Component\ContactUs\Administrator\View\Mixin\TaskBasedEvents;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Menus\Administrator\Model\ItemsModel;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
{
	use LoadAnyTemplate;
	use TaskBasedEvents;

	/**
	 * The search tools form
	 *
	 * @var    Form
	 * @since  1.6
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  1.6
	 */
	public $activeFilters = [];

	/**
	 * An array of items
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $items = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    Registry
	 * @since  1.6
	 */
	protected $state;

	public function display($tpl = null): void
	{
		/** @var ItemsModel $model */
		$model               = $this->getModel();
		$this->items         = $model->getItems();
		$this->pagination    = $model->getPagination();
		$this->state         = $model->getState();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar(): void
	{
		$user = Factory::getApplication()->getIdentity() ?: Factory::getUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(sprintf(Text::_('COM_CONTACTUS_TITLE_ITEMS')), 'fa fa-envelope');

		$canDelete    = $user->authorise('core.delete', 'com_contactus');
		$canEditState = $user->authorise('core.edit.state', 'com_contactus');

		if ($canDelete || $canEditState)
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canEditState)
			{
				$childBar->publish('items.publish')
					->icon('fa fa-check-circle')
					->text('JTOOLBAR_PUBLISH')
					->listCheck(true);

				$childBar->unpublish('items.unpublish')
					->icon('fa fa-times-circle')
					->text('JTOOLBAR_UNPUBLISH')
					->listCheck(true);

				$childBar->checkin('items.checkin')->listCheck(true);
			}

			if ($canDelete)
			{
				$childBar->delete('items.delete')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		ToolbarHelper::link(
			Route::_('index.php?option=com_contactus&view=categories'),
			'COM_CONTACTUS_TITLE_CATEGORIES',
			'list'
		);

		if ($user->authorise('core.admin', 'com_contactus') || $user->authorise('core.options', 'com_contactus'))
		{
			$toolbar->preferences('com_contactus');
		}
	}

}