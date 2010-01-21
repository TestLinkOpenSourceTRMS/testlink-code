{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: cfieldsView.tpl,v 1.7 2010/01/21 22:05:10 franciscom Exp $ 

rev :
     20090503 - franciscom - BUGID 2425 - commented show_on_design and show_on_testplan_design 
                                          till new implementation
     
     20080810 - franciscom - BUGID 1650 (REQ)
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="cfViewAction" value="lib/cfields/cfieldsView.php"}
{assign var="cfImportAction" value="lib/cfields/cfieldsImport.php?goback_url="}
{assign var="importCfieldsAction" value="$basehref$cfImportAction$basehref$cfViewAction"}

{lang_get var="labels"
          s="name,label,type,title_cfields_mgmt,manage_cfield,btn_cfields_create,
             show_on_design,enable_on_design,show_on_exec,enable_on_exec,btn_export,
             btn_import,btn_goback,
             show_on_testplan_design,enable_on_testplan_design,available_on"}

{include file="inc_head.tpl"}
<body>
<h1 class="title">{$labels.title_cfields_mgmt}</h1>
<div class="workBack">
{if $gui->cf_map != '' }
  <table class="simple" style="width: 90%">
  	<tr>
  		<th>{$labels.name}</th>
  		<th>{$labels.label}</th>
  		<th>{$labels.type}</th>
  		<th>{$labels.enable_on_design}</th>
  		<th>{$labels.show_on_exec}</th>
  		<th>{$labels.enable_on_exec}</th>
  		<th>{$labels.enable_on_testplan_design}</th>
  		<th>{$labels.available_on}</th>
  	</tr>
  
   	{foreach key=cf_id item=cf_def from=$gui->cf_map}
   	<tr>
   	<td class="bold"><a href="lib/cfields/cfieldsEdit.php?do_action=edit&cfield_id={$cf_def.id}"
   	                    title="{$labels.manage_cfield}">{$cf_def.name|escape}</a></td>
   	<td>{$cf_def.label|escape}</td>
   	<td>{$gui->cf_types[$cf_def.type]}</td>
   	<td align="center">{if $cf_def.enable_on_design eq 1}<img src="{$checked_img}">{/if} </td>
   	<td align="center">{if $cf_def.show_on_execution eq 1}<img src="{$checked_img}">{/if} </td>
   	<td align="center">{if $cf_def.enable_on_execution eq 1}<img src="{$checked_img}">{/if} </td>
   	<td align="center">{if $cf_def.enable_on_testplan_design eq 1}<img src="{$checked_img}">{/if} </td>
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
	  <form method="post" action="lib/cfields/cfieldsExport.php" name="cfieldsExport">
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