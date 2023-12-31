<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Site\View\Item;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * HTML Article View class for the Content component
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	protected $form;

	protected $item;

	protected $return_page = '';

	protected $state;

	protected $params = null;

	protected $pageclass_sfx = '';

	protected $user = null;

	protected $captchaEnabled = false;

	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = $app->getIdentity() ?: Factory::getUser();

		// Get model data.
		$this->state       = $this->get('State');
		$this->item        = $this->get('Item');
		$this->form        = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');
		$errors            = $this->get('Errors');

		// Check for errors.
		if (is_countable($errors) && count($errors))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Create a shortcut to the parameters.
		$params = &$this->state->params;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		$this->params        = $params;
		$this->user          = $user;

		$captchaSet = $params->get('captcha', Factory::getApplication()->get('captcha', '0'));

		foreach (PluginHelper::getPlugin('captcha') as $plugin)
		{
			if ($captchaSet === $plugin->name)
			{
				$this->captchaEnabled = true;
				break;
			}
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app = Factory::getApplication();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $app->getMenu()->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_CONTACTUS_PAGE_TITLE'));
		}

		$title = $this->params->def('page_title', Text::_('COM_CONTACTUS_PAGE_TITLE'));

		$this->setDocumentTitle($title);

		$app->getPathway()->addItem($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}
	}

	public function getCaptchaField($name = 'captcha', $namespace = 'contactus', $id = null, $class = '')
	{
		$model   = $this->getModel();
		$captcha = $model->getCaptchaObject();

		if (is_null($captcha))
		{
			return '';
		}

		if (strpos($class, 'required') === false)
		{
			$class .= ' required';
		}

		if (empty($id))
		{
			$id = $name;
		}

		return $captcha->display($name, $id, $class);
	}

}
