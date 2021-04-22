<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Akeeba\Component\ContactUs\Administrator\View\Item\HtmlView $this */

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

?>
<form action="<?php echo Route::_('index.php?option=com_contactus&view=Item&layout=edit&contactus_item_id=' . (int) $this->item->contactus_item_id); ?>"
	  aria-label="<?php echo Text::_('COM_CONTACTUS_TITLE_ITEMS_EDIT', true); ?>"
	  class="form-validate" id="profile-form" method="post" name="adminForm">

	<div class="card mb-2">
		<div class="card-header">
			<h3>
				<?= Text::_('COM_CONTACTUS_ITEMS_GROUP_BASIC') ?>
			</h3>
		</div>
		<div class="card-body">
			<?php foreach ($this->form->getFieldset('basic') as $field) {
				echo $field->renderField();
			} ?>
		</div>
	</div>

	<div class="card">
		<div class="card-header">
			<h3>
				<?= Text::_('COM_CONTACTUS_ITEMS_GROUP_MESSAGE') ?>
			</h3>
		</div>
		<div class="card-body">
			<?php foreach ($this->form->getFieldset('message') as $field) {
				echo $field->renderField();
			} ?>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
