<?php
/**
 * @package		contactus
 * @copyright Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 2 or later
 */

namespace Akeeba\ContactUs\Site\View\Item;

use FOF30\View\DataView\Form as BaseView;

class Form extends BaseView
{
	/**
	 * Form data saved in the session. Set by the Controller.
	 *
	 * @var   array
	 *
	 * @since 1.0.1
	 */
	public $sessionData = [];

	protected function onBeforeAdd()
	{
		parent::onBeforeAdd();

		$user = $this->container->platform->getUser();
		$name = '';

		if (!$user->guest)
		{
			$name = $user->name . ' [' . $user->username . ']';
		}

		$data = [
			'fromname'  => $this->input->getString('fromname', $this->sessionData['fromname']),
			'fromemail' => $this->input->getString('fromemail', $this->sessionData['fromemail']),
			'subject'   => $this->input->getString('subject', $this->sessionData['subject']),
			'body'      => $this->input->getString('body', $this->sessionData['body']),
		];

		$data['fromname'] = empty($data['fromname']) ? $name : $data['fromname'];
		$data['fromemail'] = empty($data['fromemail']) ? $user->email : $data['fromemail'];

		$this->form->bind($data);

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
