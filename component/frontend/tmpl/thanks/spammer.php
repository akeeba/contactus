<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>

<div class="card border-danger">
	<div class="card-header bg-danger text-white">
		<h2>
			<?= Text::_('COM_CONTACTUS_THANKYOU_MSG_SPAM_HEADER') ?>
		</h2>
	</div>
	<div class="card-body">
		<p>
			<?= Text::_('COM_CONTACTUS_THANKYOU_MSG_SPAM_BODY') ?>
		</p>
		<div>
			<a href="<?= Route::_('index.php?option=com_contactus') ?>" class="btn btn-outline-primary">
				<span class="icon icon-arrow-left"></span>
				<?= Text::_('COM_CONTACTUS_THANKYOU_MSG_SPAM_BTN') ?>
			</a>
		</div>
	</div>
</div>