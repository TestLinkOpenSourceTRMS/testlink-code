{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource: keywordsView.tpl
 smarty template - View all keywords 
*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var='labels'
          s='th_notes,th_keyword,th_delete,btn_import,btn_export,
             menu_assign_kw_to_tc,btn_create_keyword,
             menu_manage_keywords,alt_delete_keyword'}

{lang_get s='warning_delete_keyword' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action = fRoot+'lib/keywords/keywordsEdit.php?tproject_id={$gui->tproject_id}&doAction=do_delete&id=';
</script>
 
</head>
<body {$body_onload}>
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$labels.menu_manage_keywords}</h1>

<div class="workBack">
  {if $gui->keywords != ''}
  <table class="simple_tableruler sortable">
    <tr>
      <th width="30%">{$tlImages.sort_hint}{$labels.th_keyword}</th>
      <th>{$tlImages.sort_hint}{$labels.th_notes}</th>
      {if $gui->canManage != ""}
        <th style="min-width:70px">{$tlImages.sort_hint}{$labels.th_delete}</th>
      {/if}
    </tr>
    {section name=kwx loop=$gui->keywords}
    <tr>
      <td>
        {if $gui->canManage != ""}
          <a href="{$gui->editUrl}&doAction=edit&id={$gui->keywords[kwx]->dbID}">
        {/if}
        {$gui->keywords[kwx]->name|escape}
        {if $gui->canManage != ""}
          </a>
        {/if}
      </td>
      <td>{$gui->keywords[kwx]->notes|escape:htmlall|nl2br}</td>
      {if $gui->canManage != ""}
        <td class="clickable_icon">
            <img style="border:none;cursor: pointer;"
                alt="{$labels.alt_delete_keyword}" title="{$labels.alt_delete_keyword}"   
                src="{$tlImages.delete}"           
               onclick="delete_confirmation({$gui->keywords[kwx]->dbID},
                      '{$gui->keywords[kwx]->name|escape:'javascript'|escape}',
                      '{$del_msgbox_title}','{$warning_msg}');" />
        </td>
      {/if}
    </tr>
    {/section}
  </table>
  {/if}
  

  <div class="groupBtn">  
      <form name="keyword_view" id="keyword_view" method="post" action="lib/keywords/keywordsEdit.php"> 
        <input type="hidden" name="doAction" value="" />
        <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />

    {if $gui->canManage != ""}
        <input type="submit" id="create_keyword" name="create_keyword" 
                 value="{$labels.btn_create_keyword}" 
                 onclick="doAction.value='create'"/>
    {/if}
      {if $gui->keywords != ''}
        <input type="button" id="keyword_assign" name="keyword_assign" 
            value="{$labels.menu_assign_kw_to_tc}" 
              onclick="location.href=fRoot+'lib/general/frmWorkArea.php?feature=keywordsAssign';"/>
      {/if}    
    
    {if $gui->canManage != ""}
      <input type="button" name="do_import" value="{$labels.btn_import}" 
        onclick="location='{$basehref}/lib/keywords/keywordsImport.php?tproject_id={$gui->tproject_id}'" />
    {/if}
  
      {if $gui->keywords != ''}
      <input type="button" name="do_export" value="{$labels.btn_export}" 
        onclick="location='{$basehref}/lib/keywords/keywordsExport.php?doAction=export&tproject_id={$gui->tproject_id}'" />
      {/if}
      </form>
  </div>
</div>

</body>
</html>