{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: containerMoveTC.tpl,v 1.8 2010/11/06 11:42:47 amkhullar Exp $
Purpose:
        Allow user to choose testcases inside the choosen testsuite,
        to copy or move.

rev :
     20100314 - franciscom - added feedback when op ok (used when copy test cases)
                             and logic to refresh left side tree 
     20090425 - franciscom - BUGID 2422 - add checbox for bulk operation
     20080329 - contributed by Eugenia Drosdezki
                refactored by franciscom

*}
{lang_get var='labels'
          s='th_test_case,th_id,title_move_cp,title_move_cp_testcases,sorry_further,
             check_uncheck_all_checkboxes,warning,
             choose_target,copy_keywords,btn_move,btn_cp'}

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
<h1 class="title">{$level_translated}{$smarty.const.TITLE_SEP}{$object_name|escape} </h1>

<div class="workBack">
<h1 class="title">{$labels.title_move_cp_testcases}</h1>

{if $op_ok == false}
	{$user_feedback}
{else}
	<form id="move_copy_testcases" name="move_copy_testcases" method="post"
	      action="lib/testcases/containerEdit.php?objectID={$objectID}">

    {if $user_feedback != ''}
      <div class="user_feedback">{$user_feedback}</div>
      <br />
    {/if}
		<p>{$labels.choose_target}:
			<select name="containerID" id="containerID">
				  {html_options options=$containers}
			</select>
		</p>
		<p>
			<input type="checkbox" name="copyKeywords" checked="checked" value="1" />
			{$labels.copy_keywords}
		</p>

		{* need to do JS checks*}
    {* used as memory for the check/uncheck all checkbox javascript logic *}
    <input type="hidden" name="add_value_memory"  id="add_value_memory"  value="0" />
		<div id="move_copy_checkboxes">
        <table class="simple">
          <tr>
          <th class="clickable_icon">
			         <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			              onclick='cs_all_checkbox_in_div("move_copy_checkboxes","tcaseSet_","add_value_memory");'
                    title="{$labels.check_uncheck_all_checkboxes}" />
			    </th>
          <th class="tcase_id_cell">{$labels.th_id}</th>
          <th>{$labels.th_test_case}</th>
          </tr>
          
        {foreach from=$testcases key=rowid item=tcinfo}
            <tr>
                <td>
                    <input type="checkbox" name="tcaseSet[]" id="tcaseSet_{$tcinfo.tcid}" value="{$tcinfo.tcid}" />
                </td>
                <td>
                    {$tcprefix|escape}{$tcinfo.tcexternalid|escape}&nbsp;&nbsp;
                </td>
                <td>
                    {$tcinfo.tcname|escape}
                </td>
            </tr>
        {/foreach}
        </table>
        <br />
    </div>
		<div>
			<input type="submit" name="do_move_tcase_set" value="{$labels.btn_move}"
             onclick="return check_action_precondition('move_copy_checkboxes','move','{$check_msg}');"  />

			<input type="submit" name="do_copy_tcase_set" value="{$labels.btn_cp}"
			       onclick="return check_action_precondition('move_copy_checkboxes','copy','{$check_msg}');"  />

			<input type="hidden" name="old_containerID" value="{$old_containerID}" />
		</div>

	</form>
{/if}

</div>
{* 20100314 - franciscom *}
{if $refreshTree}
   	{include file="inc_refreshTreeWithFilters.tpl"}
	{*include file="inc_refreshTree.tpl"*}
{/if}
</body>
</html>