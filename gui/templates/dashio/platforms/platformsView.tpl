{*
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - View all platforms

@filesource platformsView.tpl
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{$tplBN=$cfg_section}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}
{include file="bootstrap.inc.tpl"}


{lang_get var='labels'
          s='th_notes,th_platform,th_delete,btn_import,btn_export,
             menu_manage_platforms,alt_delete_platform,warning_delete_platform,
             warning_cannot_delete_platform,delete,
             menu_assign_kw_to_tc,btn_create,
             on_design,on_exec,active_click_to_change,inactive_click_to_change,platform_open_for_exec'}

{lang_get s='warning_delete_platform' var="warning_msg" }
{lang_get s='warning_cannot_delete_platform' var="warning_msg_cannot_del" }
{lang_get s='delete' var="del_msgbox_title" }

{$tprjid=$gui->tproject_id}
{$managerURL="lib/platforms/platformsEdit.php?tproject_id=$tprjid"}
{$viewAction="lib/platforms/platformsView.php?tproject_id=$tprjid"}

{$managerAction="$basehref$managerURL"}

{$createAction="$managerAction&doAction=create"}

{$dummy="lib/platforms/platformsImport.php?tproject_id="}
{$importAction="$basehref$dummy$tprjid"}

{$dummy="lib/platforms/platformsExport.php?tproject_id="}
{$exportAction="$basehref$dummy$tprjid"}

<script type="text/javascript">
<!--
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$managerURL}'+'&doAction=do_delete&id=';
//-->
</script>

{if $gui->platforms != null}
  {$ll = $tlCfg->gui->{$cfg_section}->pagination->length}
  {* Do not initialize in DataTables -> DataTablesSelector="" *}
  {include file="DataTables.inc.tpl" DataTablesSelector="" DataTableslengthMenu=$ll}
  {include file="DataTablesColumnFiltering.inc.tpl" DataTablesSelector="#item_view" DataTableslengthMenu=$ll}
{/if}

</head>
<body class="testlink" {$body_onload}>
{include file="aside.tpl"}  
<div id="main-content">

<h1 class="{#TITLE_CLASS#}">{$labels.menu_manage_platforms}</h1>
{include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}
<div class="workBack">
{if $gui->platforms != null}
  <form method="post" id="platformsView" name="platformsView" action="{$managerAction}">
    
    <!-- this will be setted by the onclick -->
    <input type="hidden" name="doAction" id="doAction" value="">
    <input type="hidden" name="platform_id" id="platform_id" value="">
    <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">
    <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">

    
    <table class="{#item_view_table#}" id="item_view">
      <thead class="{#item_view_thead#}">
    	<tr>
    	  <th {#SMART_SEARCH#} width="30%">{$tlImages.toggle_api_info}{$labels.th_platform}</th>
    	  <th {#SMART_SEARCH#}>{$labels.th_notes}</th>
          <th {#NOT_SORTABLE#}>{$labels.on_design}</th>
          <th {#NOT_SORTABLE#}>{$labels.on_exec}</th>
          <th {#NOT_SORTABLE#}>{$labels.platform_open_for_exec}</th>
    	  {if $gui->canManage != ""}
    		  <th class="icon_cell" {#NOT_SORTABLE#}></th>
    	  {/if}
    	</tr>
	  </thead>
	  <tbody>
	  {section name=platform loop=$gui->platforms}
      {$oplat=$gui->platforms[platform]}
      <tr data-qa-item-name="{$oplat.name|escape}">
        <td data-qa-column="name">
        	{$oplat.name|escape}
          <span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$oplat.id}</span>
        	{if $gui->canManage != ""}
        	  <a href="{$managerURL}&doAction=edit&id={$oplat.id}&tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}">
        	{/if}
        	{if $gui->canManage != ""}
        	  </a>
        	{/if}
        </td>
    	  {* when using rich webeditor strip_tags is needed *}
    	  <td data-qa-column="notes">{if $gui->editorType == 'none'}{$oplat.notes|nl2br}{else}{$oplat.notes|strip_tags|strip|truncate:#PLATFORM_NOTES_TRUNCATE_LEN#}{/if}</td>
        <td class="clickable_icon" data-qa-column="enable_on_design"
            data-qa-enable_on_design="{$oplat.enable_on_design}">
            {if $oplat.enable_on_design==1} 
              <i class="fas fa-toggle-on" title="{$labels.active_click_to_change}"
                 onclick="doAction.value='disableDesign';platform_id.value={$oplat.id};$('#platformsView').submit();"></i>                            
            {else}
              <i class="fas fa-toggle-off" title="{$labels.active_click_to_change}"
                 onclick="doAction.value='enableDesign';platform_id.value={$oplat.id};$('#platformsView').submit();"></i>                            
            {/if}
        </td>
        <td class="clickable_icon" data-qa-column="enable_on_execution" data-qa-enable_on_execution="{$oplat.enable_on_execution}">
            {if $oplat.enable_on_execution==1} 
              <i class="fas fa-toggle-on" title="{$labels.active_click_to_change}"
                 onclick="doAction.value='disableExec';platform_id.value={$oplat.id};$('#platformsView').submit();"></i>                            
            {else}
              <i class="fas fa-toggle-off" title="{$labels.active_click_to_change}"
                 onclick="doAction.value='enableExec';platform_id.value={$oplat.id};$('#platformsView').submit();"></i>                            
            {/if}
        </td>


        <td class="clickable_icon">
            {if $oplat.is_open==1} 
              <i class="fas fa-toggle-on" title="{$labels.active_click_to_change}"
                 onclick="doAction.value='closeForExec';platform_id.value={$oplat.id};$('#platformsView').submit();"></i>                            
            {else}
              <i class="fas fa-toggle-off" title="{$labels.inactive_click_to_change}"   
                 onclick="doAction.value='openForExec';platform_id.value={$oplat.id};$('#platformsView').submit();"></i>       
            {/if}
        </td>

    	  {if $gui->canManage != ""}
    				<td class="clickable_icon">
            	{if $oplat.linked_count eq 0}
                <i class="fas fa-minus-circle" title="{$labels.alt_delete_platform}" 
                  onclick="delete_confirmation({$oplat.id},'{$oplat.name|escape:'javascript'|escape}', 
                                               '{$del_msgbox_title|escape:'javascript'}','{$warning_msg|escape:'javascript'}');" /></i>
    					{else}
        		   <i class="fas fa-heart" title="{$labels.alt_delete_platform}" 
        					onclick="alert_message_html('{$del_msgbox_title|escape:'javascript'}',
                                              '{$warning_msg_cannot_del|replace:'%s':$oplat.name|escape:'javascript'}');" /></i>
    					{/if}
    				</td>
    		{/if}
    		</tr>
    	{/section}
      </tbody>
	</table>
{/if}

<!-- controls -->
{include file="platforms/{$tplBN}Controls.inc.tpl" suffix="Bottom"} 
</div>
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>