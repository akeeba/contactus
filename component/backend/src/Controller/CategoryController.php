<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Mixin\ControllerEventsTrait;
use Joomla\CMS\MVC\Controller\FormController;

class CategoryController extends FormController
{
	use ControllerEventsTrait;

	protected $text_prefix = 'COM_CONTACTUS_CATEGORY';
}