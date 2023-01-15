{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource buildView.tpl

Purpose: smarty template - Show existing builds

*}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{$tproject_id=$gui->tproject_id}
{$tplan_id=$gui->tplan_id}

{$managerURL="lib/plan/buildEdit.php"}
{$editAction="$managerURL?do_action=edit&tproject_id=$tproject_id&tplan_id=$tplan_id&build_id="}
{$deleteAction="$managerURL?do_action=do_delete&tproject_id=$tproject_id&tplan_id=$tplan_id&build_id="}
{$createAction="$managerURL?do_action=create&tproject_id=$tproject_id&tplan_id=$tplan_id"}


{lang_get s='warning_delete_build' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{lang_get var="labels" 
          s='title_build_2,test_plan,th_title,th_description,th_active,
             th_open,th_delete,alt_edit_build,alt_active_build,
             alt_open_build,alt_delete_build,no_builds,btn_build_create,
             builds_description,sort_table_by_column,th_id,release_date,
             inactive_click_to_change,active_click_to_change,click_to_set_open,click_to_set_closed'}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>

{include file="bootstrap.inc.tpl"}
{if $gui->buildSet != ''}
  {$ll = $tlCfg->gui->{$cfg_section}->pagination->length}
  {* Do not initialize in DataTables -> DataTablesSelector="" *}
  {include file="DataTables.inc.tpl" DataTablesSelector="" DataTableslengthMenu=$ll}
  {include file="DataTablesColumnFiltering.inc.tpl" DataTablesSelector="#item_view" DataTablesLengthMenu=$ll}
{/if}

</head>

<body {$body_onload}>

{include file="aside.tpl"}  
<div id="main-content">

<h1 class="{#TITLE_CLASS#}">{$labels.title_build_2}{$smarty.const.TITLE_SEP_TYPE3}{$labels.test_plan}{$smarty.const.TITLE_SEP}{$gui->tplan_name|escape}</h1>

<div class="page-content">
{include file="inc_update.tpl" result=$sqlResult item="build" user_feedback=$gui->user_feedback}


{if null != $gui->buildSet && 
   (count($gui->buildSet) > $tlCfg->gui->buildView->itemQtyForTopButton)}
<div class="page-content">
  <form method="post" action="{$createAction}" id="create_build_top">
    <input class="{#BUTTON_CLASS#}" type="submit" 
           name="create_build_top" 
           id="create_build_top" 
           value="{$labels.btn_build_create}" />
  </form>
</div>
{/if}

<div id="existing_builds">
  {if $gui->buildSet ne ""}
  <form method="post" id="buildView" name="buildView" action="{$managerURL}">
    <input type="hidden" name="do_action" id="do_action" value="">
    <input type="hidden" name="build_id" id="build_id" value="">
    <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">
    <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">


    {* table id MUST BE item_view to use show/hide API info *}
    {* 
      IMPORTANT NOTICE: Because description is a text with formatting
                        filterings does not functio very well.
                        Then the choice is NO SMART SEARCH
    *}
    <table class="{#item_view_table#}" id="item_view">
      <thead class="{#item_view_thead#}">
    		<tr>
    			<th {#SMART_SEARCH#}>{$tlImages.toggle_api_info}{$labels.th_title}</th>
    			<th {#NOT_SORTABLE#}>{$labels.th_description}</th>
    			<th {#NOT_SORTABLE#} style="width:90px;">{$labels.release_date}</th>
    			<th class="icon_cell" {#NOT_SORTABLE#}>{$labels.th_active}</th>
    			<th class="icon_cell" {#NOT_SORTABLE#}>{$labels.th_open}</th>
    			<th class="icon_cell" {#NOT_SORTABLE#}></th>
    		</tr>
      </thead>
      <tbody>
  		{foreach item=build from=$gui->buildSet}
        	<tr>
  				<td><a href="{$editAction}{$build.id}" title="{$labels.alt_edit_build}">{$build.name|escape}
                 <span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$build.id}</span>
  					  </a>   
  				</td>
  				<td>{if $gui->editorType == 'none'}{$build.notes|nl2br}{else}{$build.notes}{/if}</td>
  				<td>{if $build.release_date != ''}{localize_date d=$build.release_date}{/if}</td>

          <td class="clickable_icon">
            {if $build.active==1} 
                <i class="fas fa-toggle-on" title="{$labels.active_click_to_change}"
                     onclick="do_action.value='setInactive';build_id.value={$build.id};;$('#buildView').submit();"></i>       
              {else}
                <i class="fas fa-toggle-off" title="{$labels.inactive_click_to_change}"   
                     onclick="do_action.value='setActive';build_id.value={$build.id};$('#buildView').submit();"></i>       
              {/if}
          </td>

          <td class="clickable_icon">
            {if $build.is_open==1} 
                <i class="fas fa-lock-open" title="{$labels.click_to_set_closed}"
                     onclick="do_action.value='close';build_id.value={$build.id};;$('#buildView').submit();"></i>       
            {else}
                <i class="fas fa-lock" title="{$labels.click_to_set_open}"
                     onclick="do_action.value='open';build_id.value={$build.id};;$('#buildView').submit();"></i>       
            {/if}
          </td>

  				<td class="clickable_icon">
                <i class="fas fa-minus-circle" title="{$labels.alt_delete_build}" 
                   onclick="delete_confirmation({$build.id},'{$build.name|escape:'javascript'|escape}',
                                                '{$del_msgbox_title}','{$warning_msg}');"></i>
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
    <input class="{#BUTTON_CLASS#}" type="submit"
           name="create_build_bottom" id="create_build_bottom"
           value="{$labels.btn_build_create}" />
  </form>
</div>

<p>{$labels.builds_description}</p>
</div>
</div>
{include file="supportJS.inc.tpl"}

</body>
</html>
