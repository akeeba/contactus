<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Admin\Model;

defined('_JEXEC') or die();

use FOF40\Container\Container;
use FOF40\Model\DataModel;

/**
 * Model Akeeba\ContactUs\Admin\Model\Items
 *
 * Fields:
 *
 * @property  int     $contactus_item_id
 * @property  int     $contactus_category_id
 * @property  string  $fromname
 * @property  string  $fromemail
 * @property  string  $subject
 * @property  string  $body
 * @property  string  $token
 *
 * Filters:
 *
 * @method  $this  contactus_item_id()      contactus_item_id(int $v)
 * @method  $this  contactus_category_id()  contactus_category_id(int $v)
 * @method  $this  fromname()               fromname(string $v)
 * @method  $this  fromemail()              fromemail(string $v)
 * @method  $this  subject()                subject(string $v)
 * @method  $this  body()                   body(string $v)
 * @method  $this  enabled()                enabled(bool $v)
 * @method  $this  token()                  token(string $v)
 * @method  $this  created_on()             created_on(string $v)
 * @method  $this  created_by()             created_by(int $v)
 * @method  $this  modified_on()            modified_on(string $v)
 * @method  $this  modified_by()            modified_by(int $v)
 * @method  $this  locked_on()              locked_on(string $v)
 * @method  $this  locked_by()              locked_by(int $v)
 *
 * Relations:
 *
 * @property  Categories  $category
 *
**/
class Items extends DataModel
{
	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		$this->addBehaviour('Filters');
		$this->belongsTo('category', 'Categories');
	}

}
