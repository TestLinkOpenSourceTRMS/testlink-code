{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 

@filesource planView.tpl

@internal development hint:
some smarty and javascript variables are created on the inc_*.tpl files.
*}
     
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" 
          s='testplan_title_tp_management,testplan_txt_empty_list,sort_table_by_column,
          testplan_th_name,testplan_th_notes,testplan_th_active,testplan_th_delete,
          testplan_alt_edit_tp,alt_active_testplan,testplan_alt_delete_tp,public,
          btn_testplan_create,th_id,error_no_testprojects_present,btn_export_import,
          export_import,export,import,export_testplan_links,import_testplan_links,build_qty,
          testcase_qty,platform_qty,active_click_to_change,inactive_click_to_change,
          testcase_number_help,platform_number_help,build_number_help,assign_roles,execution'}


{lang_get s='warning_delete_testplan' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{include file="inc_head.tpl" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$gui->actions->deleteAction}';
</script>

{include file="bootstrap.inc.tpl"}

{$ll = #pagination_length#}
{if $tlCfg->gui->planView->pagination->enabled}
  {$ll = $tlCfg->gui->planView->pagination->length}
{/if}
{include file="DataTables.inc.tpl" DataTablesSelector="#item_view"
                                   DataTableslengthMenu=$ll}

</head>

<body {$body_onload}>
{include file="aside.tpl"}  
<div id="main-content">

<h1 class="{#TITLE_CLASS#}">{$gui->main_descr|escape}</h1>
{if $gui->user_feedback ne ""}
  <div>
    <p class="info">{$gui->user_feedback}</p>
  </div>
{/if}

<div class="workBack">
  {if $gui->createEnabled && !is_null($gui->tplans) && 
      count($gui->tplans) > $tlCfg->gui->planView->itemQtyForTopButton}
     <div class="groupBtn">
       <form method="post" action="{$gui->actions->createAction}"
             name="topCreateForm">
         <input class="{#BUTTON_CLASS#}" type="submit" 
                name="create_testplan_top" id="create_testplan_top" 
                value="{$labels.btn_testplan_create}" />
       </form>
     </div>
  {/if}

<div id="testplan_management_list">
{if $gui->tproject_id <= 0}
  {$labels.error_no_testprojects_present}
{else}
  <form method="post" id="testPlanView" 
        name="testPlanView" action="{$gui->actions->managerURL}">
    <input type="hidden" name="itemID" id="itemID" value="">
    <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
    <input type="hidden" name="do_action" id="do_action" value="NONE">
  
    {* table id MUST BE item_view to use show/hide API info *}
    <table class="{#item_view_table#}" id="item_view">
      <thead class="{#item_view_thead#}">
    <tr>
      <th>{$tlImages.toggle_api_info}{$labels.testplan_th_name}</th>       
      <th {#NOT_SORTABLE#}>{$labels.testplan_th_notes}</th>
      <th title="{$labels.testcase_number_help}">{$labels.testcase_qty}</th>
      <th title="{$labels.build_number_help}">{$labels.build_qty}</th>
      {if $gui->drawPlatformQtyColumn}
        <th title="{$labels.platform_number_help}">{$labels.platform_qty}</th>
      {/if} 
      <th {#NOT_SORTABLE#} class="icon_cell">{$labels.testplan_th_active}</th>
      <th {#NOT_SORTABLE#} class="icon_cell">{$labels.public}</th>
      <th {#NOT_SORTABLE#}>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=testplan from=$gui->tplans}
    <tr>
      <td><a href="{$gui->actions->editAction}{$testplan.id}"> 
             {$testplan.name|escape}
             <span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$testplan.id}</span>
           
             {if $gsmarty_gui->show_icon_edit}
                 <img title="{$labels.testplan_alt_edit_tp}"  alt="{$labels.testplan_alt_edit_tp}" 
                      src="{$tlImages.edit}"/>
             {/if}  
          </a>
      </td>
    <td>{if $gui->editorType == 'none'}{$testplan.notes|nl2br}{else}{$testplan.notes}{/if}</td>
      <td align="right" style="width:8%;">
        {$testplan.tcase_qty}
      </td>
      <td align="right" style="width:6%;">
        {$testplan.build_qty}
      </td>
      {if $gui->drawPlatformQtyColumn}
        <td align="right" style="width:10%;">
          {$testplan.platform_qty}
        </td>
      {/if} 
      <td class="clickable_icon">
        {if $testplan.active}
          <i class="fas fa-toggle-on" title="{$labels.active_click_to_change}"
             onclick="itemID.value={$testplan.id};do_action.value='setInactive';$('#testPlanView').submit();"></i>       
        {else}
          <i class="fas fa-toggle-off" title="{$labels.inactive_click_to_change}"   
             onclick="do_action.value='setActive';itemID.value={$testplan.id};$('#testPlanView').submit();"></i>       
        {/if}
      </td>
      <td class="clickable_icon">
        {if $testplan.is_public}
          <i class="fas fa-check-circle" title="{$labels.public}"></i>
        {else}
          &nbsp;
        {/if}
      </td>
      <td style="width:8%;">
          <img style="border:none;cursor: pointer;" 
               alt="{$labels.testplan_alt_delete_tp}"
             title="{$labels.testplan_alt_delete_tp}" 
             onclick="delete_confirmation({$testplan.id},'{$testplan.name|escape:'javascript'|escape}',
                                          '{$del_msgbox_title}','{$warning_msg}');"
             src="{$tlImages.delete}"/>
          <a href="{$gui->actions->exportAction}{$testplan.id}"> 
          <img style="border:none;cursor: pointer;" alt="{$labels.export_testplan_links}" 
               title="{$labels.export_testplan_links}" src="{$tlImages.export}"/>
          </a>     
          <a href="{$gui->actions->importAction}{$testplan.id}"> 
          <img style="border:none;cursor: pointer;" alt="{$labels.import_testplan_links}" 
               title="{$labels.import_testplan_links}"  src="{$tlImages.import}"/>
          </a>     

          {if $testplan.rights.testplan_user_role_assignment}
            <a href="{$gui->actions->assignRolesAction}{$testplan.id}"> 
            <img style="border:none;cursor: pointer;" alt="{$labels.assign_roles}" 
                 title="{$labels.assign_roles}"  src="{$tlImages.user}"/>
            </a>     
          {/if}
          <a href="{$gui->actions->gotoExecuteAction}{$testplan.id}"> 
          <img style="border:none;cursor: pointer;" alt="{$labels.execution}" 
               title="{$labels.execution}"  src="{$tlImages.execution}"/>
          </a>     
      </td>
    </tr>
    {/foreach}
    </tbody>
  </table>
</form>

{/if}
</div>

 {if $gui->grants->testplan_create && $gui->tproject_id > 0}
 <div class="groupBtn">
    <form method="post" 
          action="{$gui->actions->createAction}" 
          name="bottomCreateForm">
      <input class="{#BUTTON_CLASS#}" type="submit" 
             name="create_testplan" id="create_testplan_bottom"
             value="{$labels.btn_testplan_create}" />
    </form>
  </div>
 {/if}
</div>
</div>

{if $gui->doViewReload == true}
  <script type="text/javascript">
  // remove query string to avoid reload of home page,
  // instead of reload only navbar
  //DEBUG -alert(parent.titlebar.location.href);

  var href_pieces = parent.titlebar.location.href.split('?');
  var hn = href_pieces[0] + '?tproject_id={$gui->tproject_id}';
  //DEBUG alert('planView.tpl -> ' + hn);
  parent.titlebar.location = hn;
  </script>
{/if}

{include file="supportJS.inc.tpl"}
</body>
</html>