<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Site\View\Item;

defined('_JEXEC') or die();

use Akeeba\ContactUs\Site\Model\Items;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;

class Html extends BaseView
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

		/** @var Items $model */
		$model = $this->getModel();
		$model->bind($data);
	}

	/**
	 * @param   string  $name       Form field name
	 * @param   string  $namespace  CAPTCHA namespace
	 * @param   string  $id         Form field ID attribute
	 * @param   string  $class      Form field class
	 *
	 * @return  string  The CAPTCHA field
	 */
	public function getCaptchaField($name = 'captcha', $namespace = 'contactus', $id = null, $class = '')
	{
		/** @var Items $model */
		$model   = $this->getModel();
		$captcha = $model->getCaptchaObject();

		if (is_null($captcha))
		{
			return '';
		}

		if (strpos($class, 'required') === false)
		{
			$class .= ' required';
		}

		if (empty($id))
		{
			$id = $name;
		}

		return $captcha->display($name, $id, $class);
	}
}
