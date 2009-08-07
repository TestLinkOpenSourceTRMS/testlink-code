{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: platformsView.tpl,v 1.1 2009/08/07 06:58:10 franciscom Exp $
Purpose: smarty template - View all platforms
*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var='labels'
          s='th_notes,th_platform,th_delete,btn_import,btn_export,
             menu_manage_platforms,alt_delete_platform,
             menu_assign_kw_to_tc,btn_create_platform'}

{lang_get s='warning_delete_platform' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/platforms/platformsEdit.php?doAction=do_delete&id=';
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
			  <img style="border:none;cursor: pointer;"
			       alt="{$labels.alt_delete_platform}"
             title="{$labels.alt_delete_platform}"
             src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"			     
				     onclick="delete_confirmation({$gui->platforms[platform].id},
				              '{$gui->platforms[platform]->name|escape:'javascript'|escape'}',
				              '{$del_msgbox_title}','{$warning_msg}');" />
			</td>
			{/if}
		</tr>
		{/section}
	</table>
  {/if}
	

	<div class="groupBtn">	

	<form name="platform_view" id="platform_view" method="post" action="lib/platforms/platformsEdit.php">
  	  <input type="hidden" name="doAction" value="" />

		  {if $gui->canManage ne ""}
	    <input type="submit" id="create_platform" name="create_platform"
	           value="{$labels.btn_create_platform}"
	           onclick="doAction.value='create'"/>

		  {/if}

	</form>
	</div>
</div>

</body>
</html>