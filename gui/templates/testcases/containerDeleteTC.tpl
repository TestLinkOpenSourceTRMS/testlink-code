{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: containerDeleteTC.tpl,v 1.1 2010/09/09 16:42:29 franciscom Exp $

Purpose:

rev :
*}
{lang_get var='labels'
          s='th_test_case,th_id,title_move_cp,title_move_cp_testcases,sorry_further,
             check_uncheck_all_checkboxes,btn_delete,
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
<h1 class="title">{$gui->main_descr|escape}</h1>

{if $op_ok == false}
	{$gui->user_feedback}
{else}
	<form id="delete_testcases" name="delete_testcases" method="post"
	      action="lib/testcases/containerEdit.php?objectID={$objectID}">

    {if $gui->user_feedback != ''}
      <div class="user_feedback">{$gui->user_feedback}</div>
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