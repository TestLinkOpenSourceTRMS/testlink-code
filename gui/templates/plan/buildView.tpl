{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource buildView.tpl

Purpose: smarty template - Show existing builds

*}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}

{$managerURL="lib/plan/buildEdit.php"}
{$editAction="$managerURL?do_action=edit&build_id="}
{$deleteAction="$managerURL?do_action=do_delete&build_id="}

{$tplanID=$gui->tplan_id}
{$createAction="$managerURL?do_action=create&tplan_id=$tplanID"}


{lang_get s='warning_delete_build' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{lang_get var="labels" 
          s='title_build_2,test_plan,th_title,th_description,th_active,
             th_open,th_delete,alt_edit_build,alt_active_build,
             alt_open_build,alt_delete_build,no_builds,btn_build_create,
             builds_description,sort_table_by_column,th_id,release_date,
             inactive_click_to_change,active_click_to_change,click_to_set_open,click_to_set_closed'}

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
{include file="inc_update.tpl" result=$sqlResult item="build" user_feedback=$gui->user_feedback}

{* --------------------------------------------------------------------------------- *}

{if count($gui->buildSet) > $tlCfg->gui->buildView->itemQtyForTopButton}
<div class="groupBtn">
  <form method="post" action="{$createAction}" id="create_build_top">
    <input type="submit" name="create_build_top" value="{$labels.btn_build_create}" />
  </form>
</div>
{/if}

<div id="existing_builds">
  {if $gui->buildSet ne ""}
  <form method="post" id="buildView" name="buildView" action="{$managerURL}">
    <input type="hidden" name="do_action" id="do_action" value="">
    <input type="hidden" name="build_id" id="build_id" value="">
    <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">


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
  				<td>{if $gui->editorType == 'none'}{$build.notes|nl2br}{else}{$build.notes}{/if}</td>
  				<td>{if $build.release_date != ''}{localize_date d=$build.release_date}{/if}</td>

          <td class="clickable_icon">
            {if $build.active==1} 
                <input type="image" style="border:none" id="set_build_active"
                       title="{$labels.active_click_to_change}" alt="{$labels.active_click_to_change}" 
                       onClick = "do_action.value='setInactive';build_id.value={$build.id};"
                       src="{$tlImages.on}"/>
              {else}
                <input type="image" style="border:none" id="set_build_inactive"
                     title="{$labels.inactive_click_to_change}" alt="{$labels.inactive_click_to_change}" 
                     onClick = "do_action.value='setActive';build_id.value={$build.id};"
                     src="{$tlImages.off}"/>
              {/if}
          </td>

          <td class="clickable_icon">
            {if $build.is_open==1} 
                <input type="image" style="border:none" id="close_build"
                       title="{$labels.click_to_set_closed}" alt="{$labels.click_to_set_closed}" 
                       onClick = "do_action.value='close';build_id.value={$build.id};"
                       src="{$tlImages.lock_open}"/>
              {else}
                <input type="image" style="border:none" id="open_build"
                     title="{$labels.click_to_set_open}" alt="{$labels.click_to_set_open}" 
                     onClick = "do_action.value='open';build_id.value={$build.id};"
                     src="{$tlImages.lock}"/>
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
   </form> 
  {else}
  	<p>{$labels.no_builds}</p>
  {/if}
</div>
{* ------------------------------------------------------------------------------------------- *}

<div class="groupBtn">
  <form method="post" action="{$createAction}" id="create_build_bottom">
    <input type="submit" name="create_build_bottom" value="{$labels.btn_build_create}" />
  </form>
</div>

<p>{$labels.builds_description}</p>
</div>

</body>
</html>
