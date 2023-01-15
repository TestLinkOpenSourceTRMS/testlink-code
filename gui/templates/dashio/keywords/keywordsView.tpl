{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource: keywordsView.tpl
 smarty template - View all keywords 
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Here head tag will be opened *}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
  {include file="inc_del_onclick.tpl"}

  {lang_get var='labels'
            s='th_notes,th_keyword,th_delete,btn_import,btn_export,
              menu_assign_kw_to_tc,btn_create_keyword,
              menu_manage_keywords,alt_delete_keyword,tcvqty_with_kw'}

  {lang_get s='warning_delete_keyword' var="warning_msg" }
  {lang_get s='delete' var="del_msgbox_title" }

  <script type="text/javascript">
  /* All this stuff is needed for logic contained in inc_del_onclick.tpl */
  var del_action = fRoot+'lib/keywords/keywordsEdit.php'+
                  '?tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}' + 
                  '&doAction=do_delete'+
                  '&openByOther={$gui->openByOther}&id=';
  </script>

  {if $gui->bodyOnLoad != ''}
    <script language="JavaScript">
    var {$gui->dialogName} = new std_dialog();
    </script>
  {/if}

  {include file="bootstrap.inc.tpl"} 

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
 
</head>

<body onLoad="{$gui->bodyOnLoad}" onUnload="{$gui->bodyOnUnload}" class="testlink">
{include file="aside.tpl"}
<div id="main-content">
<h1 class="{#TITLE_CLASS#}">{$labels.menu_manage_keywords}</h1>

<div class="workBack">
  {if $gui->keywords != ''}
  <table class="{#item_view_table#}" id="{$gridHTMLID}">
    <thead class="{#item_view_thead#}">
      <tr>
        <th {#SMART_SEARCH#} width="30%">{$labels.th_keyword}</th>
        <th {#SMART_SEARCH#}>{$labels.th_notes}</th>
        {if $gui->canManage != ""}
          <th {#NOT_SORTABLE#} class="icon_cell"></th>
        {/if}
      </tr>
    </thead>

    <tbody>
    {section name=kwx loop=$gui->keywords}
      {$kwID=$gui->keywords[kwx]->dbID}
    <tr>
      <td>
        {if $gui->canManage != ""}
          <a href="{$gui->editUrl}&tplan_id={$gui->tplan_id}&doAction=edit&id={$gui->keywords[kwx]->dbID}&openByOther={$gui->openByOther}">
        {/if}
        {$gui->keywords[kwx]->name|escape}

        {if $gui->canManage != ""}
          </a>
        {/if}
        <span title="{$labels.tcvqty_with_kw}">({$gui->kwOnTCV[$kwID]['tcv_qty']})</span>
      </td>
      <td>{$gui->keywords[kwx]->notes|escape:htmlall|nl2br}</td>
      {if $gui->canManage != ""}
        {$yesDel = 1}
        <td class="clickable_icon">

            {if $gui->kwExecStatus != '' && 
                isset($gui->kwExecStatus[$kwID]) &&
                $gui->kwExecStatus[$kwID]['exec_or_not'] == 'EXECUTED'}
                {$yesDel = 0}
            {/if}

            {if $gui->kwFreshStatus != '' && 
                isset($gui->kwFreshStatus[$kwID]) && 
                $gui->kwFreshStatus[$kwID]['fresh_or_frozen'] == 'FROZEN'}
                {$yesDel = 0}
            {/if}

            {if $yesDel == 1}
                <i class="fas fa-minus-circle" title="{$labels.alt_delete_keyword}" 
                   onclick="delete_confirmation({$gui->keywords[kwx]->dbID},
                                              '{$gui->keywords[kwx]->name|escape:'javascript'|escape}',
                                              '{$del_msgbox_title}','{$warning_msg}');" ></i>
            {/if}          
        </td>
      {/if}
    </tr>
    {/section}
   </tbody>
  </table>
  {/if}
  

  <div class="page-content">  
      <form name="keyword_view" id="keyword_view" method="post" action="lib/keywords/keywordsEdit.php"> 
        <input type="hidden" name="doAction" value="" />
        <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
        <input type="hidden" name="openByOther"
               value="{$gui->openByOther}" />

    {if $gui->canManage != ""}
        <input class="{#BUTTON_CLASS#}" type="submit" 
               id="create_keyword" name="create_keyword" 
               value="{$labels.btn_create_keyword}" 
               onclick="doAction.value='create'"/>
    {/if}
    {if $gui->keywords != '' && $gui->canAssign!=''}
        <input class="{#BUTTON_CLASS#}" type="button" 
               id="keyword_assign" name="keyword_assign" 
            value="{$labels.menu_assign_kw_to_tc}" 
              onclick="location.href=fRoot+'lib/general/frmWorkArea.php?feature=keywordsAssign&tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}';"/>
    {/if}    
    
    {if $gui->canManage != ""}
      <input class="{#BUTTON_CLASS#}" type="button" 
             name="do_import" id="do_import" 
             value="{$labels.btn_import}" 
             onclick="location='{$basehref}/lib/keywords/keywordsImport.php?tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}'" />
    {/if}
  
      {if $gui->keywords != ''}
      <input class="{#BUTTON_CLASS#}" type="button" 
        name="do_export" id="do_import" 
        value="{$labels.btn_export}" 
        onclick="location='{$basehref}/lib/keywords/keywordsExport.php?doAction=export&tproject_id={$gui->tproject_id}&tplan_id={$gui->tplan_id}'" />
      {/if}
      </form>
  </div>
</div>
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>