<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Site\Dispatcher;

defined('_JEXEC') or die;

use Akeeba\Component\ContactUs\Administrator\Controller\Mixin\TriggerEvent;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Throwable;

class Dispatcher extends ComponentDispatcher
{
	use TriggerEvent;

	protected $defaultController = 'item';

	public function dispatch()
	{
		// Check the minimum supported PHP version
		$minPHPVersion = '7.2.0';
		$softwareName  = 'Akeeba ContactUs';

		if (!@require_once JPATH_ADMINISTRATOR . '/components/com_contactus/tmpl/commontemplates/wrongphp.php')
		{
			return;
		}

		$jLang = $this->app->getLanguage();
		$jLang->load($this->option, JPATH_COMPONENT_ADMINISTRATOR, null, true, true);
		$jLang->load($this->option, JPATH_ADMINISTRATOR, null, true, true);
		$jLang->load($this->option, JPATH_SITE, null, true, true);

		try
		{
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

			if (!(include_once JPATH_ADMINISTRATOR . '/components/com_contactus/commontemplates/errorhandler.php'))
			{
				throw $e;
			}
		}
	}

	private function applyViewAndController(): void
	{
		$view = $this->input->getCmd('view');
		$task = $this->input->getCmd('task');

		// Check for a controller.task command.
		if (strpos($task ?? '', '.') !== false)
		{
			// Explode the controller.task command.
			[$view, $task] = explode('.', $task);
		}

		if (empty($view) && empty($task))
		{
			$view = 'item';
			$task = 'add';
		}
		elseif (empty($view))
		{
			$view = 'item';
		}

		$view = strtolower($view);

		$this->input->set('controller', $view);
		$this->input->set('task', $task);
		$this->input->set('view', $view);
	}

}