<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\Component\ContactUs\Site\Controller;


use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

class ItemController extends BaseController
{
	use FormFactoryAwareTrait;

	protected $context = 'item';

	protected $option = 'com_contactus';

	public function __construct($config = [], MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null, FormFactoryInterface $formFactory = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// Set the form factory
		$this->setFormFactory($formFactory);

		// Set the default task
		$this->registerDefaultTask('add');
	}

	public function add()
	{
		$this->app->input->set('contactus_item_id', null);

		return $this->display(false);
	}

	public function send()
	{
		/** @var SiteApplication $app */
		$app = Factory::getApplication();
		$app->allowCache(false);

		// TODO
		die("not implemented... yet");
	}
}