<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Akeeba\Component\ContactUs\Administrator\View\Category\HtmlView $this */

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

?>
<form action="<?php echo Route::_('index.php?option=com_contactus&view=Category&layout=edit&contactus_category_id=' . (int) $this->item->contactus_category_id); ?>"
	  aria-label="<?php echo Text::_('COM_CONTACTUS_TITLE_CATEGORIES_' . ( (int) $this->item->contactus_category_id === 0 ? 'ADD' : 'EDIT'), true); ?>"
	  class="form-validate" id="profile-form" method="post" name="adminForm">

	<div class="card-body">
		<?php foreach ($this->form->getFieldset('basic') as $field) {
			echo $field->renderField();
		} ?>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
