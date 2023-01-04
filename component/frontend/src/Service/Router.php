<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Site\Service;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;

class Router extends RouterView
{
	public function __construct(SiteApplication $app = null, AbstractMenu $menu = null)
	{
		$itemView = new RouterViewConfiguration('item');
		//$itemView->setKey('task');
		$this->registerView($itemView);

		$thanksView = new RouterViewConfiguration('thanks');
		$thanksView->addLayout('default');
		$thanksView->addLayout('spammer');
		$this->registerView($thanksView);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	public function build(&$query)
	{
		$view = 'item';

		if (strpos($query['task'] ?? '', '.') !== false)
		{
			[$view, $task] = explode('.', $query['task']);
		}

		$query['view'] = strtolower($query['view'] ?? ($view ?? ''));
		$query['task'] = strtolower($task ?? '');

		if (empty($query['task']))
		{
			unset ($query['task']);
		}

		return parent::build($query);
	}


}