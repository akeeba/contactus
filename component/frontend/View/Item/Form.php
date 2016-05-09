<?php
/**
 * @package		contactus
 * @copyright	Copyright (c)2013-2016 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU General Public License version 2 or later
 */

namespace Akeeba\ContactUs\Site\View\Item;

use FOF30\View\DataView\Form as BaseView;

class Form extends BaseView
{
	protected function onBeforeAdd()
	{
		parent::onBeforeAdd();

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
