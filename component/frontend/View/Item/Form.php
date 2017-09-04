<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2013-2017 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

namespace Akeeba\ContactUs\Site\View\Item;

use Akeeba\ContactUs\Site\Model\Items;
use FOF30\View\DataView\Form as BaseView;

class Form extends BaseView
{
	protected function onBeforeAdd()
	{
		parent::onBeforeAdd();

		$user = $this->container->platform->getUser();
		$name = '';

		if (!$user->guest)
		{
			$name = $user->name . ' [' . $user->username . ']';
		}

		$this->form->bind([
			'subject' => $this->input->getString('subject', ''),
			'body' => $this->input->getString('body', ''),
		]);

		$css = <<< CSS
#akeeba-renderjoomla input.input-xlarge {
	width: 50%;
}

#akeeba-renderjoomla input.input-xxlarge {
	width: 75%;
	font-size: 120%;
}
CSS;

		$this->addCssInline($css);
	}

}
