<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Dispatcher;

defined('_JEXEC') or die;

use Akeeba\Component\ContactUs\Administrator\Mixin\TriggerEventTrait;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Throwable;

class Dispatcher extends ComponentDispatcher
{
	use TriggerEventTrait;

	protected $defaultController = 'items';

	public function dispatch()
	{
		try
		{
			// Check the minimum supported PHP version
			$minPHPVersion = '7.4.0';
			$softwareName  = 'Akeeba ContactUs';

			if (!version_compare(PHP_VERSION, $minPHPVersion))
			{
				throw new \RuntimeException(
					sprintf(
						'%s requires PHP %s or later',
						$softwareName,
						$minPHPVersion
					)
				);
			}

			$jLang = $this->app->getLanguage();
			$jLang->load($this->option, JPATH_ADMINISTRATOR, null, true, true);
			$jLang->load($this->option, JPATH_SITE, null, true, true);

			// Apply the view and controller from the request, falling back to the default view/controller if necessary
			$this->applyViewAndController();

			// Dispatch the component
			$this->triggerEvent('onBeforeDispatch');

			parent::dispatch();

			// This will only execute if there is no redirection set by the Controller
			$this->triggerEvent('onAfterDispatch');
		}
		catch (Throwable $e)
		{
			$title = 'Akeeba Contactus';
			$isPro = false;

			if (!(include_once __DIR__ . '/../../tmpl/commontemplates/errorhandler.php'))
			{
				throw $e;
			}
		}
	}

	private function applyViewAndController(): void
	{
		// Handle a custom default controller name
		$view       = $this->input->getCmd('view', $this->defaultController);
		$controller = $this->input->getCmd('controller', $view);
		$task       = $this->input->getCmd('task', 'main');

		// Check for a controller.task command.
		if (strpos($task, '.') !== false)
		{
			// Explode the controller.task command.
			[$controller, $task] = explode('.', $task);
		}

		$this->input->set('view', $controller);
		$this->input->set('controller', $controller);
		$this->input->set('task', $task);
	}

}