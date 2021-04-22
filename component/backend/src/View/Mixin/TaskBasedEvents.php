<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\Component\ContactUs\Administrator\View\Mixin;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Controller\Mixin\TriggerEvent;

trait TaskBasedEvents
{
	use TriggerEvent;

	public function display($tpl = null)
	{
		$task = $this->getModel()->getState('task');

		$eventName = 'onBefore' . ucfirst($task);
		$this->triggerEvent($eventName, [&$tpl]);

		parent::display($tpl);

		$eventName = 'onAfter' . ucfirst($task);
		$this->triggerEvent($eventName, [&$tpl]);
	}
}