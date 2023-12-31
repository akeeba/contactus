<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Site\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Mixin\RunPluginsTrait;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use function count;

class ItemController extends BaseController
{
	use FormFactoryAwareTrait;
	use RunPluginsTrait;

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

	public function save()
	{
		$this->checkToken();

		/** @var SiteApplication $app */
		$app = Factory::getApplication();
		$app->allowCache(false);

		$app     = Factory::getApplication();
		$model   = $this->getModel();
		$data    = $this->input->post->get('jform', [], 'array');
		$context = "$this->option.edit.$this->context";
		$form    = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Send an object which can be modified through the plugin event
		$objData = (object) $data;
		$this->triggerPluginEvent(
			'onContentNormaliseRequestData',
			[$this->option . '.' . $this->context, $objData, $form]
		);
		$data = (array) $objData;

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Push up to three validation messages out to the user.
			$errors = $model->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			/**
			 * We need the filtered value of calendar fields because the UTC normalisation is
			 * done in the filter and on output. This would apply the Timezone offset on
			 * reload. We set the calendar values we save to the processed date.
			 */
			$filteredData = $form->filter($data);

			foreach ($form->getFieldset() as $field)
			{
				if ($field->type === 'Calendar')
				{
					$fieldName = $field->fieldname;

					if (isset($filteredData[$fieldName]))
					{
						$data[$fieldName] = $filteredData[$fieldName];
					}
				}
			}

			$app->setUserState($context . '.data', $data);

			$this->setRedirect(Route::_('index.php?option=com_contactus&view=Item.add', false));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($validData))
		{
			$app->setUserState('com_contactus.edit.item.data', $validData);

			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect(Route::_('index.php?option=com_contactus&view=Item.add', false));

			return false;
		}

		$app->setUserState('com_contactus.edit.item.data', null);

		$url = 'index.php?option=com_contactus&view=thanks' . ($model->getState('isSpam', false) ? '&layout=spammer' : '');

		$this->setRedirect(Route::_($url, false));

		return true;
	}
}