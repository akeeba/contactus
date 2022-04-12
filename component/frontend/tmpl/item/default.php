<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Akeeba\Component\ContactUs\Site\View\Item\HtmlView $this */

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

$privacyPolicyPage = ComponentHelper::getParams('com_contactus')->get('privacypolicy', '/privacy.html');
$captcha           = $this->getCaptchaField();
?>
<form action="<?= Route::_('index.php?option=com_contactus&task=Item.save'); ?>"
	  aria-label="<?= Text::_('COM_CONTACTUS_TITLE_ITEMS_EDIT', true); ?>"
	  class="form-validate" id="profile-form" method="post" name="adminForm">

	<?= \Akeeba\Component\ContactUs\Site\Helper\ModuleRenderHelper::loadPosition('contactus_top') ?>

	<?php foreach ($this->form->getFieldsets() as $fieldset):
		if ($fieldset->name === 'consent') continue;
	?>
	<div class="card mb-2">
		<div class="card-body">
			<?php if (!empty($fieldset->description)): ?>
			<p class="alert alert-info">
				<?= Text::_($fieldset->description) ?>
			</p>
			<?php endif; ?>

			<?php foreach ($this->form->getFieldset($fieldset->name) as $field): ?>
				<div class="row mb-2">
					<div class="col-sm-3">
						<?= $field->label ?>
					</div>
					<div class="col-sm-9">
						<?= $field->input ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endforeach; ?>

	<?= \Akeeba\Component\ContactUs\Site\Helper\ModuleRenderHelper::loadPosition('contactus_middle') ?>

	<div class="card mb-2 border-success">
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-sm-9 offset-sm-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="jform[consent]" id="jform_consent">
						<label class="form-check-label" for="jform_consent">
							<?= $this->form->getField('consent')->title ?>
						</label>
					</div>
					<div class="form-text">
						<?= Text::sprintf('COM_CONTACTUS_ITEMS_FIELD_CONSENT_HELP', $privacyPolicyPage) ?>
					</div>
				</div>
			</div>

			<?php if (!empty($captcha)): ?>
			<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<?= $captcha ?>
					</div>
			</div>
			<?php endif; ?>

			<div class="row mb-3 mt-4">
				<div class="col-sm-9 offset-sm-3">
					<button type="submit" class="btn btn-success btn-lg w-100" name="btnSubmit">
						<span class="fa fa-envelope"></span>
						<?= Text::_('COM_CONTACTUS_ITEMS_BTN_SUBMIT') ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<?= \Akeeba\Component\ContactUs\Site\Helper\ModuleRenderHelper::loadPosition('contactus_bottom') ?>

	<?= HTMLHelper::_('form.token'); ?>
</form>
