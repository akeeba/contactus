<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

use Akeeba\ContactUs\Admin\Model\Items;
use Akeeba\ContactUs\Admin\View\Items\Html;
use FOF40\Html\FEFHelper\BrowseView;
use FOF40\Html\SelectOptions;

defined('_JEXEC') or die();

/**
 * @var  Html  $this
 * @var  Items $row
 * @var  Items $model
 */

$model = $this->getModel();

?>
@include('admin:com_contactus/Common/phpversion_warning', [
	'softwareName'  => 'Contact Us!',
	'minPHPVersion' => '7.2.0',
])

@extends('any:lib_fof40/Common/browse')

@section('browse-filters')
    <div class="akeeba-filter-element akeeba-form-group">
        {{ BrowseView::modelFilter('contactus_category_id', 'title', 'Categories')  }}
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('fromname')
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('fromemail')
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('subject')
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        @if (version_compare(JVERSION, '3.999.999', 'le'))
            @jhtml('calendar', $model->created_on, 'created_on', 'created_on', '%Y-%m-%d', ['placeholder' => JText::_('COM_CONTACTUS_ITEMS_FIELD_CREATED_ON')])
        @else
            <input
                    type="datetime-local"
                    pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
                    name="created_on"
                    id="created_on"
                    value="{{{ $model->created_on }}}"
                    placeholder="@lang('COM_CONTACTUS_ITEMS_FIELD_CREATED_ON')"
            >
        @endif
    </div>

@stop

@section('browse-table-header')
    {{-- ### HEADER ROW ### --}}
    <tr>
        {{-- Drag'n'drop reordering --}}
        <th width="20">
            @jhtml('FEFHelp.browse.orderfield', 'ordering')
        </th>
        {{-- Row select --}}
        <th width="20">
            @jhtml('FEFHelp.browse.checkall')
        </th>
        {{-- fromname --}}
        <th>
            @sortgrid('fromname')
        </th>
        {{-- fromemail --}}
        <th>
            @sortgrid('fromemail')
        </th>
        {{-- created_on --}}
        <td width="13%">
            @sortgrid('created_on')
        </td>
        {{-- category --}}
        <td>
            @sortgrid('contactus_category_id')
        </td>
        {{-- subject --}}
        <th>
            @sortgrid('subject')
        </th>
    </tr>
@stop

@section('browse-table-body-withrecords')
    {{-- Table body shown when records are present. --}}
	<?php $i = 0; ?>
    @foreach($this->items as $row)
        <tr>
            <td>
                @jhtml('FEFHelp.browse.order', 'ordering', $row->ordering)
            </td>
            <td>
                @jhtml('FEFHelp.browse.id', ++$i, $row->getId())
            </td>
            <td>
                <a href="@route(BrowseView::parseFieldTags('index.php?option=com_contactus&view=Item&task=edit&id=[ITEM:ID]', $row))">
                    {{{ $row->fromname }}}
                </a>
            </td>
            <td>
                <a href="@route(BrowseView::parseFieldTags('index.php?option=com_contactus&view=Item&task=edit&id=[ITEM:ID]', $row))">
                    {{{ $row->fromemail }}}
                </a>
            </td>
            <td>
                {{ $this->formatDate($row->created_on) }}
            </td>
            <td>
                {{{  BrowseView::modelOptionName($row->contactus_category_id, 'Categories') }}}
            </td>
            <td>
                <a href="@route(BrowseView::parseFieldTags('index.php?option=com_contactus&view=Item&task=edit&id=[ITEM:ID]', $row))">
                    {{{ $row->subject }}}
                </a>
            </td>
        </tr>
    @endforeach
@stop
