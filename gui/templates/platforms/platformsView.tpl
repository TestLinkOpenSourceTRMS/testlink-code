{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: platformsView.tpl,v 1.6 2009/11/30 21:52:18 erikeloff Exp $
Purpose: smarty template - View all platforms

20091010 - franciscom - export XML feature
*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var='labels'
          s='th_notes,th_platform,th_delete,btn_import,btn_export,
             menu_manage_platforms,alt_delete_platform,
             menu_assign_kw_to_tc,btn_create_platform'}

{lang_get s='warning_delete_platform' var="warning_msg" }
{lang_get s='warning_cannot_delete_platform' var="warning_msg_cannot_del" }
{lang_get s='delete' var="del_msgbox_title" }

{assign var="viewAction" value="lib/platforms/platformsView.php"}
{assign var="dummy" value="lib/platforms/platformsImport.php?goback_url="}
{assign var="importAction" value="$basehref$dummy$basehref$viewAction"}


<script type="text/javascript">
<!--
	/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
	var del_action=fRoot+'lib/platforms/platformsEdit.php?doAction=do_delete&id=';
//-->
</script>
 
</head>
<body {$body_onload}>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$labels.menu_manage_platforms}</h1>

<div class="workBack">
{if $gui->platforms neq ''}
	<table class="simple sortable" style="width:95%">
		<tr>
			<th width="30%">{$sortHintIcon}{$labels.th_platform}</th>
			<th>{$sortHintIcon}{$labels.th_notes}</th>
			{if $gui->canManage != ""}
				<th>{$labels.th_delete}</th>
			{/if}
		</tr>
		{section name=platform loop=$gui->platforms}
		<tr>
			<td>
				{if $gui->canManage != ""}
					<a href="lib/platforms/platformsEdit.php?doAction=edit&amp;id={$gui->platforms[platform].id}">
				{/if}
				{$gui->platforms[platform].name|escape}
				{if $gui->canManage != ""}
					</a>
				{/if}
			</td>
			<td>{$gui->platforms[platform].notes|escape|nl2br}</td>
			{if $gui->canManage ne ""}
			<td class="clickable_icon">
				{if $gui->platforms[platform].linked_count eq 0}
				<img style="border:none;cursor: pointer;"
						alt="{$labels.alt_delete_platform}"
						title="{$labels.alt_delete_platform}"
						src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
						onclick="delete_confirmation({$gui->platforms[platform].id},
							'{$gui->platforms[platform].name|escape:'javascript'|escape}',
				              '{$del_msgbox_title}','{$warning_msg}');" />
				{else}
					<img style="border:none;cursor: pointer;"
						alt="{$labels.alt_delete_platform}"
						title="{$labels.alt_delete_platform}"
						src="{$smarty.const.TL_THEME_IMG_DIR}/trash_greyed.png"
						onclick="alert_message_html(
							'{$del_msgbox_title}','{$warning_msg_cannot_del|replace:'%s':$gui->platforms[platform].name}');" />
				{/if}
			</td>
			{/if}
		</tr>
		{/section}
	</table>
 {/if}
	
	<div class="groupBtn">	
   		<form style="float:left" name="platform_view" id="platform_view" method="post" action="lib/platforms/platformsEdit.php">
	  		<input type="hidden" name="doAction" value="" />
		  	{if $gui->canManage ne ""}
		    	<input type="submit" id="create_platform" name="create_platform"
		        	value="{$labels.btn_create_platform}"
		           	onclick="doAction.value='create'"/>
			  {/if}	
		</form>
     	<form name="platformsExport" id="platformsExport" method="post" action="lib/platforms/platformsExport.php">
     		<input type="hidden" name="goback_url" value="{$basehref|escape}{$viewAction|escape}"/>
			<input type="submit" name="export_platforms" id="export_platforms"
		         style="margin-left: 3px;" value="{$labels.btn_export}" />
		  	{if $gui->canManage ne ""}       
		  		<input type="button" name="import_platforms" id="import_platforms" 
		         	onclick="location='{$importAction}'" value="{$labels.btn_import}" />
       	  	{/if}
	  	</form>
    </div>
</div>
</body>
</html>
