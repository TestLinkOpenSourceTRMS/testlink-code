{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildView.tpl,v 1.18 2010/10/17 09:46:37 franciscom Exp $

Purpose: smarty template - Show existing builds

Rev:
    20101017 - franciscom - image access refactored (tlImages)
    20090509 - franciscom - BUGID - display release_date
    20070921 - franciscom - BUGID  - added strip_tags|strip to notes
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{assign var="managerURL" value="lib/plan/buildEdit.php"}
{assign var="editAction" value="$managerURL?do_action=edit&amp;build_id="}
{assign var="deleteAction" value="$managerURL?do_action=do_delete&build_id="}
{assign var="createAction" value="$managerURL?do_action=create"}


{lang_get s='warning_delete_build' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{lang_get var="labels" 
          s='title_build_2,test_plan,th_title,th_description,th_active,
             th_open,th_delete,alt_edit_build,alt_active_build,
             alt_open_build,alt_delete_build,no_builds,btn_build_create,
             builds_description,sort_table_by_column,th_id,release_date'}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>
</head>

<body {$body_onload}>

<h1 class="title">{$labels.title_build_2}{$smarty.const.TITLE_SEP_TYPE3}{$labels.test_plan}{$smarty.const.TITLE_SEP}{$gui->tplan_name|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" result=$sqlResult item="build"}

{* ------------------------------------------------------------------------------------------- *}
<div id="existing_builds">
  {if $gui->buildSet ne ""}
    {* table id MUST BE item_view to use show/hide API info *}
  	<table id="item_view" class="simple_tableruler sortable">
  		<tr>
  			<th>{$tlImages.toggle_api_info}{$tlImages.sort_hint}{$labels.th_title}</th>
  			<th class="{$noSortableColumnClass}">{$labels.th_description}</th>
  			<th class="{$noSortableColumnClass}" style="width:90px;">{$labels.release_date}</th>
  			<th class="{$noSortableColumnClass}">{$labels.th_active}</th>
  			<th class="{$noSortableColumnClass}">{$labels.th_open}</th>
  			<th class="{$noSortableColumnClass}">{$labels.th_delete}</th>
  		</tr>
  		{foreach item=build from=$gui->buildSet}
        	<tr>
  				<td><span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$build.id}</span>
  				    <a href="{$editAction}{$build.id}" title="{$labels.alt_edit_build}">{$build.name|escape}
  					     {if $gsmarty_gui->show_icon_edit}
  					         <img style="border:none" alt="{$labels.alt_edit_build}" title="{$labels.alt_edit_build}"
  					              src="{$tlImages.edit}"/>
  					     {/if}    
  					  </a>   
  				</td>
  				<td>{$build.notes|strip_tags|strip|truncate:#BUILD_NOTES_TRUNCATE_LEN#}</td>
  				<td>{if $build.release_date != ''}{localize_date d=$build.release_date}{/if}</td>
  				<td class="clickable_icon">
  				   {if $build.active == 1} 
  				     <img style="border:none"  title="{$labels.alt_active_build}"  alt="{$labels.alt_active_build}" 
  				          src="{$tlImages.checked}"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td class="clickable_icon">
  				   {if $build.is_open == 1} 
  				     <img style="border:none"  title="{$labels.alt_open_build}"  alt="{$labels.alt_open_build}" 
  				          src="{$tlImages.checked}"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td class="clickable_icon">
				       <img style="border:none;cursor: pointer;"  title="{$labels.alt_delete_build}" 
  				            alt="{$labels.alt_delete_build}" 
 					            onclick="delete_confirmation({$build.id},'{$build.name|escape:'javascript'|escape}',
 					                                         '{$del_msgbox_title}','{$warning_msg}');"
  				            src="{$tlImages.delete}"/>
  				</td>
  			</tr>
  		{/foreach}
  	</table>
  {else}
  	<p>{$labels.no_builds}</p>
  {/if}
</div>
{* ------------------------------------------------------------------------------------------- *}

 <div class="groupBtn">
    <form method="post" action="{$createAction}" id="create_build">
      <input type="submit" name="create_build" value="{$labels.btn_build_create}" />
    </form>
  </div>

	<p>{$labels.builds_description}</p>

</div>

</body>
</html>
