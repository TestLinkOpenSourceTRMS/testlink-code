{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource projectView.tpl
Purpose: smarty template - display Test Project List

Development hint:
some variables smarty and javascript are created on the inc_*.tpl files.
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

{if $tlCfg->gui->projectView->pagination->enabled}
  {$ll = $tlCfg->gui->projectView->pagination->length}
  {include file="DataTables.inc.tpl" DataTablesSelector="#item_view"
                                     DataTableslengthMenu=$ll}
{/if}

{include file="bootstrap.inc.tpl"}
</head>

<body {$body_onload} style="background-color: #eaeaea">
{include file="aside.tpl"}

<div id="main-content">
  <h1 class="{#TITLE_CLASS#}">{$gui->pageTitle}</h1>
  <div class="workBack">
    <div class="groupBtn">
      {if $gui->canManage}
      <form method="post" action="{$createAction}" style="display:inline;">
        <input class="{#BUTTON_CLASS#}" type="submit" 
               id="create" name="create"
               value="{$labels.btn_create}" />
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
                <th>{$tlImages.toggle_api_info}{$labels.th_name}</th>
                <th data-orderable="false">{$labels.th_notes}</th>
                <th>{$labels.tcase_id_prefix}</th>
                <th>{$labels.th_issuetracker}</th>
                <th>{$labels.th_codetracker}</th>
                <th data-orderable="false">{$labels.th_requirement_feature}</th>
                <th class="icon_cell">{$labels.th_active}</th>
                <th class="icon_cell">{$labels.public}</th>
                {if $gui->canManage == "yes"}
                <th class="icon_cell" data-orderable="false"></th>
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
                    <input type="image" style="border:none" 
                           title="{$labels.click_to_disable}"  alt="{$labels.click_to_disable}" 
                           onClick = "doAction.value='disableRequirements';itemID.value={$testproject.id};"
                           src="{$tlImages.on}"/>
                  {else}
                    <input type="image" style="border:none" 
                           title="{$labels.click_to_enable}"  alt="{$labels.click_to_enable}" 
                           onClick = "doAction.value='enableRequirements';itemID.value={$testproject.id};"
                           src="{$tlImages.off}"/>
                  {/if}
              </td>
              <td class="clickable_icon">
                {if $testproject.active}
                    <input type="image" style="border:none" 
                           title="{$labels.active_click_to_change}"  alt="{$labels.active_click_to_change}" 
                           onClick = "doAction.value='setInactive';itemID.value={$testproject.id};"
                           src="{$tlImages.on}"/>
                  {else}
                    <input type="image" style="border:none" 
                           title="{$labels.inactive_click_to_change}"  alt="{$labels.inactive_click_to_change}" 
                           onClick = "doAction.value='setActive';itemID.value={$testproject.id};"
                           src="{$tlImages.off}"/>
                  {/if}
              </td>
              <td class="clickable_icon">
                {if $testproject.is_public}
                    <img style="border:none"  title="{$labels.public}" alt="{$labels.public}" src="{$tlImages.choiceOn}" />
                  {else}
                    &nbsp;
                  {/if}
              </td>
              {if $gui->canManage == "yes"}
              <td class="clickable_icon">
                  <img style="border:none;cursor: pointer;"  alt="{$labels.testproject_alt_delete}"
                       title="{$labels.testproject_alt_delete}"
                       onclick="delete_confirmation({$testproject.id},'{$testproject.name|escape:'javascript'|escape}',
                                                  '{$del_msgbox_title}','{$warning_msg}');"
                       src="{$tlImages.delete}"/>
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
