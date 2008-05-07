{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: containerMoveTC.tpl,v 1.3 2008/05/07 21:01:22 schlundus Exp $
Purpose:
        Allow user to choose testcases inside the choosen testsuite,
        to copy or move.

rev :
     20080329 - contributed by Eugenia Drosdezki
                refactored by franciscom

*}
{lang_get var='labels'
          s='th_test_case,th_id,title_move_cp,title_move_cp_testcases,sorry_further,
             choose_target,copy_keywords,btn_move,btn_cp'}

{lang_get s='select_at_least_one_testcase' var="check_msg"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{lang_get s='warning'}";
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
		<div id="move_copy_checkboxes">
        <table class="simple">
          <tr>
          <th class="clickable_icon">&nbsp;</th>
          <th class="tcase_id_cell">{$labels.th_id}</th>
          <th>{$labels.th_test_case}</th>
          </tr>
        {foreach from=$testcases key=rowid item=tcinfo}
            <tr>
                <td>
                    <input type="checkbox" name="tcaseSet[]" value="{$tcinfo.TCID}" />
                </td>
                <td>
                    {$tcprefix|escape}{$tcinfo.TCEXTERNALID|escape}&nbsp;&nbsp;
                </td>
                <td>
                    {$tcinfo.TCNAME|escape}
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
</body>
</html>