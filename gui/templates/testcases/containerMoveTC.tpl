{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: containerMoveTC.tpl,v 1.8 2010/11/06 11:42:47 amkhullar Exp $
Purpose:
        Allow user to choose testcases inside the choosen testsuite,
        to copy or move.

@internal revisions
@since 1.9.10
*}
{lang_get var='labels'
          s='th_test_case,th_id,title_move_cp,title_move_cp_testcases,sorry_further,
             check_uncheck_all_checkboxes,warning,execution_history,design,copy_requirement_assignments,
             choose_target,copy_keywords,btn_move,btn_cp,summary,btn_copy_ghost_zone'}

{lang_get s='select_at_least_one_testcase' var="check_msg"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
jQuery( document ).ready(function() {
jQuery(".chosen-select").chosen({ width: "50%", search_contains: true });
});


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
{lang_get s=$level var=level_translated}
<h1 class="title">{$level_translated}{$smarty.const.TITLE_SEP}{$object_name|escape} </h1>

<div class="workBack">
{if !$testCasesTableView}    
  <h1 class="title">{$labels.title_move_cp_testcases}</h1>
{/if}

{if $op_ok == false}
	{$user_feedback}
{else}
	<form id="move_copy_testcases" name="move_copy_testcases" method="post"
	      action="lib/testcases/containerEdit.php?objectID={$objectID}">

    {if !$testCasesTableView}    
      {if $user_feedback != ''}
        <div class="user_feedback">{$user_feedback}</div>
        <br />
      {/if}
  		<p>{$labels.choose_target}:
  			<select name="containerID" id="containerID" class="chosen-select">
  				  {html_options options=$containers}
  			</select>
  		</p>
  		<p>
  			<input type="checkbox" name="copyKeywords" checked="checked" value="1" />
  			{$labels.copy_keywords}
  		</p>
      <p>
        <input type="checkbox" name="copyRequirementAssignments" id='copyRequirementAssignments' 
               checked="checked" value="1">
        {$labels.copy_requirement_assignments}
      </p>
    {/if}

		{* need to do JS checks*}
    {* used as memory for the check/uncheck all checkbox javascript logic *}
    <input type="hidden" name="add_value_memory"  id="add_value_memory"  value="0" />
		<div id="move_copy_checkboxes">
        <table class="simple">
          <tr>
          {if !$testCasesTableView}  
          <th class="clickable_icon">
			         <img src="{$tlImages.toggle_all}"
			              onclick='cs_all_checkbox_in_div("move_copy_checkboxes","tcaseSet_","add_value_memory");'
                    title="{$labels.check_uncheck_all_checkboxes}" />
			    </th>
          {/if}
          <th>{$labels.th_test_case}</th>
          <th>{$labels.summary}</th>
          </tr>
          
        {foreach from=$testcases key=rowid item=tcinfo}
            <tr>
              {if !$testCasesTableView}  
                <td>
                    <input type="checkbox" name="tcaseSet[]" id="tcaseSet_{$tcinfo.tcid}" value="{$tcinfo.tcid}" />
                </td>
              {/if}
                <td>
                    <img class="clickable" src="{$tlImages.history_small}"
                         onclick="javascript:openExecHistoryWindow({$tcinfo.tcid});"
                         title="{$labels.execution_history}" />
                    <img class="clickable" src="{$tlImages.edit_icon}"
                         onclick="javascript:openTCaseWindow({$tcinfo.tcid});"
                         title="{$labels.design}" />
                    {$tcprefix|escape}{$tcinfo.tcexternalid|escape}{$gsmarty_gui->title_separator_1}{$tcinfo.tcname|escape}
                </td>
                <td style="width:60%;">
                  {$tcinfo.summary}
                </td>
            </tr>
        {/foreach}
        </table>
        <br />
    </div>

    {if !$testCasesTableView}    
		<div>
			<input type="submit" name="do_move_tcase_set" value="{$labels.btn_move}"
             onclick="return check_action_precondition('move_copy_checkboxes','move','{$check_msg}');"  />

			<input type="submit" name="do_copy_tcase_set" value="{$labels.btn_cp}"
			       onclick="return check_action_precondition('move_copy_checkboxes','copy','{$check_msg}');"  />

      <input type="submit" name="do_copy_tcase_set_ghost" value="{$labels.btn_copy_ghost_zone}"
             onclick="return check_action_precondition('move_copy_checkboxes','copy','{$check_msg}');"  />

			<input type="hidden" name="old_containerID" value="{$old_containerID}" />
		</div>
    {/if}
	</form>
{/if}

</div>
{if $refreshTree}
 	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</body>
</html>