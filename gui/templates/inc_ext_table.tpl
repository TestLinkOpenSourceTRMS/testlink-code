{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_ext_table.tpl
Purpose: rendering of Ext Js table

@internal revisions
@since 2.0
20120930 - franciscom - refactored for Smarty 3.0
*}


{*
 IMPORTANT:
 Following functions uses global JS variables created 
 using exttable.class.php

 @url http://extjs.com/deploy/dev/examples/grid/array-grid.html
*}
{lang_get var="labels" s="expand_collapse_groups, show_all_columns,
	show_all_columns_tooltip, default_state, multisort, multisort_tooltip, export_to_csv,
	multisort_button_tooltip, button_refresh, btn_reset_filters, caption_nav_filters"}
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

// Functions for MultiSort
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
		tooltip: '{$labels.multisort_button_tooltip|escape:javascript}',
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
	Ext.state.Manager.setProvider(new Ext.ux.JsonCookieProvider());
	{foreach from=$gui->tableSet key=idx item=matrix}
		{$tableID=$matrix->tableID}

		store['{$tableID}'] = new Ext.data.GroupingStore({
			reader: new Ext.data.ArrayReader({},
				fields['{$tableID}'])
				{if $matrix->groupByColumn >= 0}
					,groupField: '{$matrix->groupByColumn}'
				{/if}
				// enable sorting by a default column
				{if !is_null($matrix->sortByColumn)}
					,sortInfo:{ field:'{$matrix->sortByColumn}',direction:'{$matrix->sortDirection}' }
				{/if}
			});
		store['{$tableID}'].loadData(tableData['{$tableID}']);
			
		grid['{$tableID}'] = new Ext.ux.SlimGridPanel({
			id: '{$tableID}',
			store: store['{$tableID}'],
			{if !$matrix->storeTableState}
				stateful: false,
			{/if}

			// init grid plugins
			plugins: [
				//init filter plugin
				filters = new Ext.ux.grid.GridFilters({
					// set to local filtering (on client side)
					local: true,
					showMenu: true,
					menuFilterText: '{$labels.caption_nav_filters|escape:javascript}'
				})
			],
			
			//show toolbar
			{if $matrix->toolbar->show}
			tbar: tbar = new Ext.ux.TableToolbar({
				table_id: '{$tableID}',
				showExpandCollapseGroupsButton: {$matrix->toolbar->showButton->expandCollapseGroups|@json_encode},
				showAllColumnsButton: {$matrix->toolbar->showButton->showAllColumns|@json_encode},
				{if $matrix->toolbar->showButton->defaultState && $matrix->toolbar->show && $matrix->storeTableState}
				showDefaultStateButton: true,
				{else}
				showDefaultStateButton: false,
				{/if}
				showRefreshButton: {$matrix->toolbar->showButton->refresh|@json_encode},

				labels: {
					button_refresh: '{$labels.button_refresh|escape:javascript}',
					default_state:  '{$labels.default_state|escape:javascript}',
					expand_collapse_groups: '{$labels.expand_collapse_groups|escape:javascript}',
					show_all_columns: '{$labels.show_all_columns|escape:javascript}',
					show_all_columns_tooltip: '{$labels.show_all_columns_tooltip|escape:javascript}'
				}
				
				//init plugins for multisort
				{if $matrix->multiSortEnabled}
					// minor syntax error causing problems on IE6
					,plugins: [
						reorderer = new Ext.ux.ToolbarReorderer(),
						droppable = new Ext.ux.ToolbarDroppable({
						
							createItem: function(data) {
								var column = this.getColumnFromDragDrop(data);
								return createSorterButton({
									text    : column.header,
									sortData: {
										field: column.dataIndex,
										direction: "DESC"
									}
								}, '{$tableID}');
							},

							canDrop: function(dragSource, event, data) {
								var sorters = getSorters('{$tableID}'),
                					column  = this.getColumnFromDragDrop(data);

								for (var i=0; i < sorters.length; i++) {
									if (sorters[i].field == column.dataIndex) return false;
								}

								return true;
							},
							
							//after multisort buttons changed sort data again 
							afterLayout: function () {
								doSort('{$tableID}');
							},

							getColumnFromDragDrop: function(data) {
								var index    = data.header.cellIndex,
								colModel = grid['{$tableID}'].colModel,
								column   = colModel.getColumnById(colModel.getColumnId(index));

								return column;
							}
						})
					],  //END plugins
					items: [], //necessary line as otherwise plugins will throw an error
					listeners: {
						scope    : this,
						reordered: function(button) {
							updateButtons(button,'{$tableID}', false);
						}
					}
				{/if} //end plugins for multisort
			}), //END tbar
			{/if} //ENDIF showtoolbar
			
			listeners: {
			{if $matrix->multiSortEnabled && $matrix->toolbar->show}
				scope: this,
				render: function() {
					dragProxy = grid['{$tableID}'].getView().columnDrag;
					ddGroup = dragProxy.ddGroup;
					droppable.addDDGroup(ddGroup);
				}
			{/if}
			}, //END listeners

			view: new Ext.grid.GroupingView({
				forceFit: true
				{if $matrix->showGroupItemsCount}
					,groupTextTpl: '{ text } ({ [values.rs.length] } { [values.rs.length > 1 ? "Items" : "Item"] })'
				{/if}
				{if $matrix->hideGroupedColumn}
					,hideGroupedColumn:true
				{/if}
				}), //END view
			
			columns: columnData['{$tableID}']
			
			{$matrix->getGridSettings()}
			
		}); //END grid

		// Export Button
		{if $matrix->toolbar->showButton->export && $matrix->toolbar->show}
			tbar.add({
				xtype: 'exporttoolbarbutton',
				component: grid['{$tableID}'],
				formatter: new Ext.ux.Exporter.CSVFormatter(),
				text: '{$labels.export_to_csv}',
				iconCls: 'tbar-export',
				store: store['{$tableID}']
			});
		{/if}
		
		// add button to reset filters
		// TODO : show only as active if at least 1 column is filtered
		{if $matrix->toolbar->showButton->resetFilters && $matrix->toolbar->show}
			tbar.add({
				text: '{$labels.btn_reset_filters|escape:javascript}',
				iconCls: 'tbar-reset-filters',
				handler: function() {
					grid['{$tableID}'].filters.clearFilters();
				}
			});
		{/if}

		//MULTISORT
		{if $matrix->multiSortEnabled && $matrix->toolbar->show}
			
			// add button seperator
			tbar.add({
				xtype: 'tbseparator'
			});
			
			// add multisort text
			tbar.add({
				handleMouseEvents: false,
				iconCls: 'tbar-info',
				iconAlign: 'right',
				text: '{$labels.multisort|escape:javascript}',
				tooltip: '{$labels.multisort_tooltip|escape:javascript}',
				tooltipType: 'title'
			});
		{/if}
		//END MULTISORT
		
		//render grid
		grid['{$tableID}'].render('{$tableID}_target');
	{/foreach}

}); // END Ext.onReady
</script>