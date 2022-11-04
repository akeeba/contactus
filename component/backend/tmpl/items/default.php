<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Akeeba\Component\ContactUs\Administrator\View\Items\HtmlView $this */

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getApplication()->getIdentity() ?: Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$nullDate  = Factory::getDbo()->getNullDate();

$i = 0;
?>

<form action="<?= Route::_('index.php?option=com_contactus&view=Items'); ?>"
      method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]) ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?= Text::_('INFO'); ?></span>
						<?= Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="articleList">
						<caption class="visually-hidden">
							<?= Text::_('COM_CONTACTUS_ITEMS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?= Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?= Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?= HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col">
								<?= Text::_('COM_CONTACTUS_ITEMS_FIELD_FROMNAME') ?>
							</th>
							<th scope="col">
								<?= HTMLHelper::_('searchtools.sort', 'COM_CONTACTUS_ITEMS_FIELD_SUBJECT', 'subject', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?= HTMLHelper::_('searchtools.sort', 'COM_CONTACTUS_ITEMS_FIELD_CREATED_ON', 'created_on', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?= Text::_('JPUBLISHED') ?>
							</th>
							<th scope="col" class="w-1 d-none d-md-table-cell">
								<?= HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'contactus_item_id', $listDirn, $listOrder); ?>
							</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $item) :?>
							<tr class="row<?= $i++ % 2; ?>">
								<td class="text-center">
									<?= HTMLHelper::_('grid.id', $i, $item->contactus_item_id, !(empty($item->locked_on) || ($item->locked_on === $nullDate)), 'cid', 'cb', $item->subject); ?>
								</td>

								<td>
									<?= $this->escape($item->fromname) ?> <br />
									<small class="text-muted">
										<?= $this->escape($item->fromemail) ?>
									</small>
								</td>

								<td>
									<div class="break-word">
										<?php if ($user->authorise('core.edit', 'com_contactus')): ?>
										<a href="<?= Route::_('index.php?option=com_contactus&task=item.edit&contactus_item_id=' . (int) $item->contactus_item_id); ?>"
										   title="<?= Text::_('JACTION_EDIT'); ?><?= $this->escape($item->subject); ?>">
											<?= $this->escape($item->subject); ?>
										</a>
										<?php else: ?>
											<?= $this->escape($item->subject); ?>
										<?php endif ?>
										<br/>
										<span class="text-muted">
											<?= HTMLHelper::_('contactus.categoryFormat', $item->contactus_category_id) ?>
										</span>
									</div>
								</td>

								<td>
									<?= HTMLHelper::_('contactus.dateFormat', $item->created_on) ?>
								</td>

								<td class="text-center">
									<?= HTMLHelper::_('jgrid.published', $item->enabled, $i, 'items.', $user->authorise('core.edit.state', 'com_contactus'), 'cb'); ?>
								</td>

								<td class="w-1 d-none d-md-table-cell">
									<?= $item->contactus_item_id ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // Load the pagination. ?>
					<?= $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?= HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>