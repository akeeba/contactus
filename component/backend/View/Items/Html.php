<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Admin\View\Items;

use FOF30\Date\Date;
use FOF30\Model\DataModel;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\Factory;

defined('_JEXEC') or die();

class Html extends BaseView
{
	protected function onBeforeBrowse()
	{
		/** @var DataModel $model */
		$model = $this->getModel();

		$order = $model->getState('filter_order', $model->getIdFieldName(), 'cmd');
		$dir   = $model->getState('filter_order_Dir', 'DESC', 'cmd');

		$model->setState('filter_order', $order);
		$model->setState('filter_order_Dir', $dir);

		parent::onBeforeBrowse();
	}


	/**
	 * Format a date for display.
	 *
	 * The $tzAware parameter defines whether the formatted date will be timezone-aware. If set to false the formatted
	 * date will be rendered in the UTC timezone. If set to true the code will automatically try to use the logged in
	 * user's timezone or, if none is set, the site's default timezone (Server Timezone). If set to a positive integer
	 * the same thing will happen but for the specified user ID instead of the currently logged in user.
	 *
	 * @param   string    $date     The date to format
	 * @param   string    $format   The format string, default is whatever you specified in the component options
	 * @param   bool|int  $tzAware  Should the format be timezone aware? See notes above.
	 *
	 * @return string
	 */
	public function formatDate($date, $format = null, $tzAware = true)
	{
		// Which timezone should I use?
		$tz = null;

		if ($tzAware !== false)
		{
			$userId    = is_bool($tzAware) ? null : (int) $tzAware;

			try
			{
				$tzDefault = Factory::getApplication()->get('offset');
			}
			catch (\Exception $e)
			{
				$tzDefault = new \DateTimeZone('GMT');
			}

			$user      = Factory::getUser($userId);
			$tz        = $user->getParam('timezone', $tzDefault);
		}

		$jDate = new Date($date, $tz);

		if (empty($format))
		{
			$format = $this->container->params->get('dateformat', 'Y-m-d H:i T');
			$format = str_replace('%', '', $format);
		}

		return $jDate->format($format, true);
	}

}