<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class ContactusControllerItem extends F0FController
{
	/**
	 * Redirects the user to the Thank You page after successfully receiving the message
	 *
	 * @return  bool  True to continue processing
	 */
	protected function onAfterSave()
	{
		/** @var ContactusModelItems $model */
		$model = $this->getThisModel();

		if ($model->saveSuccessful)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_contactus&view=thankyou'));
		}

		return true;
	}
}