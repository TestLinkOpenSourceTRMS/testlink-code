{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource issueTrackerView.tpl

*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var='labels'
  s='th_issuetracker,th_issuetracker_type,th_delete,th_description,
     menu_assign_kw_to_tc,title_issuetracker_mgmt,
     btn_create,alt_delete,th_issuetracker_env,
     check_bts_connection,bts_check_ok,bts_check_ko'}

{lang_get s='warning_delete' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/issuetrackers/issueTrackerEdit.php?doAction=doDelete&id=';
</script> 

{if $gui->items != ''}
  {$ll = #pagination_length#}
  {include file="DataTables.inc.tpl" 
           DataTablesSelector="#item_view"
           DataTableslengthMenu=$ll}
{/if}

</head>
<body {$body_onload}>
{include file="aside.tpl"}  
<div id="main-content">

<h1 class="title big-font">{$labels.title_issuetracker_mgmt}</h1>

<div class="workBack">
  {include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}
  <table class="{#item_view_table#}" id="item_view">
    <thead class="{#item_view_thead#}">
      <tr>
        <th width="30%">{$labels.th_issuetracker}</th>
        <th>{$labels.th_issuetracker_type}</th>
        <th>{$labels.th_issuetracker_env}</th>
        {if $gui->canManage != ""}
          <th data-orderable="false" style="min-width:70px">{$labels.th_delete}</th>
        {/if}
      </tr>
    </thead>
  {if $gui->items != ''}
    {foreach key=item_id item=item_def from=$gui->items}
      <tr>
        <td>
          {if $gui->canManage != ""}
            <a href="lib/issuetrackers/issueTrackerView.php?id={$item_def.id}">
              <i class="fa fa-wrench" aria-hidden="true" title="{$labels.check_bts_connection}"></i>
            </a>
            {if $item_def.connection_status == "ok"}
              <i class="fa fa-heartbeat fa-lg" aria-hidden="true" title="{$labels.bts_check_ok}"></i>
            {elseif $item_def.connection_status == "ko"}
              <i class="fas fa-skull-crossbones fa-lg" title="{$labels.bts_check_ko}"></i>
            {else}
              &nbsp;
            {/if}
          {/if}

          {if $gui->canManage != ""}
            <a href="lib/issuetrackers/issueTrackerEdit.php?doAction=edit&amp;id={$item_def.id}">
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
              <i class="fas fa-minus-circle" title="{$labels.testproject_alt_delete}" 
                 onclick="delete_confirmation({$item_def.id},'{$item_def.name|escape:'javascript'|escape}',
                                                      '{$del_msgbox_title}','{$warning_msg}');"></i>
            {/if}
          </td>
        </td>  
      </tr>
    {/foreach}
  {/if}
  </table>
  
  <div class="groupBtn">  
      <form name="item_view" id="item_view" method="post" action="lib/issuetrackers/issueTrackerEdit.php"> 
        <input type="hidden" name="doAction" value="" />
  
    {if $gui->canManage != ""}
        <input class="btn btn-primary" type="submit" 
               id="create" name="create" 
               value="{$labels.btn_create}" 
               onclick="doAction.value='create'"/>
    {/if}
      </form>
  </div>
</div>
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>