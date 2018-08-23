<?php
/**
 * @package		contactus
 * @copyright   Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license		GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Admin\Model;

defined('_JEXEC') or die();

use FOF30\Container\Container;
use FOF30\Model\DataModel;

class Items extends DataModel
{
	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		$this->addBehaviour('Filters');
		$this->belongsTo('category', 'Categories');
	}

}
