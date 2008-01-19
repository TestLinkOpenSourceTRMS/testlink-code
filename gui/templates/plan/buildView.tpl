{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildView.tpl,v 1.8 2008/01/19 17:12:46 franciscom Exp $

Purpose: smarty template - Show existing builds

Rev:
    20080116 - franciscom - added option to show/hide id useful for API 
                            removed testplan id from title
    20080109 - franciscom - added sort table by JS
    20071007 - franciscom - delete on click logic refactored 
    20070921 - franciscom - BUGID  - added strip_tags|strip to notes
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{assign var="managerURL" value="lib/plan/buildNew.php"}
{assign var="editAction" value="$managerURL?do_action=edit&build_id="}
{assign var="deleteAction" value="$managerURL?do_action=delete&build_id="}
{assign var="createAction" value="$managerURL?do_action=create"}


{lang_get s='warning_delete_build' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{lang_get var="labels" 
          s='title_build_2,test_plan,th_title,th_description,th_active,
             th_open,th_delete,alt_edit_build,alt_active_build,
             alt_open_build,alt_delete_build,no_builds,btn_build_create,
             builds_description,sort_table_by_column,th_id'}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>
</head>

<body {$body_onload}>

<h1>{$labels.title_build_2}{$smarty.const.TITLE_SEP_TYPE3}{$labels.test_plan}{$smarty.const.TITLE_SEP}{$tplan_name|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" result=$sqlResult item="build"}

{* ------------------------------------------------------------------------------------------- *}
<div id="existing_builds">
  {if $the_builds ne ""}
    {* table id MUST BE item_view to use show/hide API info *}
  	<table id="item_view" class="simple  sortable" style="width:80%">
  		<tr>
  			<th>{$toogle_api_info_img}{$sortHintIcon}{$labels.th_title}</th>
  			<th class="{$noSortableColumnClass}">{$labels.th_description}</th>
  			<th class="{$noSortableColumnClass}">{$labels.th_active}</th>
  			<th class="{$noSortableColumnClass}">{$labels.th_open}</th>
  			<th class="{$noSortableColumnClass}">{$labels.th_delete}</th>
  		</tr>
  		{foreach item=build from=$the_builds}
        	<tr>
  				<td><span class="api_info" style='display:none'>{$smarty.const.TL_API_ID_FORMAT|replace:"%s":$build.id}</span>
  				    <a href="{$editAction}{$build.id}" title="{$labels.alt_edit_build}">{$build.name|escape}
  					     {if $gsmarty_gui->show_icon_edit}
  					         <img style="border:none"
  					              alt="{$labels.alt_edit_build}" 
  					              title="{$labels.alt_edit_build}"
  					              src="{$smarty.const.TL_THEME_IMG_DIR}/icon_edit.png"/>
  					     {/if}    
  					  </a>   
  				</td>
  				<td>{$build.notes|strip_tags|strip|truncate:#BUILD_NOTES_TRUNCATE_LEN#}</td>
  				<td class="clickable_icon">
  				   {if $build.active eq 1} 
  				     <img style="border:none" 
  				            title="{$labels.alt_active_build}" 
  				            alt="{$labels.alt_active_build}" 
  				            src="{$checked_img}"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td class="clickable_icon">
  				   {if $build.is_open eq 1} 
  				     <img style="border:none" 
  				            title="{$labels.alt_open_build}" 
  				            alt="{$labels.alt_open_build}" 
  				            src="{$checked_img}"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td class="clickable_icon">
				       <img style="border:none;cursor: pointer;" 
  				            title="{$labels.alt_delete_build}" 
  				            alt="{$labels.alt_delete_build}" 
 					            onclick="delete_confirmation({$build.id},'{$build.name|escape:'javascript'}',
 					                                         '{$del_msgbox_title}','{$warning_msg}');"
  				            src="{$delete_img}"/>
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
