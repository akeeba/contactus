<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\View\Item;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Model\ItemModel;
use Akeeba\Component\ContactUs\Administrator\View\Mixin\LoadAnyTemplate;
use Akeeba\Component\ContactUs\Administrator\View\Mixin\TaskBasedEvents;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	use LoadAnyTemplate;
	use TaskBasedEvents;

	/**
	 * The Form object
	 *
	 * @var    Form
	 * @since  1.5
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $state;

	public function display($tpl = null): void
	{
		/** @var ItemModel $model */
		$model       = $this->getModel();
		$this->form  = $model->getForm();
		$this->item  = $model->getItem();
		$this->state = $model->getState();

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
		Factory::getApplication()->input->set('hidemainmenu', true);

		ToolbarHelper::title(Text::_('COM_CONTACTUS_TITLE_ITEMS_EDIT'), 'fa fa-envelope');

		ToolbarHelper::apply('Item.apply');
		ToolbarHelper::save('Item.save');

		ToolbarHelper::cancel('Item.cancel', 'JTOOLBAR_CLOSE');
	}
}