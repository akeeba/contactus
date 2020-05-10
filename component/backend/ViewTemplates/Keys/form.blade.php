<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

use FOF30\Utils\FEFHelper\Html as FEFHtml;
use FOF30\Utils\FEFHelper\BrowseView;
use FOF30\Utils\SelectOptions;
use Akeeba\ContactUs\Admin\Model\Categories;
use Akeeba\ContactUs\Admin\View\Category\Html;

defined('_JEXEC') or die();

/**
 * @var  Html       $this
 * @var  Categories $item
 */

$item = $this->item;

?>
@extends('any:lib_fof30/Common/edit')

@section('edit-form-body')

    <div class="akeeba-panel--teal" id="akeeba-contactus-category-basic-configuration">
        <header class="akeeba-block-header">
            <h3>@lang('COM_CONTACTUS_KEYS_GROUP_BASIC')</h3>
        </header>

        <div class="akeeba-form-group">
            <label for="email">
                @fieldtitle('email')
            </label>
            <input type="email" class="email" name="email" id="email" value="{{{ $item->email }}}">
        </div>

        <div class="akeeba-form-group">
            <label for="enabled">
                @lang('JPUBLISHED')
            </label>
            @jhtml('FEFHelper.select.booleanswitch', 'enabled', $item->enabled)
        </div>

        <div class="akeeba-form-group">
            <label for="pubkey">
                @fieldtitle('pubkey')
            </label>
            <textarea rows="10" cols="72" name="pubkey" id="pubkey">{{ $item->pubkey }}</textarea>
        </div>

    </div>
@stop
