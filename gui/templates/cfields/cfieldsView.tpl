{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsView.tpl

@internal revisions
*}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{$cfViewAction="lib/cfields/cfieldsView.php"}

{$cfImportAction="lib/cfields/cfieldsImport.php?goback_url="}
{$importCfieldsAction="$basehref$cfImportAction$basehref$cfViewAction"}

{$cfExportAction="lib/cfields/cfieldsExport.php?goback_url="}
{$exportCfieldsAction="$basehref$cfExportAction$basehref$cfViewAction"}


{lang_get var="labels"
          s="name,label,type,title_cfields_mgmt,manage_cfield,btn_cfields_create,
             btn_export,btn_import,btn_goback,sort_table_by_column,enabled_on_context,
             display_on_exec,available_on"}

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
      <th class="{$noSortableColumnClass}">{$labels.enabled_on_context}</th>
  		<th class="{$noSortableColumnClass}">{$labels.display_on_exec}</th>
  		<th>{$tlImages.sort_hint}{$labels.available_on}</th>
  	</tr>
  
   	{foreach key=cf_id item=cf_def from=$gui->cf_map}
   	<tr>
   	<td width="20%" class="bold"><a href="lib/cfields/cfieldsEdit.php?do_action=edit&cfield_id={$cf_def.id}"
   	                    title="{$labels.manage_cfield}">{$cf_def.name|escape}</a></td>
   	<td >{$cf_def.label|escape}</td>
   	<td width="5%">{$gui->cf_types[$cf_def.type]}</td>
    <td width="10%">{$cf_def.enabled_on_context}</td>
   	<td align="center" width="5%">{if $cf_def.show_on_execution}<img src="{$tlImages.checked}">{/if} </td>
   	<td width="10%">{lang_get s=$cf_def.node_description}</td>
   	
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