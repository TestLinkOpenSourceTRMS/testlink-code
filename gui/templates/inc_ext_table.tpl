{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_ext_table.tpl,v 1.19 2010/08/19 15:24:22 mx-julian Exp $
Purpose: rendering of Ext Js table

@internal Revisions:
	 20100819 - Julian - MultiSort, showGroupItemsCount
	 20100818 - Julian - use toolbar object to generate toolbar
	 20100817 - Julian - toolbar items configurable, hideGroupedColumn
	 20100816 - Eloff - allow text selection in wrapped columns
	 20100816 - Julian - added function to allow column wrap (multiple lines per cell)
	 20100816 - asimon - enable sorting of ExtJS table by a default column
	 20100719 - Eloff - Update due to changes in tlExtTable
	 20100719 - Julian - Replaced lables for toolbar items with more general ones
	 20100716 - Eloff - Add toolbar and make panel stateful
	 20100716 - Eloff - Allow grouping on any column
	 20100715 - Eloff - Add grouping on first column
	 20090710 - Eloff - Added comment to explain magic numbers
	 20090709 - Eloff - Initial commit
*}


{*
 IMPORTANT:
 Following functions uses global JS variables created 
 using exttable.class.php

 @url http://extjs.com/deploy/dev/examples/grid/array-grid.html
*}
{lang_get var="labels" s="expand_collapse_groups, show_all_columns,
	show_all_columns_tooltip, multisort"}
{literal}
<script type="text/javascript">
/*
 statusRenderer() 
 translate this code to a localized string and applies formatting
*/
function statusRenderer(val)
{
  // This must be refactore using same styles that other features
  // and MUST NOT BE HARDCODED HERE
  //
	var style = "";
	if (val == "p")			style = "color: green; font-weight: bold";
	else if (val == "f")	style = "color: red; font-weight: bold";
	else if (val == "n")	style = "color: gray";
	else					style = "color: blue";

	return "<span style=\""+style+"\">" + status_code_label[val] + "</span>";
}

/*
 statusCompare() 
 handles the sorting order by status. 
 It maps a status code to a number. 
 The statuses are then sorted by comparing those numbers.
 WARNING: Global coupling
          uses variable status_code_order
*/
function statusCompare(val)
{
	var order=0;
	order = status_code_order[val];
	if( order == undefined )
	{
	  alert('Configuration Issue - test case execution status code: ' + val + ' is not configured ');
	  order = -1;
	}
	return order;	
}

function priorityRenderer(val)
{
	return prio_code_label[val];
}

function columnWrap(val){
    return '<div style="white-space:normal !important; -moz-user-select: text; -webkit-user-select: text;">'+ val +'</div>';
}

//Functions for MultiSort
function updateButtons(button,table){
	button.sortData.direction = button.sortData.direction.toggle('ASC','DESC');
	button.setIconClass(button.iconCls.toggle('x-tbar-page-next', 'x-tbar-page-prev'));
	doSort(table);
}

function doSort(table){
	sorters = getSorters();
	store[table].sort(sorters, 'ASC');
}

function getSorters() {
var sorters = [];   
	Ext.each(tbar.find('multisort', 'yes'), function(button) {
		sorters.push(button.sortData);
	}, this);
	return sorters;
}
//End Functions for MultiSort

Ext.onReady(function() {
{/literal}
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=$matrix->tableID}

		store['{$tableID}'] = new Ext.data.GroupingStore({ldelim}
			reader: new Ext.data.ArrayReader({ldelim}{rdelim},
				fields['{$tableID}'])
			{if $matrix->groupByColumn >= 0}
			,groupField: 'idx{$matrix->groupByColumn}'
			{/if}
			// 20100816 - asimon - enable sorting by a default column
			{if ($matrix->sortByColumn >= 0) && (count($matrix->multiSortButtons) < 2)}
			,sortInfo:{ldelim}field:'idx{$matrix->sortByColumn}',direction:'{$matrix->sortDirection}'{rdelim}
			{/if}
			{rdelim});
		store['{$tableID}'].loadData(tableData['{$tableID}']);
		
		grid['{$tableID}'] = new Ext.grid.GridPanel({ldelim}
			id: '{$tableID}',
			store: store['{$tableID}'],
			{if $matrix->show_toolbar}
			tbar: tbar = new Ext.Toolbar(),
			{/if}
			view: new Ext.grid.GroupingView({ldelim}
				forceFit: true
				{if $matrix->showGroupItemsCount}
				,groupTextTpl: '{ldelim}text{rdelim} ({ldelim}[values.rs.length]{rdelim} ' +
					'{ldelim}[values.rs.length > 1 ? "Items" : "Item"]{rdelim})'
				{/if}
				{if $matrix->hideGroupedColumn}
				,hideGroupedColumn:true
				{/if}
				{rdelim}),
				columns: columnData['{$tableID}']
				{$matrix->getGridSettings()}
			{rdelim});
	{/foreach}
	
	{if $matrix->toolbar_expand_collapse_groups_button && $matrix->show_toolbar}
		tbar.add({ldelim}
			text: '{$labels.expand_collapse_groups|escape:javascript}',
			iconCls: 'x-group-by-icon',
			handler: function () {ldelim}
				grid['{$tableID}'].getView().toggleAllGroups();
			{rdelim}
		{rdelim});
		{/if}
		
		{if $matrix->toolbar_show_all_columns_button && $matrix->show_toolbar}
		tbar.add({ldelim}
			text: '{$labels.show_all_columns|escape:javascript}',
			tooltip: '{$labels.show_all_columns_tooltip|escape:javascript}',
			tooltipType: 'title',
			iconCls: 'x-cols-icon',
			handler: function (button, state) {ldelim}
				var cm = grid['{$tableID}'].getColumnModel();
				for (var i=0;i<cm.getColumnCount();i++) {ldelim}
					//do not show grouped column if hideGroupedColumn is true
					if (grid['{$tableID}'].getView().hideGroupedColumn == false ||
						store['{$tableID}'].groupField != 'idx'+i) {ldelim}
						cm.setHidden(i, false);
					{rdelim}
				{rdelim}
			{rdelim}
		{rdelim});
	{/if}
	
	//MULTISORT
	{if count($matrix->multiSortButtons) >= 2 && $matrix->show_toolbar}
		tbar.add({ldelim}
			xtype: 'tbseparator'
		{rdelim});
	
		tbar.add({ldelim}
			xtype: 'tbtext',
			text: '{$labels.multisort|escape:javascript}'
		{rdelim});
			
		{foreach from=$matrix->multiSortButtons key=id item=button}
			fieldname = grid['{$tableID}'].getColumnModel().getColumnHeader('{$button.field}');
			tbar.add({ldelim}
				text: fieldname,
				//todo: find better symbol 'sort-asc', 'sort-desc' dont want to show
				{if $button.direction == 'ASC'}
					iconCls: 'x-tbar-page-prev',
					{else}
						iconCls: 'x-tbar-page-next',
					{/else}
				{/if}
				multisort: 'yes',
				sortData: {ldelim}
					field: 'idx{$button.field}',
					direction: '{$button.direction}'
				{rdelim},
				listeners: {ldelim}
					click: function (button,table) {ldelim}
						updateButtons(button,{$tableID});
						{rdelim}
				{rdelim}
			{rdelim});
		{/foreach}
	{/if}

	{foreach from=$gui->tableSet key=idx item=matrix}
    {assign var=tableID value=$matrix->tableID}
	  grid['{$tableID}'].render('{$tableID}_target');
	  {if count($matrix->multiSortButtons) >= 2}
	  	doSort({$tableID});
	  {/if}
  {/foreach}

});
</script>
