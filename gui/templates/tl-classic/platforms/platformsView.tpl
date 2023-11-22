{*
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - View all platforms

@filesource platformsView.tpl
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{$tplBN=$cfg_section}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
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
 
</head>
<body class="testlink" {$body_onload}>

<h1 class="title">{$labels.menu_manage_platforms}</h1>
{include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}
<div class="page-content">
{if $gui->platforms != ''}
  <form method="post" id="platformsView" 
    name="platformsView" action="{$managerAction}">
    
    <!-- this will be setted by the onclick -->
    <input type="hidden" name="doAction" id="doAction" value="">
    <input type="hidden" name="platform_id" id="platform_id" value="">
    <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">

	<table class="table table-bordered sortable">
	  <thead class="thead-dark">
    	<tr>
    	  <th width="30%">{$tlImages.toggle_api_info}{$tlImages.sort_hint}{$labels.th_platform}</th>
    	  <th>{$tlImages.sort_hint}{$labels.th_notes}</th>
          <th>{$labels.on_design}</th>
          <th>{$labels.on_exec}</th>
          <th>{$labels.platform_open_for_exec}</th>
    	  {if $gui->canManage != ""}
    		  <th class="{$noSortableColumnClass}"  
                  width="10%">{$labels.th_delete}
              </th>
    	  {/if}
    	</tr>
	  </thead>
	  {section name=platform loop=$gui->platforms}
      {$oplat=$gui->platforms[platform]}
      <tr>
        <td>
          <span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$oplat.id}</span>
        	{if $gui->canManage != ""}
        	  <a href="{$managerURL}&doAction=edit&id={$oplat.id}&tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}">
        	{/if}
        	{$oplat.name|escape}
        	{if $gui->canManage != ""}
        	  </a>
        	{/if}
        </td>
    	  {* when using rich webeditor strip_tags is needed *}
    	  <td>{if $gui->editorType == 'none'}{$oplat.notes|nl2br}{else}{$oplat.notes|strip_tags|strip|truncate:#PLATFORM_NOTES_TRUNCATE_LEN#}{/if}</td>
        <td class="clickable_icon">
            {if $oplat.enable_on_design==1} 
              <input type="image" style="border:none"
                                   id="disableDesign_{$oplat.id}"
                                   name="disableDesign"
                                   title="{$labels.active_click_to_change}" alt="{$labels.active_click_to_change}" 
                                   onClick = "platform_id.value={$oplat.id};doAction.value='disableDesign';"
                                   src="{$tlImages.on}"/>
            {else}
              <input type="image" style="border:none"
                                 id="enableDesign_{$oplat.id}"
                                 name="enableDesign"
                                 title="{$labels.inactive_click_to_change}" alt="{$labels.inactive_click_to_change}" 
                                 onClick = "doAction.value='enableDesign';platform_id.value={$oplat.id};"
                                 src="{$tlImages.off}"/>
            {/if}
        </td>
        <td class="clickable_icon">
            {if $oplat.enable_on_execution==1} 
              <input type="image" style="border:none"
                                   id="disableExec_{$oplat.id}"
                                   title="{$labels.active_click_to_change}" alt="{$labels.active_click_to_change}" 
                                   onClick = "doAction.value='disableExec';platform_id.value={$oplat.id};"
                                   src="{$tlImages.on}"/>
            {else}
              <input type="image" style="border:none" 
                                 id="enableExec_{$oplat.id}"
                                 title="{$labels.inactive_click_to_change}" alt="{$labels.inactive_click_to_change}" 
                                 onClick = "doAction.value='enableExec';platform_id.value={$oplat.id};"
                                 src="{$tlImages.off}"/>
            {/if}
        </td>
        <td class="clickable_icon">
            {if $oplat.is_open==1} 
              <input type="image" style="border:none"
                                   id="closeForExec_{$oplat.id}"
                                   title="{$labels.active_click_to_change}" alt="{$labels.active_click_to_change}" 
                                   onClick = "doAction.value='closeForExec';platform_id.value={$oplat.id};"
                                   src="{$tlImages.on}"/>
            {else}
              <input type="image" style="border:none" 
                                 id="openForExec_{$oplat.id}"
                                 title="{$labels.inactive_click_to_change}" alt="{$labels.inactive_click_to_change}" 
                                 onClick = "doAction.value='openForExec';platform_id.value={$oplat.id};"
                                 src="{$tlImages.off}"/>
            {/if}
        </td>




    	  {if $gui->canManage != ""}
    				<td class="clickable_icon">
            	{if $oplat.linked_count eq 0}
            		<img style="border:none;cursor: pointer;"	alt="{$labels.alt_delete_platform}"
            						title="{$labels.alt_delete_platform}"	src="{$tlImages.delete}"
            						onclick="delete_confirmation({$oplat.id},
            							      '{$oplat.name|escape:'javascript'|escape}', '{$del_msgbox_title|escape:'javascript'}','{$warning_msg|escape:'javascript'}');" />
    					{else}
        				<img style="border:none;cursor: pointer;"
                   	alt="{$labels.alt_delete_platform}"
        						title="{$labels.alt_delete_platform}"	src="{$tlImages.delete_disabled}"
        						onclick="alert_message_html('{$del_msgbox_title|escape:'javascript'}','{$warning_msg_cannot_del|replace:'%s':$oplat.name|escape:'javascript'}');" />
    					{/if}
    				</td>
    		{/if}
    		</tr>
    	{/section}
	</table>
{/if}

<!-- controls -->
{include file="platforms/{$tplBN}Controls.inc.tpl" suffix="Bottom"} 
</div>
</form>
</body>
</html>