{*
TestLink Open Source Project - http://testlink.sourceforge.net/

Viewer for massive delete of test cases inside a test suite

@filesource containerDeleteTC.tpl
20110402 - franciscom - BUGID 4322: New Option to block delete of executed test cases
20100910 - franciscom - BUGID 3047: Deleting multiple TCs
*}
{lang_get var='labels'
          s='th_test_case,th_id,title_move_cp,title_move_cp_testcases,sorry_further,
             check_uncheck_all_checkboxes,btn_delete,th_linked_to_tplan,th_version,
             platform,th_executed,choose_target,copy_keywords,btn_move,warning,btn_cp,
             execution_history,design'}

{lang_get s='select_at_least_one_testcase' var="check_msg"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
//BUGID 3943: Escape all messages (string)
var alert_box_title = "{$labels.warning|escape:'javascript'}";
{literal}
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
{/literal}
</head>

<body>
{lang_get s=$level var=level_translated}
<h1 class="title">{$level_translated}{$smarty.const.TITLE_SEP}{$gui->object_name|escape} </h1>

<div class="workBack">
<h1 class="title">{$gui->main_descr|escape}</h1>

{if $gui->op_ok == false}
	{$gui->user_feedback}
{else}
	<form id="delete_testcases" name="delete_testcases" method="post"
	      action="{$basehref}lib/testcases/containerEdit.php?objectID={$gui->objectID}">
    <input type="hidden" name="do_delete_testcases"  id="do_delete_testcases"  value="1" />

    {if $gui->user_feedback != ''}
      <div class="user_feedback">{$gui->user_feedback}</div>
      <br />
    {/if}
    {if $gui->system_message != ''}
      <div class="user_feedback">{$gui->system_message}</div>
      <br />
    {/if}

		{* need to do JS checks*}
    {* used as memory for the check/uncheck all checkbox javascript logic *}
    <input type="hidden" name="add_value_memory"  id="add_value_memory"  value="0" />
		<div id="delete_checkboxes">
        <table class="simple">
          <tr>
          <th class="clickable_icon">
			         <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			              onclick='cs_all_checkbox_in_div("delete_checkboxes","tcaseSet_","add_value_memory");'
                    title="{$labels.check_uncheck_all_checkboxes}" />
			    </th>
          <th>{$labels.th_test_case}</th>
          </tr>
          
        {foreach from=$gui->testCaseSet key=rowid item=tcinfo}
            <tr>
                <td>
					{if $tcinfo.draw_check}
                    	<input type="checkbox" name="tcaseSet[]" id="tcaseSet_{$tcinfo.id}" value="{$tcinfo.id}" />
                    {/if}	
                </td>
                <td>
                    <img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/history_small.png"
                         onclick="javascript:openExecHistoryWindow({$tcinfo.id});"
                         title="{$labels.execution_history}" />
                    <img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/edit_icon.png"
                         onclick="javascript:openTCaseWindow({$tcinfo.id});"
                         title="{$labels.design}" />
                    {$tcinfo.external_id|escape}{$gsmarty_gui->title_separator_1}{$tcinfo.name|escape}
                </td>
            </tr>
            {if $gui->exec_status_quo[$rowid] != ''}
            <tr>
            <td>&nbsp;</td>
            <td>
	                      <table class="simple_tableruler">
	                  		<tr>
	                  			<th>{$labels.th_version}</th>
	                  			<th>{$labels.th_linked_to_tplan}</th>
	                  			{if $gui->display_platform[$rowid]}<th>{$labels.platform}</th> {/if}
	                  			<th>{$labels.th_executed}</th>
	                  			</tr>
	                  		{foreach from=$gui->exec_status_quo[$rowid] key=testcase_version_id item=on_tplan_status}
	                  			{foreach from=$on_tplan_status key=tplan_id item=status_on_platform}
	                  				{foreach from=$status_on_platform key=platform_id item=status}
	                  			    <tr>
	                  				    <td style="width:4%;text-align:right;">{$status.version}</td>
	                  				    <td align="left">{$status.tplan_name|escape}</td>
	                  			      {if $gui->display_platform[$rowid]}
	                  			        <td align="left">{$status.platform_name|escape}</td>
	                  			      {/if}
	                  				    <td style="width:4%;text-align:center;">{if $status.executed != ""}<img src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png" />{/if}</td>
	                  				  </tr>
	                  			  {/foreach}
	                  			{/foreach}
	                  		{/foreach}
	                      </table>
            </td>
            </tr>
            {/if}
        {/foreach}
        </table>
        <br />
    </div>




		<div>
			<input type="submit" name="do_delete_testcases" value="{$labels.btn_delete}">
		</div>

	</form>
{/if}

</div>
{if $gui->refreshTree}
   	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</body>
</html>