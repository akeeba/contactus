<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Mixin\ControllerEventsTrait;
use Joomla\CMS\MVC\Controller\AdminController;

class CategoriesController extends AdminController
{
	use ControllerEventsTrait;

	protected $text_prefix = 'COM_CONTACTUS_CATEGORIES';

	public function getModel($name = 'Category', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}

}