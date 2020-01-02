<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Site\Controller;

use Akeeba\ContactUs\Site\Controller\Mixin\PredefinedTaskList;
use Akeeba\ContactUs\Site\Model\Items;
use Akeeba\ContactUs\Site\View\Item\Html;
use FOF30\Container\Container;
use FOF30\Controller\DataController;

defined('_JEXEC') or die();

class Item extends DataController
{
	use PredefinedTaskList;

	static public $savedFields = [
		'fromname', 'fromemail', 'subject', 'body',
	];

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = [
			'add',
			'save',
		];
	}

	/**
	 * Let guest users see the contact form
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function onBeforeAdd()
	{
		$data = [];

		// Save the form data to the session
		foreach (self::$savedFields as $fieldName)
		{
			$data[$fieldName] = $this->container->platform->getSessionVar('form.' . $fieldName, '', 'com_contactus');
		}

		/** @var Html $view */
		$view              = $this->getView();
		$view->sessionData = $data;

		return true;
	}

	/**
	 * Let guest users submit the contact form
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function onBeforeSave()
	{
		// Save the form data to the session
		foreach (self::$savedFields as $fieldName)
		{
			$value = $this->container->input->get($fieldName, '', 'raw', 2);
			$this->container->platform->setSessionVar('form.' . $fieldName, $value, 'com_contactus');
		}

		return true;
	}

	/**
	 * Redirects the user to the Thank You page after successfully receiving the message
	 *
	 * @return  bool  True to continue processing
	 *
	 * @since   1.0.0
	 */
	protected function onAfterSave()
	{
		/** @var Items $model */
		$model = $this->getModel();

		if ($model->saveSuccessful)
		{
			$url = 'index.php?option=com_contactus&view=ThankYou';

			if ($model->isSpam)
			{
				$url .= '&layout=spammer';
			}

			$this->setRedirect(\JRoute::_($url));

			// Unset data from the session
			foreach (self::$savedFields as $fieldName)
			{
				$this->container->platform->unsetSessionVar('form.' . $fieldName, 'com_contactus');
			}
		}

		return true;
	}
}
