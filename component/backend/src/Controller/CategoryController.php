<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Controller\Mixin\ControllerEvents;
use Joomla\CMS\MVC\Controller\FormController;

class CategoryController extends FormController
{
	use ControllerEvents;
}