<?php
/**
 * @package    contactus
 * @copyright  Copyright (c)2013-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3 or later
 */

use Akeeba\ContactUs\Admin\Model\Keys;
use Akeeba\ContactUs\Admin\View\Keys\Html;
use FOF40\Html\FEFHelper\BrowseView;
use FOF40\Html\SelectOptions;

defined('_JEXEC') or die();

/**
 * @var  Html $this
 * @var  Keys $row
 * @var  Keys $model
 */

$model = $this->getModel();

?>

@extends('any:lib_fof40/Common/browse')

@section('browse-filters')
    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('email')
    </div>

    <div class="akeeba-filter-element akeeba-form-group">
        {{ BrowseView::publishedFilter('enabled', 'JENABLED') }}
    </div>
@stop

@section('browse-table-header')
    {{-- ### HEADER ROW ### --}}
    <tr>
        {{-- Row select --}}
        <th width="20">
            @jhtml('FEFHelp.browse.checkall')
        </th>
        {{-- Email --}}
        <th>
            @sortgrid('email')
        </th>
        {{-- Enabled --}}
        <th width="60">
            @sortgrid('enabled', 'JENABLED')
        </th>
    </tr>
@stop

@section('browse-table-body-withrecords')
    {{-- Table body shown when records are present. --}}
	<?php $i = 0; ?>
    @foreach($this->items as $row)
        <tr>
            {{-- Row select --}}
            <td>
                @jhtml('FEFHelp.browse.id', ++$i, $row->getId())
            </td>
            {{-- Email --}}
            <td>
                <a href="@route(BrowseView::parseFieldTags('index.php?option=com_contactus&view=Keys&task=edit&id=[ITEM:ID]', $row))">
                    {{{ $row->email }}}
                </a>
            </td>
            {{-- Enabled --}}
            <td>
                @jhtml('FEFHelp.browse.published', $row->enabled, $i)
            </td>
        </tr>
    @endforeach
@stop
