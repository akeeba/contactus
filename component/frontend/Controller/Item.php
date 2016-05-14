<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2013-2016 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

namespace Akeeba\ContactUs\Site\Controller;

use Akeeba\ContactUs\Site\Controller\Mixin\PredefinedTaskList;
use Akeeba\ContactUs\Site\Model\Items;
use FOF30\Container\Container;
use FOF30\Controller\DataController;

defined('_JEXEC') or die();

class Item extends DataController
{
	use PredefinedTaskList;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = [
			'add',
		    'save'
		];
	}

	/**
	 * Redirects the user to the Thank You page after successfully receiving the message
	 *
	 * @return  bool  True to continue processing
	 */
	protected function onAfterSave()
	{
		/** @var Items $model */
		$model = $this->getModel();

		if ($model->saveSuccessful)
		{
			$this->setRedirect(\JRoute::_('index.php?option=com_contactus&view=ThankYou'));
		}

		return true;
	}
}