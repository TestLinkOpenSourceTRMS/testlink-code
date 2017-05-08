{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource projectView.tpl
Purpose: smarty template - display Test Project List

Development hint:
some variables smarty and javascript are created on the inc_*.tpl files.

@internal revisions
@since 1.9.15
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{$managerURL="lib/project/projectEdit.php"}
{$deleteAction="$managerURL?doAction=doDelete&tprojectID="}
{$editAction="$managerURL?doAction=edit&amp;tprojectID="}
{$createAction="$managerURL?doAction=create"}
{$searchAction="lib/project/projectView.php?doAction=search"}


{lang_get s='popup_product_delete' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{lang_get var="labels" 
          s='title_testproject_management,testproject_txt_empty_list,tcase_id_prefix,
          th_name,th_notes,testproject_alt_edit,testproject_alt_active,btn_search_filter,
          th_requirement_feature,testproject_alt_delete,btn_create,public,hint_like_search_on_name,
          testproject_alt_requirement_feature,th_active,th_delete,th_id,btn_reset_filter,
          th_issuetracker,th_reqmgrsystem_short,active_click_to_change,inactive_click_to_change,
          click_to_enable,click_to_disable'}


{include file="inc_head.tpl" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>

{if $tlCfg->gui->projectView->pagination->enabled}
  {$ll = $tlCfg->gui->projectView->pagination->length}
  {include file="DataTables.inc.tpl" DataTablesOID="item_view"
                                     DataTableslengthMenu=$ll}
{/if}

</head>

<body {$body_onload}>

<h1 class="title">{$gui->pageTitle}</h1>
<div class="workBack">

<div class="groupBtn">
  <form method="post" action="{$searchAction}" style="display:inline;">
    <input type="text" id="name" name="name" value="{$gui->name}"  
           size="{#TESTPROJECT_NAME_SIZE#}" maxlength="{#TESTPROJECT_NAME_MAXLEN#}"
           placeholder="{$labels.hint_like_search_on_name}" required/>
    <input type="submit" id="search" name="search" value="{$labels.btn_search_filter}" title="{$labels.hint_like_search_on_name}" />
  </form>
  <form method="post" action="{$searchAction}" style="display:inline;">
    <input type="submit" name="resetFilter" value="{$labels.btn_reset_filter}" />
  </form>
  &nbsp;&nbsp;&nbsp;
  {if $gui->canManage}
  <form method="post" action="{$createAction}" style="display:inline;">
    <input type="submit" id="create" name="create" value="{$labels.btn_create}" />
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
  <form method="post" id="testProjectView" name="testProjectView" action="{$managerURL}">
    <input type="hidden" name="doAction" id="doAction" value="">
    <input type="hidden" name="tprojectID" id="tprojectID" value="">

  <table id="item_view" class="simple_tableruler sortable">
    <tr>
      <th>{$tlImages.toggle_api_info}
      {$tlImages.sort_hint}{$labels.th_name}</th>
      <th class="{$noSortableColumnClass}">{$labels.th_notes}</th>
      <th>{$tlImages.sort_hint}{$labels.tcase_id_prefix}</th>
      <th>{$tlImages.sort_hint}{$labels.th_issuetracker}</th>
      <th class="{$noSortableColumnClass}">{$labels.th_requirement_feature}</th>
      <th class="icon_cell">{$labels.th_active}</th>
      <th class="icon_cell">{$labels.public}</th>
      {if $gui->canManage == "yes"}
      <th class="icon_cell">{$labels.th_delete}</th>
      {/if}
    </tr>
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
      <td width="10%">
        {$testproject.prefix|escape}
      </td>
      
      <td width="10%">
        {$testproject.itstatusImg} &nbsp; {$testproject.itname|escape} 
      </td>
      {*
      <td width="10%">
        {$testproject.rmsstatusImg} &nbsp; {$testproject.rmsname|escape} 
      </td>
      *}
      <td class="clickable_icon">
        {if $testproject.opt->requirementsEnabled}
            <input type="image" style="border:none" 
                   title="{$labels.click_to_disable}"  alt="{$labels.click_to_disable}" 
                   onClick = "doAction.value='disableRequirements';tprojectID.value={$testproject.id};"
                   src="{$tlImages.on}"/>
          {else}
            <input type="image" style="border:none" 
                   title="{$labels.click_to_enable}"  alt="{$labels.click_to_enable}" 
                   onClick = "doAction.value='enableRequirements';tprojectID.value={$testproject.id};"
                   src="{$tlImages.off}"/>
          {/if}
      </td>
      <td class="clickable_icon">
        {if $testproject.active}
            <input type="image" style="border:none" 
                   title="{$labels.active_click_to_change}"  alt="{$labels.active_click_to_change}" 
                   onClick = "doAction.value='setInactive';tprojectID.value={$testproject.id};"
                   src="{$tlImages.on}"/>
          {else}
            <input type="image" style="border:none" 
                   title="{$labels.inactive_click_to_change}"  alt="{$labels.inactive_click_to_change}" 
                   onClick = "doAction.value='setActive';tprojectID.value={$testproject.id};"
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

  </table>
  </form>
{/if}
</div>

</div>

{if $gui->doAction == "reloadAll"}
  <script type="text/javascript">
  top.location = top.location;
  </script>
{else}
  {if $gui->doAction == "reloadNavBar"}
  <script type="text/javascript">
  // remove query string to avoid reload of home page,
  // instead of reload only navbar
  var href_pieces=parent.titlebar.location.href.split('?');
  parent.titlebar.location=href_pieces[0];
  </script>
  {/if}
{/if}

</body>
</html>