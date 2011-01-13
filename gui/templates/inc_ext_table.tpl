{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_ext_table.tpl,v 1.49.2.2 2011/01/13 17:57:19 mx-julian Exp $
Purpose: rendering of Ext Js table

@internal Revisions:
	 20110113 - Julian - BUGID 4169 - minor syntax error causing problems on IE6
	 20101116 - Julian - Localized Menu Item for GridFilters
	 20101022 - Julian - BUGID 3979 - Use grid filters for exttables
	 20101018 - eloff - Refactor export/collapse button
	                             show all columns button
	                             restore to default state
	                             refresh button
	 20100828 - eloff - Refactored the rendering of status
	 20100826 - eloff - BUGID 3714 - Use JsonCookieProvider
	 20100826 - Julian - fixed multisort feature for multiple tables
	                   - added checks if table state is stored
	 20100825 - eloff - Fix toolbars if multiple tables are rendered
	 20100824 - Julian - added refresh toolbar button
	                   - added function to remove multisort buttons
	 20100823 - Julian - quoted tableID as it is a string and no integer value
	                   - toolbar button to expand/collapse groups changed to not
	                     use toggleAllGroups() function anymore as this function
	                     did expand collapsed but also collapse expanded groups
	 20100819 - Julian - MultiSort (BUGID 3694), showGroupItemsCount
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
	show_all_columns_tooltip, default_state, multisort, multisort_tooltip,
	multisort_button_tooltip, button_refresh, btn_reset_filters, caption_nav_filters"}
{literal}
<script type="text/javascript" src="gui/javascript/ext_extensions.js" language="javascript"></script>
<script type="text/javascript">
/*
 statusRenderer() 
 translate this code to a localized string and applies formatting
*/
function statusRenderer(item)
{
	item.cssClass = item.cssClass || "";
	return "<span class=\""+item.cssClass+"\">" + item.text + "</span>";
}

/*
 statusCompare() 
 handles the sorting order by status. 
 It maps a status code to a number. 
 The statuses are then sorted by comparing those numbers.
 WARNING: Global coupling
          uses variable status_code_order
*/
function statusCompare(item)
{
	var order=0;
	order = status_code_order[item.value];
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
function createSorterButton(config, table) {
	config = config || {};
	Ext.applyIf(config, {
		listeners: {
			"click": function(button, e) {
				if(e.shiftKey == true) {
					button.destroy();
					doSort(table);
				} else {
					updateButtons(button, table, true);
				}
			}
		},
		iconCls: 'tbar-sort-' + config.sortData.direction.toLowerCase(),
		{/literal}tooltip: '{$labels.multisort_button_tooltip|escape:javascript}',{literal}
		tooltipType: 'title',
		multisort: 'yes',
		reorderable: true
	});

	return new Ext.Button(config);
};
    
function updateButtons(button,table,changeDirection){
	sortData = button.sortData;
	iconCls = button.iconCls;
	
	if (sortData != undefined) {
		if (changeDirection != false) {
			button.sortData.direction = button.sortData.direction.toggle('ASC','DESC');
			button.setIconClass(button.iconCls.toggle('tbar-sort-asc', 'tbar-sort-desc'));
		}
	}
	store[table].clearFilter();
	doSort(table);
}

function doSort(table){
	sorters = getSorters(table);
	store[table].sort(sorters, 'ASC');
}

function getSorters(table) {
var sorters = [];
	tbar = grid[table].getTopToolbar();
	Ext.each(tbar.find('multisort', 'yes'), function(button) {
		sorters.push(button.sortData);
	}, this);
	return sorters;
}
//End Functions for MultiSort

Ext.onReady(function() {
{/literal}
	Ext.state.Manager.setProvider(new Ext.ux.JsonCookieProvider());
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=$matrix->tableID}

		store['{$tableID}'] = new Ext.data.GroupingStore({ldelim}
			reader: new Ext.data.ArrayReader({ldelim}{rdelim},
				fields['{$tableID}'])
				{if $matrix->groupByColumn >= 0}
					,groupField: '{$matrix->groupByColumn}'
				{/if}
				// 20100816 - asimon - enable sorting by a default column
				{if !is_null($matrix->sortByColumn)}
					,sortInfo:{ldelim}field:'{$matrix->sortByColumn}',direction:'{$matrix->sortDirection}'{rdelim}
				{/if}
			{rdelim});
		store['{$tableID}'].loadData(tableData['{$tableID}']);
			
		grid['{$tableID}'] = new Ext.ux.SlimGridPanel({ldelim}
			id: '{$tableID}',
			store: store['{$tableID}'],
			{if !$matrix->storeTableState}
				stateful: false,
			{/if}

			// init grid plugins
			plugins: [
				//init filter plugin
				filters = new Ext.ux.grid.GridFilters({ldelim}
					// set to local filtering (on client side)
					local: true,
					showMenu: true,
					menuFilterText: '{$labels.caption_nav_filters|escape:javascript}'
				{rdelim})
			],
			
			//show toolbar
			{if $matrix->showToolbar}
			tbar: tbar = new Ext.ux.TableToolbar({ldelim}
				table_id: '{$tableID}',
				showExpandCollapseGroupsButton: {$matrix->toolbarExpandCollapseGroupsButton|@json_encode},
				showAllColumnsButton: {$matrix->toolbarShowAllColumnsButton|@json_encode},
				{if $matrix->toolbarDefaultStateButton && $matrix->showToolbar && $matrix->storeTableState}
				showDefaultStateButton: true,
				{else}
				showDefaultStateButton: false,
				{/if}
				showRefreshButton: {$matrix->toolbarRefreshButton|@json_encode},

				labels: {ldelim}
					button_refresh: '{$labels.button_refresh|escape:javascript}',
					default_state:  '{$labels.default_state|escape:javascript}',
					expand_collapse_groups: '{$labels.expand_collapse_groups|escape:javascript}',
					show_all_columns: '{$labels.show_all_columns|escape:javascript}',
					show_all_columns_tooltip: '{$labels.show_all_columns_tooltip|escape:javascript}'
				{rdelim}
				//init plugins for multisort
				{if $matrix->allowMultiSort}
					//BUGID 4169 - minor syntax error causing problems on IE6
					,plugins: [
						reorderer = new Ext.ux.ToolbarReorderer(),
						droppable = new Ext.ux.ToolbarDroppable({ldelim}
						
							createItem: function(data) {ldelim}
								var column = this.getColumnFromDragDrop(data);
								return createSorterButton({ldelim}
									text    : column.header,
									sortData: {ldelim}
										field: column.dataIndex,
										direction: "DESC"
									{rdelim}
								{rdelim}, '{$tableID}');
							{rdelim},

							canDrop: function(dragSource, event, data) {ldelim}
								var sorters = getSorters('{$tableID}'),
                					column  = this.getColumnFromDragDrop(data);

								for (var i=0; i < sorters.length; i++) {ldelim}
									if (sorters[i].field == column.dataIndex) return false;
								{rdelim}

								return true;
							{rdelim},
							
							//after multisort buttons changed sort data again 
							afterLayout: function () {ldelim}
								doSort('{$tableID}');
							{rdelim},

							getColumnFromDragDrop: function(data) {ldelim}
								var index    = data.header.cellIndex,
								colModel = grid['{$tableID}'].colModel,
								column   = colModel.getColumnById(colModel.getColumnId(index));

								return column;
							{rdelim}
						{rdelim})
					],  //END plugins
					items: [], //necessary line as otherwise plugins will throw an error
					listeners: {ldelim}
						scope    : this,
						reordered: function(button) {ldelim}
							updateButtons(button,'{$tableID}', false);
						{rdelim}
					{rdelim}
				{/if} //end plugins for multisort
			{rdelim}), //END tbar
			{/if} //ENDIF showtoolbar
			
			listeners: {ldelim}
			{if $matrix->allowMultiSort && $matrix->showToolbar}
				scope: this,
				render: function() {ldelim}
					dragProxy = grid['{$tableID}'].getView().columnDrag;
					ddGroup = dragProxy.ddGroup;
					droppable.addDDGroup(ddGroup);
				{rdelim}
			{/if}
			{rdelim}, //END listeners

			view: new Ext.grid.GroupingView({ldelim}
				forceFit: true
				{if $matrix->showGroupItemsCount}
					,groupTextTpl: '{ldelim}text{rdelim} ({ldelim}[values.rs.length]{rdelim} ' +
						'{ldelim}[values.rs.length > 1 ? "Items" : "Item"]{rdelim})'
				{/if}
				{if $matrix->hideGroupedColumn}
					,hideGroupedColumn:true
				{/if}
				{rdelim}), //END view
			
			columns: columnData['{$tableID}']
			
			{$matrix->getGridSettings()}
			
		{rdelim}); //END grid

		//Export Button
		{if $matrix->showExportButton && $matrix->showToolbar}
			tbar.add(new Ext.ux.Exporter.Button({ldelim}
				component: grid['{$tableID}'],
				store: store['{$tableID}']
			{rdelim}));
		{/if}
		
		// add button to reset filters
		// TODO : show only as active if at least 1 column is filtered
		{if $matrix->toolbarResetFiltersButton && $matrix->showToolbar}
			tbar.add({ldelim}
				text: '{$labels.btn_reset_filters|escape:javascript}',
				iconCls: 'tbar-reset-filters',
				handler: function() {ldelim}
					grid['{$tableID}'].filters.clearFilters();
				{rdelim}
			{rdelim});
		{/if}

		//MULTISORT
		{if $matrix->allowMultiSort && $matrix->showToolbar}
			
			//add button seperator
			tbar.add({ldelim}
				xtype: 'tbseparator'
			{rdelim});
			
			//add multisort text
			tbar.add({ldelim}
				handleMouseEvents: false,
				iconCls: 'tbar-info',
				iconAlign: 'right',
				text: '{$labels.multisort|escape:javascript}',
				tooltip: '{$labels.multisort_tooltip|escape:javascript}',
				tooltipType: 'title'
			{rdelim});
		{/if}
		//END MULTISORT
		
		//render grid
		grid['{$tableID}'].render('{$tableID}_target');
	{/foreach}

}); // END Ext.onReady
</script>
