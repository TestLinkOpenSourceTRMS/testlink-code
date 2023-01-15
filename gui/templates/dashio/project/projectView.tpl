{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource projectView.tpl
Purpose: smarty template - display Test Project List

Development hint:
- some variables smarty and javascript are created on the inc_*.tpl files.

CRITICAL
- $gui->doViewReload -> reload Logic!!

*}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{$managerURL=$gui->actions->managerURL}
{$deleteAction=$gui->actions->deleteAction}
{$editAction=$gui->actions->editAction}
{$createAction=$gui->actions->createAction}
{$searchAction=$gui->actions->searchAction}


{lang_get s='popup_product_delete' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{lang_get var="labels" 
          s='title_testproject_management,testproject_txt_empty_list,tcase_id_prefix,
          th_name,th_notes,testproject_alt_edit,testproject_alt_active,btn_search_filter,
          th_requirement_feature,testproject_alt_delete,btn_create,public,hint_like_search_on_name,
          testproject_alt_requirement_feature,th_active,th_delete,th_id,btn_reset_filter,
          th_issuetracker,th_codetracker,th_reqmgrsystem_short,active_click_to_change,inactive_click_to_change,
          click_to_enable,click_to_disable'}


{include file="inc_head.tpl" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>

{include file="bootstrap.inc.tpl"}

{if $gui->tprojects != ''}
  {$ll = $tlCfg->gui->{$cfg_section}->pagination->length}
  {* Do not initialize in DataTables -> DataTablesSelector="" *}
  {include file="DataTables.inc.tpl" DataTablesSelector="" DataTablesLengthMenu=$ll}
  {include file="DataTablesColumnFiltering.inc.tpl" DataTablesSelector="#item_view" DataTablesLengthMenu=$ll}
{/if}

</head>

<body {$body_onload} style="background-color: #eaeaea">
{include file="aside.tpl"}

<div id="main-content">
  <h1 class="{#TITLE_CLASS#}">{$gui->pageTitle}</h1>
  <div class="workBack">
    <div class="groupBtn">
      {if $gui->canManage}
      <form id="createItem" id="createItem" method="post" action="{$createAction}" style="display:inline;">
        <input class="{#BUTTON_CLASS#}" type="submit"  id="create" name="create" value="{$labels.btn_create}" />
      </form>
      {/if}
    </div>
    <p>
    <div id="testproject_management_list">
      {if $gui->tprojects == ''}
        {if $gui->feedback != ''}
          {$gui->feedback|escape}
        {else}
          {$labels.testproject_txt_empty_list}
        {/if}
      {else}
        <form method="post" id="testProjectView" name="testProjectView"
          action="{$managerURL}">
          <input type="hidden" name="doAction" id="doAction" value="">
          <input type="hidden" name="itemID" id="itemID" value="">
          <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
          <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">

          <table id="item_view" class="{#item_view_table#}">
            <thead class="{#item_view_thead#}">
              <tr>
                <th {#SMART_SEARCH#}>{$tlImages.toggle_api_info}{$labels.th_name}</th>
                <th {#NOT_SORTABLE#}>{$labels.th_notes}</th>
                <th {#SMART_SEARCH#}>{$labels.tcase_id_prefix}</th>
                <th >{$labels.th_issuetracker}</th>
                <th >{$labels.th_codetracker}</th>
                <th class="icon_cell" {#NOT_SORTABLE#}>{$labels.th_requirement_feature}</th>
                <th class="icon_cell" {#NOT_SORTABLE#}>{$labels.th_active}</th>
                <th class="icon_cell" {#NOT_SORTABLE#}>{$labels.public}</th>
                {if $gui->canManage == "yes"}
                  <th class="icon_cell" {#NOT_SORTABLE#}></th>
                {/if}
              </tr>
            </thead>
            <tbody>
            {foreach item=testproject from=$gui->tprojects}
            <tr>
              <td>    <a href="{$editAction}{$testproject.id}">
                     {$testproject.name|escape}
                     <span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$testproject.id}</span>
                     {if $gsmarty_gui->show_icon_edit}
                          <img title="{$labels.testproject_alt_edit}" alt="{$labels.testproject_alt_edit}"
                               src="{$tlImages.edit}"/>
                      {/if}
                   </a>
              </td>
              <td>
                {if $gui->editorType == 'none'}{$testproject.notes|nl2br}{else}{$testproject.notes}{/if}</td>
              </td>
              <td width="7%">
                {$testproject.prefix|escape}
              </td>
              
              <td width="7%">
                {$testproject.itstatusImg} &nbsp; {$testproject.itname|escape} 
              </td>
              <td width="7%">
                {$testproject.ctstatusImg} &nbsp; {$testproject.ctname|escape} 
              </td>
              <td class="clickable_icon">
                {if $testproject.opt->requirementsEnabled}
                  <i class="fas fa-toggle-on" title="{$labels.active_click_to_change}"
                     onclick = "doAction.value='disableRequirements';itemID.value={$testproject.id};$('#testProjectView').submit();"></i>       
                {else}
                  <i class="fas fa-toggle-off" title="{$labels.inactive_click_to_change}"   
                     onclick = "doAction.value='enableRequirements';itemID.value={$testproject.id};$('#testProjectView').submit();"></i>       
                {/if}
              </td>
              <td class="clickable_icon">
                {if $testproject.active}
                  <i class="fas fa-toggle-on" title="{$labels.active_click_to_change}"
                     onclick="doAction.value='setInactive';itemID.value={$testproject.id};$('#testProjectView').submit();"></i>       
                {else}
                  <i class="fas fa-toggle-off" title="{$labels.inactive_click_to_change}"   
                     onclick="doAction.value='setActive';itemID.value={$testproject.id};$('#testProjectView').submit();"></i>       
                {/if}
              </td>
              <td class="clickable_icon">
                {if $testproject.is_public}
                  <i class="fas fa-check-circle" title="{$labels.public}"></i>
                {else}
                  &nbsp;
                {/if}
              </td>
              {if $gui->canManage == "yes"}
              <td class="clickable_icon">
                <i class="fas fa-minus-circle" title="{$labels.testproject_alt_delete}" 
                   onclick="delete_confirmation({$testproject.id},'{$testproject.name|escape:'javascript'|escape}',
                                                '{$del_msgbox_title}','{$warning_msg}');"></i>

              </td>
              {/if}
            </tr>
            {/foreach}
           </tbody>
          </table>
        </form>
      {/if}
    </div>
  </div>
</div>

{if $gui->doViewReload == true}
  <script type="text/javascript">
  
  // According to amount of test projects the user has found while accessing
  // the project edit feature TO CREATE a test project
  // the type and target of refresh will change
  //
  // remove query string to avoid reload of home page,
  // instead of reload only navbar
  // DEBUG -
  //DEBUG console.log('parent.titlebar.location.href -> ' + parent.titlebar.location.href);
  var href_pieces = parent.titlebar.location.href.split('?');

  {if $gui->projectCount > 0}  
    // will refresh ONLY the NAVBAR
    // It seems that when operation is DELETE we will need to refresh also
    // the left side menu, but this has a minore annoyance
    // How to do this without exiting from the project view page??
    //
    var hn = href_pieces[0] + '?tproject_id={$gui->tproject_id}&updateMainPage=1';
    //DEBUG console.log('planView.tpl -> ' + hn);
    parent.titlebar.location = hn;
  {else}
    //DEBUG alert('8888');
    //DEBUG console.log('888888 p - planView.tpl ->>>>>> ');
    
    // we are creating the FIRST Test Project, we need to update also the left side menu
    // var hn = href_pieces[0] + '?tproject_id={$gui->tproject_id}&updateMainPage=1&activeMenu=projects';  
    var hn = href_pieces[0] + '?tproject_id={$gui->tproject_id}' 
                            + '&updateMainPage=1&activeMenu=projects&projectView=1';  
    hn = hn.replace('lib/general/navBar.php','index.php');
    //DEBUG console.log('0 p - planView.tpl -> ' + hn);
    //DEBUG alert('9999');
    //DEBUG alert('0 p - planView.tpl -> ' + hn);

    parent.location = hn;
  {/if}
  </script>
{/if}

{include file="supportJS.inc.tpl"}
</body>
</html>