{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource reqBulkMOn.tpl

Purpose:

*}
{lang_get var='labels'
          s='title_move_cp,title_move_cp_testcases,sorry_further,req_doc_id,
             check_uncheck_all_checkboxes,title,copy_testcase_assignments,
             choose_target,btn_toogle_mon,btn_start_mon,btn_stop_mon,warning,monitoring'}

{lang_get s='select_at_least_one_req' var="check_msg"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";

/*
  function: check_action_precondition

  args :

  returns:

*/
function check_action_precondition(container_id,action,msg)
{
	var containerSelect = document.getElementById('containerID');
	if(checkbox_count_checked(container_id) > 0 && containerSelect.value > 0)
	{
	  return true;
	}
	else
	{
	   alert_message(alert_box_title,msg);
	   return false;
	}
}
</script>
</head>

<body>
<h1 class="title">{$gui->main_descr}</h1>
<div class="workBack">
<h1 class="title">{$gui->action_descr}</h1>
{if $gui->array_of_msg != ''}
  <br />
  {include file="inc_msg_from_array.tpl" array_of_msg=$gui->array_of_msg arg_css_class="messages"}
  <br />
{/if}

	<form id="bulkMon" name="bulkMon" method="post" action="{$gui->page2call}">
    <input type="hidden" name="req_spec_id"  id="req_spec_id"  value="{$gui->req_spec_id}" />
	  
		<br />

		{* need to do JS checks*}
    {* used as memory for the check/uncheck all checkbox javascript logic *}
    <input type="hidden" name="add_value_memory"  id="add_value_memory"  value="0" />
		<div id="checkbox_region">
        <table class="simple_tableruler">
          <tr>
          <th class="clickable_icon">
			         <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			              onclick='cs_all_checkbox_in_div("checkbox_region","itemSet_","add_value_memory");'
                    title="{$labels.check_uncheck_all_checkboxes}" />
			    </th>
          <th style="width:15%">{$labels.req_doc_id}</th>
          <th>{$labels.title}</th>
          <th style="width:10%">{$labels.monitoring}</th>
          </tr>

        {foreach from=$gui->items key=rowid item=item_info}
            <tr>
                <td>
                    <input type="checkbox" name="itemSet[]" id="itemSet_{$item_info.id}" 
                           value="{$item_info.id}" {if count($gui->items) == 1} checked="checked" {/if}/>
                </td>
                <td>
                    {$item_info.req_doc_id|escape}&nbsp;&nbsp;
                </td>
                <td>
                    {$item_info.title|escape}
                </td>
                <td>
                    {$item_info.monitor}
                </td>
                
            </tr>
        {/foreach}
        </table>
        <br />
    </div>
		<div>
      <input type="hidden" name="doAction" id="doAction"  value="{$gui->doActionButton}" />
			<input type="submit" name="toogleMon" value="{$labels.btn_toogle_mon}"
			       onclick="return check_action_precondition('checkbox_region','copy','{$check_msg}');"  />

      {if $gui->enable_start_btn}       
      <input type="submit" name="startMon" value="{$labels.btn_start_mon}"
             onclick="return check_action_precondition('checkbox_region','copy','{$check_msg}');"  />
      {/if}

      {if $gui->enable_stop_btn}       
      <input type="submit" name="stopMon" value="{$labels.btn_stop_mon}"
             onclick="return check_action_precondition('checkbox_region','copy','{$check_msg}');"  />
      {/if}
             
		</div>

	</form>

	{if isset($gui->refreshTree) && $gui->refreshTree}
 	  {include file="inc_refreshTreeWithFilters.tpl"}
	{/if}

</div>
</body>
</html>
