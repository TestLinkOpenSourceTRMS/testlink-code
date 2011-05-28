{*
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - View all platforms

@filesource platformsView.tpl

@internal revisions
20110409 - franciscom - BUGID 4368: Provide WYSIWYG Editor for platform notes
*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_action_onclick.tpl"} {* includes ext-js *}

{lang_get var='labels'
          s='th_notes,th_platform,th_delete,btn_import,btn_export,
             menu_manage_platforms,alt_delete_platform,warning_delete_platform,
             warning_cannot_delete_platform,delete,
             menu_assign_kw_to_tc,btn_create_platform'}

{lang_get s='warning_delete_platform' var="warning_msg"}
{lang_get s='warning_cannot_delete_platform' var="warning_msg_cannot_del"}
{lang_get s='delete' var="del_msgbox_title"}

{$viewAction="lib/platforms/platformsView.php"}

<script type="text/javascript">
<!--
/* All this stuff is needed for logic contained in inc_action_onclick.tpl */
var target_action=fRoot+'lib/platforms/platformsEdit.php?tproject_id='+{$gui->tproject_id};
target_action +='&doAction=do_delete&id=';
//-->
</script>
 
</head>
<body {$body_onload}>
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$labels.menu_manage_platforms}</h1>
{include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}
<div class="workBack">
{if $gui->platforms != ''}
	<table class="simple_tableruler sortable">
		<tr>
			<th width="30%">{$tlImages.toggle_api_info}{$tlImages.sort_hint}{$labels.th_platform}</th>
			<th>{$tlImages.sort_hint}{$labels.th_notes}</th>
			{if $gui->canManage != ""}
				<th>{$labels.th_delete}</th>
			{/if}
		</tr>
		{section name=platform loop=$gui->platforms}
		<tr>
			<td>
				<span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$gui->platforms[platform].id}</span>
				{if $gui->canManage != ""}
					<a href="lib/platforms/platformsEdit.php?doAction=edit&id={$gui->platforms[platform].id}&tproject_id={$gui->tproject_id}">
				{/if}
				{$gui->platforms[platform].name|escape}
				{if $gui->canManage != ""}
					</a>
				{/if}
			</td>
			{* when using rich webeditor strip_tags is needed - franciscom *}
			<td>{$gui->platforms[platform].notes|strip_tags|strip|truncate:#PLATFORM_NOTES_TRUNCATE_LEN#}</td>
			{if $gui->canManage != ""}
			<td class="clickable_icon">
				{if $gui->platforms[platform].linked_count eq 0}
				<img style="border:none;cursor: pointer;"	alt="{$labels.alt_delete_platform}"
						title="{$labels.alt_delete_platform}"	src="{$tlImages.delete}"
						onclick="action_confirmation({$gui->platforms[platform].id},
							      '{$gui->platforms[platform].name|escape:'javascript'|escape}', '{$del_msgbox_title|escape:'javascript'}','{$warning_msg|escape:'javascript'}');" />
				{else}
					<img style="border:none;cursor: pointer;" 	alt="{$labels.alt_delete_platform}"
						title="{$labels.alt_delete_platform}"	src="{$tlImages.delete_disabled}"
						onclick="alert_message_html('{$del_msgbox_title|escape:'javascript'}','{$warning_msg_cannot_del|replace:'%s':$gui->platforms[platform].name|escape:'javascript'}');" />
				{/if}
			</td>
			{/if}
		</tr>
		{/section}
	</table>
 {/if}
	
	<div class="groupBtn">	
		{if $gui->canManage != ""}
   		<form style="float:left" name="platform_view" id="platform_view" 
   			  method="post" action="lib/platforms/platformsEdit.php">
	  		<input type="hidden" name="doAction" value="" />
	  		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
		    <input type="submit" id="create_platform" name="create_platform" value="{$labels.btn_create_platform}"
		           onclick="doAction.value='create'"/>
		</form>
		{/if}	
		
     	<form style="float:left" name="platExport" id="platExport" method="post" action="lib/platforms/platformsExport.php">
     		<input type="hidden" name="goback_url" value="{$basehref|escape}{$viewAction|escape}"/>
	  		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
			<input type="submit" name="export_platforms" id="export_platforms"
		           style="margin-left: 3px;" value="{$labels.btn_export}" />
	  	</form>
		
		{if $gui->canManage != ""}       
     	<form name="platImport" id="platImport" method="post" action="lib/platforms/platformsImport.php">
     		<input type="hidden" name="goback_url" value="{$basehref|escape}{$viewAction|escape}"/>
	  		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
			<input type="submit" name="import_platforms" id="import_platforms"
		           style="margin-left: 3px;" value="{$labels.btn_import}" />
	  	</form>
        {/if}

    </div>
</div>
</body>
</html>