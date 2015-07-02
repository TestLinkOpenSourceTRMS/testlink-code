{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource reqMgrSystemView.tpl

@internal revisions
@since 1.9.6

*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var='labels'
          s='th_reqmgrsystem,th_reqmgrsystem_type,th_delete,th_description,
             btn_create,alt_delete,th_reqmgrsystem_env,check_reqmgrsystem_connection,
             reqmgrsystem_check_ok,reqmgrsystem_check_ko'}

{lang_get s='warning_delete' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/reqmgrsystems/reqMgrSystemEdit.php?doAction=doDelete&id=';
</script>
 
</head>
<body {$body_onload}>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<div class="workBack">
  {include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}
  {if $gui->items != ''}
  <table class="simple_tableruler sortable">
    <tr>
      <th width="30%">{$tlImages.sort_hint}{$labels.th_reqmgrsystem}</th>
      <th>{$tlImages.sort_hint}{$labels.th_reqmgrsystem_type}</th>
      <th>{$labels.th_reqmgrsystem_env}</th>
      {if $gui->canManage != ""}
        <th style="min-width:70px">{$tlImages.sort_hint}{$labels.th_delete}</th>
      {/if}
    </tr>

    {foreach key=item_id item=item_def from=$gui->items}
    <tr>
      <td>
        {if $gui->canManage != ""}
          <a href="lib/reqmgrsystems/reqMgrSystemView.php?id={$item_def.id}">
            <img src="{$tlImages.wrench}" title="{$labels.check_reqmgrsystem_connection}">
          </a>
          {if $item_def.connection_status == "ok"}
            <img src="{$tlImages.check_ok}" title="{$labels.reqmgrsystem_check_ok}">
          {elseif $item_def.connection_status == "ko"}
            <img src="{$tlImages.check_ko}" title="{$labels.reqmgrsystem_check_ko}">
          {else}
            &nbsp;
          {/if}
        {/if}

        {if $gui->canManage != ""}
          <a href="lib/reqmgrsystems/reqMgrSystemEdit.php?doAction=edit&amp;id={$item_def.id}">
        {/if}
        {$item_def.name|escape}
        {if $gui->canManage != ""}
          </a>
        {/if}

      </td>
      <td>{$item_def.type_descr|escape}</td>
      <td class="clickable_icon">{$item_def.env_check_msg|escape}</td>

        <td class="clickable_icon">
        {if $gui->canManage != ""  && $item_def.link_count == 0}
            <img style="border:none;cursor: pointer;"
                 alt="{$labels.alt_delete}" title="{$labels.alt_delete}"   
                 src="{$tlImages.delete}"           
                onclick="delete_confirmation({$item_def.id},
                      '{$item_def.name|escape:'javascript'|escape}',
                      '{$del_msgbox_title}','{$warning_msg}');" />
        {/if}
        </td>
    </tr>
    {/foreach}
  </table>
  {/if}
  
  <div class="groupBtn">  
      <form name="item_view" id="item_view" method="post" action="lib/reqmgrsystems/reqMgrSystemEdit.php"> 
        <input type="hidden" name="doAction" value="" />
  
    {if $gui->canManage != ""}
        <input type="submit" id="create" name="create" value="{$labels.btn_create}" 
                 onclick="doAction.value='create'"/>
    {/if}
      </form>
  </div>
</div>

</body>
</html>