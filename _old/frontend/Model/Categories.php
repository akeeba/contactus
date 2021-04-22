<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Site\Model;

defined('_JEXEC') or die();

use Akeeba\ContactUs\Admin\Model\Categories as AdminCategories;
use FOF40\Container\Container;

class Categories extends AdminCategories
{
	public function __construct(Container $container, array $config = [])
	{
		parent::__construct($container, $config);

		$this->addBehaviour('language');
	}

}
