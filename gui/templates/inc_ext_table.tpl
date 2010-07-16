{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_ext_table.tpl,v 1.5 2010/07/16 19:47:12 erikeloff Exp $
Purpose: rendering of Ext Js table

rev :
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

Ext.onReady(function() {
{/literal}
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value="table_$idx"}

		store['{$tableID}'] = new Ext.data.GroupingStore({ldelim}
			reader: new Ext.data.ArrayReader({ldelim}{rdelim},
				fields['{$tableID}'])
			{if is_int($matrix->groupByColumn)}
			,groupField: 'idx{$matrix->groupByColumn}'
			{/if}
			{rdelim});
		store['{$tableID}'].loadData(tableData['{$tableID}']);
		grid['{$tableID}'] = new Ext.grid.GridPanel({ldelim}
			store: store['{$tableID}'],
			view: new Ext.grid.GroupingView({ldelim}
				forceFit: true
				{rdelim}),
				columns: columnData['{$tableID}']
				{$matrix->getGridSettings()}
			{rdelim});
	{/foreach}

	{foreach from=$gui->tableSet key=idx item=matrix}
    {assign var=tableID value="table_$idx"}
	  grid['{$tableID}'].render('{$tableID}');
  {/foreach}

});
</script>
