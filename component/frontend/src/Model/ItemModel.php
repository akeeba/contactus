<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\Component\ContactUs\Site\Model;

defined('_JEXEC') or die;

use Akeeba\Component\ContactUs\Administrator\Model\ItemModel as AdminItemModel;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;

class ItemModel extends AdminItemModel
{
	public function getCaptchaObject($namespace = 'contactus')
	{
		try
		{
			/** @var SiteApplication $app */
			$app = Factory::getApplication();
		}
		catch (\Exception $e)
		{
			return null;
		}

		$plugin = $app->getParams()->get('captcha', $app->get('captcha'));

		if ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null)
		{
			return null;
		}

		return Captcha::getInstance($plugin, ['namespace' => $namespace]);
	}

}