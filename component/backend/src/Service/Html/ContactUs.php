<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Service\Html;

defined('_JEXEC') || die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

class ContactUs
{
	private static $categories;

	/**
	 * The Joomla database driver
	 *
	 * @var DatabaseDriver
	 */
	private $dbo;

	public function __construct(DatabaseDriver $dbo)
	{
		$this->dbo = $dbo;
	}

	/**
	 * Format a date for display.
	 *
	 * Usage: HTMLHelper::_('contactus.dateFormat', $date, $format, $tzAware)
	 *
	 * The $tzAware parameter defines whether the formatted date will be timezone-aware. If set to false the formatted
	 * date will be rendered in the UTC timezone. If set to true the code will automatically try to use the logged in
	 * user's timezone or, if none is set, the site's default timezone (Server Timezone).
	 *
	 * @param   string       $date     The date to format
	 * @param   string|null  $format   The format string, default is whatever you specified in the component options
	 * @param   bool         $tzAware  Should the format be timezone aware? See notes above.
	 *
	 * @return  string
	 */
	public function dateFormat(string $date, ?string $format = null, bool $tzAware = true): string
	{
		// Which timezone should I use?
		if ($tzAware !== false)
		{
			try
			{
				$tzDefault = Factory::getApplication()->get('offset', 'GMT');
				$user      = Factory::getApplication()->getIdentity() ?: Factory::getUser();
				$tz        = $user->getParam('timezone', $tzDefault);
			}
			catch (Exception $e)
			{
				$tz = null;
			}
		}

		$jDate = new Date($date, $tz);

		if (empty($format))
		{
			$format = ComponentHelper::getParams('com_contactus')->get('dateformat', 'Y-m-d H:i T');
			$format = str_replace('%', '', $format);
		}

		return $jDate->format($format, true);
	}

	/**
	 * Formats a ContactUs category ID as a display title
	 *
	 * @param   int|null  $catId  The category ID
	 *
	 * @return  string  The category title, en-dash if not found
	 */
	public function categoryFormat(?int $catId): string
	{
		$categories = $this->getCategories();

		return empty($catId) ? '—' : ($categories[$catId] ?? '—');
	}

	/**
	 * Returns a category selection list
	 *
	 * @param   string      $name      HTML field name
	 * @param   array|null  $attribs   Attributes, passed to select.genericlist
	 * @param   int|null    $selected  Currently selected value
	 * @param   false       $idtag     HTML id attribute
	 *
	 * @return  string
	 */
	public function categories(string $name, ?array $attribs = null, ?int $selected = null, bool $idtag = false): string
	{
		$categories = $this->getCategories();

		$data = array_map(function ($catTitle, $catId) {
			return HTMLHelper::_('select.option', $catId, $catTitle);
		}, array_values($categories), array_keys($categories));

		array_unshift($data, HTMLHelper::_('select.option', 0, Text::_('COM_CONTACTUS_ITEMS_FIELD_CONTACTUS_CATEGORY_ID_SELECT')));

		return HTMLHelper::_('select.genericlist', $data, $name, $attribs, 'value', 'text', $selected, $idtag, false);
	}

	/**
	 * Returns a category ID to category title map
	 *
	 * @return  array
	 */
	protected function getCategories(): array
	{
		if (is_array(self::$categories))
		{
			return self::$categories;
		}

		$db    = $this->dbo;
		$query = $db->getQuery(true)
			->select([
				$db->quoteName('contactus_category_id'),
				$db->quoteName('title'),
			])->from($db->quoteName('#__contactus_categories'));

		try
		{
			self::$categories = $db->setQuery($query)->loadAssocList('contactus_category_id', 'title') ?? '';
		}
		catch (Exception $e)
		{
			self::$categories = [];
		}

		return self::$categories;
	}
}