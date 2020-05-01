<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

use Akeeba\ContactUs\Admin\Model\Categories;
use Akeeba\ContactUs\Admin\View\Categories\Html;
use FOF30\Utils\FEFHelper\BrowseView;
use FOF30\Utils\SelectOptions;

defined('_JEXEC') or die();

/**
 * @var  Html       $this
 * @var  Categories $row
 * @var  Categories $model
 */

$model = $this->getModel();

?>

@extends('any:lib_fof30/Common/browse')

@section('browse-filters')
    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('title')
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('email')
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        {{ BrowseView::accessFilter('access') }}
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        {{ BrowseView::publishedFilter('enabled', 'JENABLED') }}
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        @selectfilter('language', SelectOptions::getOptions('languages'))
    </div>

@stop

@section('browse-table-header')
    {{-- ### HEADER ROW ### --}}
    <tr>
        {{-- Drag'n'drop reordering --}}
        <th width="20">
            @jhtml('FEFHelper.browse.orderfield', 'ordering')
        </th>
        {{-- Row select --}}
        <th width="20">
            @jhtml('FEFHelper.browse.checkall')
        </th>
        {{-- Title --}}
        <th>
            @sortgrid('title')
        </th>
        {{-- Email --}}
        <th>
            @sortgrid('email')
        </th>
        {{-- Access --}}
        <th>
            @sortgrid('access', 'JFIELD_ACCESS_LABEL')
        </th>
        {{-- Enabled --}}
        <th width="60">
            @sortgrid('enabled', 'JENABLED')
        </th>
        {{-- Language --}}
        <th>
            @sortgrid('language')
        </th>
    </tr>
@stop

@section('browse-table-body-withrecords')
    {{-- Table body shown when records are present. --}}
	<?php $i = 0; ?>
    @foreach($this->items as $row)
        <tr>
            {{-- Drag'n'drop reordering --}}
            <td>
                @jhtml('FEFHelper.browse.order', 'ordering', $row->ordering)
            </td>
            {{-- Row select --}}
            <td>
                @jhtml('FEFHelper.browse.id', ++$i, $row->getId())
            </td>
            {{-- Title --}}
            <td>
                <a href="@route(BrowseView::parseFieldTags('index.php?option=com_contactus&view=Category&task=edit&id=[ITEM:ID]', $row))">
                    {{{ $row->title }}}
                </a>
            </td>
            {{-- Email --}}
            <td>
                <a href="@route(BrowseView::parseFieldTags('index.php?option=com_contactus&view=Category&task=edit&id=[ITEM:ID]', $row))">
                    {{{ $row->email }}}
                </a>
            </td>
            {{-- Access --}}
            <td>
                {{ BrowseView::getOptionName($row->access, SelectOptions::getOptions('access')) }}
            </td>
            {{-- Enabled --}}
            <td>
                @jhtml('FEFHelper.browse.published', $row->enabled, $i)
            </td>
            {{-- TODO Language --}}
            <td>
                {{{ BrowseView::getOptionName($row->language, \FOF30\Utils\SelectOptions::getOptions('languages', ['none' => 'COM_CONTACTUS_CATEGORIES_FIELD_LANGUAGE_NONE'])) }}}
            </td>
        </tr>
    @endforeach
@stop
