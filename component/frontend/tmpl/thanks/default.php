<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
?>

<div class="card border-success">
    <div class="card-header bg-success text-white">
        <h2>
		    <?= Text::_('COM_CONTACTUS_THANKYOU_MSG_HEADER') ?>
        </h2>
    </div>
	<div class="card-body">
		<p>
			<?= Text::_('COM_CONTACTUS_THANKYOU_MSG_BODY') ?>
		</p>
	</div>
</div>
