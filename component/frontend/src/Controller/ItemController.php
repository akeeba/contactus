<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Site\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Mixin\CMSObjectWorkaroundTrait;
use Akeeba\Component\ContactUs\Administrator\Mixin\RunPluginsTrait;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
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
	use CMSObjectWorkaroundTrait;

	protected $context = 'item';

	protected $option = 'com_contactus';

	public function __construct(
		$config = [], ?MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null,
		?FormFactoryInterface $formFactory = null
	)
	{
		parent::__construct($config, $factory, $app, $input);

		// Set the form factory
		$this->setFormFactory($formFactory);

		// Do not expose the form factory accessors as controller tasks
		$this->unregisterTask('getFormFactory');
		$this->unregisterTask('setFormFactory');

		// Set the default task
		$this->registerDefaultTask('add');
	}

	public function add()
	{
		$this->app->getInput()->set('contactus_item_id', null);

		return $this->display(false);
	}

	public function save()
	{
		$this->checkToken();

		$cParams = ComponentHelper::getParams('com_contactus');

		if ($cParams->get('offline', 0))
		{
			$this->setMessage(Text::_('COM_CONTACTUS_ITEM_ERR_OFFLINE'), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_contactus&view=Item.add', false));

			return true;
		}

		/** @var SiteApplication $app */
		$app = Factory::getApplication();
		$app->allowCache(false);

		$app     = Factory::getApplication();
		$model   = $this->getModel();
		$data    = $this->input->post->get('jform', [], 'array');
		$context = "$this->option.edit.$this->context";
		[$form, $error,] = $this->cmsObjectSafeCall($model, 'getForm', $data, false);

		if (!$form)
		{
			$app->enqueueMessage($error, 'error');

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
		[$validData, $error, $errors] = $this->cmsObjectSafeCall($model, 'validate', $form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Push up to three validation messages out to the user.
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
		[$isSaved, $error,] = $this->cmsObjectSafeCall($model, 'save', $validData);

		if (!$isSaved)
		{
			$app->setUserState('com_contactus.edit.item.data', $validData);

			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');
			$this->setRedirect(Route::_('index.php?option=com_contactus&view=Item.add', false));

			return false;
		}

		$app->setUserState('com_contactus.edit.item.data', null);

		$url = 'index.php?option=com_contactus&view=thanks' . ($model->getState('isSpam', false) ? '&layout=spammer'
				: '');

		$this->setRedirect(Route::_($url, false));

		return true;
	}
}
