{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsView.tpl

@internal revisions
20110331 - franciscom - make table sortable
20101017 - franciscom - image access refactored (tlImages)
20100315 - franciscom - added management on goback_url for export action
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="cfViewAction" value="lib/cfields/cfieldsView.php"}

{assign var="cfImportAction" value="lib/cfields/cfieldsImport.php?goback_url="}
{assign var="importCfieldsAction" value="$basehref$cfImportAction$basehref$cfViewAction"}

{assign var="cfExportAction" value="lib/cfields/cfieldsExport.php?goback_url="}
{assign var="exportCfieldsAction" value="$basehref$cfExportAction$basehref$cfViewAction"}


{lang_get var="labels"
          s="name,label,type,title_cfields_mgmt,manage_cfield,btn_cfields_create,
             show_on_design,enable_on_design,show_on_exec,enable_on_exec,btn_export,
             btn_import,btn_goback,sort_table_by_column,
             show_on_testplan_design,enable_on_testplan_design,available_on"}

{include file="inc_head.tpl" enableTableSorting="yes"}

<body>
<h1 class="title">{$labels.title_cfields_mgmt}</h1>
<div class="workBack">
{if $gui->cf_map != '' }
  <table id='item_view' class="simple_tableruler sortable">
  	<tr>
  		<th>{$tlImages.sort_hint}{$labels.name}</th>
  		<th>{$tlImages.sort_hint}{$labels.label}</th>
  		<th>{$tlImages.sort_hint}{$labels.type}</th>
  		<th class="{$noSortableColumnClass}">{$labels.enable_on_design}</th>
  		<th class="{$noSortableColumnClass}">{$labels.show_on_exec}</th>
  		<th class="{$noSortableColumnClass}">{$labels.enable_on_exec}</th>
  		<th class="{$noSortableColumnClass}">{$labels.enable_on_testplan_design}</th>
  		<th>{$tlImages.sort_hint}{$labels.available_on}</th>
  	</tr>
  
   	{foreach key=cf_id item=cf_def from=$gui->cf_map}
   	<tr>
   	<td class="bold"><a href="lib/cfields/cfieldsEdit.php?do_action=edit&cfield_id={$cf_def.id}"
   	                    title="{$labels.manage_cfield}">{$cf_def.name|escape}</a></td>
   	<td>{$cf_def.label|escape}</td>
   	<td>{$gui->cf_types[$cf_def.type]}</td>
   	<td align="center">{if $cf_def.enable_on_design eq 1}<img src="{$tlImages.checked}">{/if} </td>
   	<td align="center">{if $cf_def.show_on_execution eq 1}<img src="{$tlImages.checked}">{/if} </td>
   	<td align="center">{if $cf_def.enable_on_execution eq 1}<img src="{$tlImages.checked}">{/if} </td>
   	<td align="center">{if $cf_def.enable_on_testplan_design eq 1}<img src="{$tlImages.checked}">{/if} </td>
   	<td>{lang_get s=$cf_def.node_description}</td>
   	
   	</tr>
   	{/foreach}
  </table>
{/if} {* $cf_map != '' *}
  
  <div class="groupBtn">
    <span style="float: left">
    <form method="post" action="lib/cfields/cfieldsEdit.php?do_action=create">
      <input type="submit" name="create_cfield" value="{$labels.btn_cfields_create}" />
    </form>
    </span>
    <span>
	  <form method="post" action="{$exportCfieldsAction}" name="cfieldsExport">
		  <input type="submit" name="export_cf" id="export_cf"
		         style="margin-left: 3px;" value="{$labels.btn_export}" />
		         
		  <input type="button" name="import_cf" id="import_cf" 
		         onclick="location='{$importCfieldsAction}'" value="{$labels.btn_import}" />
       
	  </form>
	  </span>
  </div>

</div>
</body>
</html>