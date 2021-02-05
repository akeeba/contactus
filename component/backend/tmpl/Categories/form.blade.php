<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

use FOF40\Html\FEFHelper\BrowseView;
use FOF40\Html\SelectOptions;
use Akeeba\ContactUs\Admin\Model\Categories;
use Akeeba\ContactUs\Admin\View\Category\Html;

defined('_JEXEC') or die();

/**
 * @var  Html       $this
 * @var  Categories $item
 */

$item = $this->item;

?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')

    <div class="akeeba-panel--teal" id="akeeba-contactus-category-basic-configuration">
        <header class="akeeba-block-header">
            <h3>@lang('COM_CONTACTUS_CATEGORIES_GROUP_BASIC')</h3>
        </header>

        <div class="akeeba-form-group">
            <label for="title">
                @fieldtitle('title')
            </label>
            <input type="text" class="title" name="title" id="title" value="{{{ $item->title }}}">
        </div>

        <div class="akeeba-form-group">
            <label for="email">
                @fieldtitle('email')
            </label>
            <input type="text" class="email" name="email" id="email" value="{{{ $item->email }}}">
        </div>

        <div class="akeeba-form-group">
            <label for="access">
                @lang('JFIELD_ACCESS_LABEL')
            </label>
            @jhtml('FEFHelp.select.genericlist', \FOF40\Html\SelectOptions::getOptions('access'), 'access', ['list.select' => $item->access])
        </div>

        <div class="akeeba-form-group">
            <label for="language">
                @fieldtitle('language')
            </label>
            {{ BrowseView::genericSelect('language', \FOF40\Html\SelectOptions::getOptions('languages', ['none' => 'COM_CONTACTUS_CATEGORIES_FIELD_LANGUAGE_NONE']), $item->language, ['fof.autosubmit' => false, 'translate' => false]) }}
        </div>

        <div class="akeeba-form-group">
            <label for="enabled">
                @lang('JPUBLISHED')
            </label>
            @jhtml('FEFHelp.select.booleanswitch', 'enabled', $item->enabled)
        </div>
    </div>

    <div class="akeeba-panel--teal" id="akeeba-contactus-category-auto-reply">
        <header class="akeeba-block-header">
            <h3>@lang('COM_CONTACTUS_CATEGORIES_GROUP_AUTOREPLY')</h3>
        </header>

        <div class="akeeba-form-group">
            <label for="sendautoreply">
                @fieldtitle('sendautoreply')
            </label>
            @jhtml('FEFHelp.select.booleanswitch', 'sendautoreply', $item->sendautoreply)
        </div>

        <div class="akeeba-form-group">
            <label for="autoreply">
                @fieldtitle('autoreply')
            </label>
            <div class="akeeba-nofef">
                @jhtml('FEFHelp.edit.editor', 'autoreply', $item->autoreply)
            </div>
        </div>

    </div>
@stop