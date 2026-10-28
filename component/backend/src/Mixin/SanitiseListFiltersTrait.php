<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Administrator\Mixin;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Keeps malformed `filter[...]` and `list[...]` request values out of the list model state and the user session.
 *
 * Joomla stores whatever `filter` and `list` arrays it gets in the user session, and the search tools bar renders
 * them back. A value of the wrong type (e.g. `filter[search][]=x` or `list[limit][]=5`) makes either the model or the
 * search tools bar throw on every later visit, until the session ends.
 *
 * @since  4.3.0
 */
trait SanitiseListFiltersTrait
{
	/**
	 * Replaces filter and list values of the wrong type with an empty string, in the request and in the user state.
	 *
	 * Call it before `parent::populateState()`.
	 *
	 * @param   string[]  $arrayFilters  Names of the filters which legitimately hold a list of scalar values
	 *
	 * @return  void
	 * @since   4.3.0
	 */
	protected function sanitiseListFilters(array $arrayFilters = []): void
	{
		/** @var ListModel $this */
		$app   = Factory::getApplication();
		$input = $app->getInput();

		foreach (['filter' => $arrayFilters, 'list' => []] as $name => $arrayKeys)
		{
			$key = $this->context . '.' . $name;

			// The request
			$value = $input->get($name, null, 'raw');

			if ($value !== null)
			{
				$input->set($name, $this->sanitiseListArray($value, $arrayKeys));
			}

			// The user state, in case a previous request poisoned it
			$value = $app->getUserState($key);

			if ($value !== null)
			{
				$app->setUserState($key, $this->sanitiseListArray($value, $arrayKeys));
			}
		}
	}

	/**
	 * Returns an array whose values are all scalars, or lists of scalars for the keys in $arrayKeys.
	 *
	 * @param   mixed     $value      The raw value
	 * @param   string[]  $arrayKeys  Keys which may hold a list of scalars
	 *
	 * @return  array
	 * @since   4.3.0
	 */
	private function sanitiseListArray($value, array $arrayKeys): array
	{
		if (!is_array($value))
		{
			return [];
		}

		foreach ($value as $k => $v)
		{
			if (is_scalar($v) || $v === null)
			{
				continue;
			}

			if (is_array($v) && in_array($k, $arrayKeys, true))
			{
				$value[$k] = array_values(array_filter($v, 'is_scalar'));

				continue;
			}

			$value[$k] = '';
		}

		return $value;
	}
}
