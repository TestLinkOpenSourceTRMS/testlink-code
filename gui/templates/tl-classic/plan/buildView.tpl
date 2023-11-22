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


{* ------------------------------------------------------------------------------------------------ *}
{* 
   IMPORTANT DEVELOPMENT NOTICE 
   Because we are using also DataTablesColumnFiltering
   We MUST NOT Initialize the Data Table on DataTables.inc.tpl.
   We got this effect with DataTablesOID=""
*}
  {* Data Tables Config Area - BEGIN*}
  {$gridHTMLID="item_view"}
  {* Do not initialize in DataTables.inc.tpl -> DataTablesSelector="" *}
  {include file="DataTables.inc.tpl" DataTablesSelector=""}
  {include 
    file="DataTablesColumnFiltering.inc.tpl" 
    DataTablesSelector="#{$gridHTMLID}" 
    DataTablesLengthMenu=$tlCfg->gui->{$cfg_section}->pagination->length
  }
  {* Data Tables Config Area - End*}
{* ------------------------------------------------------------------------------------------------ *}


{include file="bootstrap.inc.tpl"}
</head>

<body {$body_onload} class="testlink">

<h1 class="title">{$labels.title_build_2}{$smarty.const.TITLE_SEP_TYPE3}{$labels.test_plan}{$smarty.const.TITLE_SEP}{$gui->tplan_name|escape}</h1>

<div class="page-content">
{include file="inc_update.tpl" result=$sqlResult item="build" user_feedback=$gui->user_feedback}


{if null != $gui->buildSet && 
   (count($gui->buildSet) > $tlCfg->gui->buildView->itemQtyForTopButton)}
<div class="page-content">
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
  	<table id="{$gridHTMLID}" class="table table-bordered no-sortable">
      <thead class="thead-dark">
    		<tr>
    			<th data-draw-filter="smartsearch">{$tlImages.toggle_api_info}{$labels.th_title}</th>
    			<th data-draw-filter="smartsearch">{$labels.th_description}</th>
    			<th data-draw-filter="smartsearch"  style="width:90px;">{$labels.release_date}</th>

          {* Custom Fields *}
          {if $gui->cfieldsColumns != null}
             {foreach item=cflbl from=$gui->cfieldsColumns}
              <th data-draw-filter="regexp" title="{$cflbl}">{$cflbl}</th>
             {/foreach}
          {/if}

    			<th {#NOT_SORTABLE#}>{$labels.th_active}</th>
    			<th {#NOT_SORTABLE#}>{$labels.th_open}</th>
    			<th {#NOT_SORTABLE#}>{$labels.th_delete}</th>
    		</tr>
      </thead>
      <tbody>
  		{foreach item=build from=$gui->buildSet}
        	<tr>
  				<td>
  				    <a href="{$editAction}{$build.id}" title="{$labels.alt_edit_build}">{$build.name|escape}
  					     {if $gsmarty_gui->show_icon_edit}
  					         <img style="border:none" alt="{$labels.alt_edit_build}" title="{$labels.alt_edit_build}"
  					              src="{$tlImages.edit}"/>
  					     {/if}    
  					  </a>   
              <span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$build.id}</span>
  				</td>
  				<td>{if $gui->editorType == 'none'}{$build.notes|nl2br}{else}{$build.notes}{/if}</td>
  				<td>{if $build.release_date != ''}{localize_date d=$build.release_date}{/if}</td>

          {* Custom fields *}
          {if $gui->cfieldsColumns != null}
             {foreach item=cflbl from=$gui->cfieldsColumns}
               <td data-sort="{$build[$cflbl]['data-order']}">{$build[$cflbl]['value']|escape}</td>
             {/foreach}
          {/if}


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
      </tbody>
  	</table>
   </form> 
  {else}
  	<p>{$labels.no_builds}</p>
  {/if}
</div>


<div class="page-content">
  <form method="post" action="{$createAction}" id="create_build_bottom">
    <input type="submit" name="create_build_bottom" value="{$labels.btn_build_create}" />
  </form>
</div>

<p>{$labels.builds_description}</p>
</div>

</body>
</html>
