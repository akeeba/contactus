<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Extension;

defined('_JEXEC') || die;

use Akeeba\Component\ContactUs\Administrator\Service\Html\ContactUs as ContactUsHtml;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\Database\DatabaseInterface;
use Psr\Container\ContainerInterface;

class ContactUsComponent extends MVCComponent implements
	BootableExtensionInterface, CategoryServiceInterface, RouterServiceInterface
{
	use HTMLRegistryAwareTrait;
	use RouterServiceTrait;
	use CategoryServiceTrait;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function boot(ContainerInterface $container)
	{
		$dbo = $container->has(DatabaseInterface::class) ? $container->get('DatabaseDriver') : Factory::getDbo();

		$this->getRegistry()->register('contactus', new ContactUsHtml($dbo));
	}
}