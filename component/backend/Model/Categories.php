<?php
/**
 * @package        contactus
 * @copyright      Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Admin\Model;

defined('_JEXEC') or die();

use FOF30\Container\Container;
use FOF30\Model\DataModel;

/**
 * Model Akeeba\ContactUs\Admin\Model\Categories
 *
 * Fields:
 *
 * @property  int    $contactus_category_id
 * @property  string $title
 * @property  string $email
 * @property  bool   $sendautoreply
 * @property  string $autoreply
 * @property  int    $access
 * @property  string $language
 *
 * Filters:
 *
 * @method  $this  contactus_category_id()  contactus_category_id(int $v)
 * @method  $this  title()                  title(string $v)
 * @method  $this  email()                  email(string $v)
 * @method  $this  sendautoreply()          sendautoreply(bool $v)
 * @method  $this  autoreply()              autoreply(string $v)
 * @method  $this  access()                 access(int $v)
 * @method  $this  language()               language(string $v)
 * @method  $this  ordering()               ordering(int $v)
 * @method  $this  enabled()                enabled(bool $v)
 * @method  $this  created_on()             created_on(string $v)
 * @method  $this  created_by()             created_by(int $v)
 * @method  $this  modified_on()            modified_on(string $v)
 * @method  $this  modified_by()            modified_by(int $v)
 * @method  $this  locked_on()              locked_on(string $v)
 * @method  $this  locked_by()              locked_by(int $v)
 *
**/
class Categories extends DataModel
{
	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		$this->addBehaviour('Filters');
	}

}
