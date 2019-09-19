{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqCopy.tpl,v 1.10 2010/11/06 11:42:47 amkhullar Exp $
Purpose:
        Allow user to choose requirements inside a req spec to copy.
        Will be used also to implement copy from requirement view feature.

*}
{lang_get var='labels'
          s='title_move_cp,title_move_cp_testcases,sorry_further,req_doc_id,
             check_uncheck_all_checkboxes,title,copy_testcase_assignments,
             choose_target,btn_cp,warning'}

{lang_get s='select_at_least_one_req' var="check_msg"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
// BUGID 3943: Escape all messages (string)
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
<h1 class="title">{$gui->main_descr}</h1>
<div class="workBack">
<h1 class="title">{$gui->action_descr}</h1>
{if $gui->array_of_msg != ''}
  <br />
  {include file="inc_msg_from_array.tpl" array_of_msg=$gui->array_of_msg arg_css_class="messages"}
  <br />
{/if}

	<form id="copy_req" name="copy_req" method="post" action="{$gui->page2call}">
    <input type="hidden" name="req_spec_id"  id="req_spec_id"  value="{$gui->req_spec_id}" />
	  
		<p>{$labels.choose_target}:
			<select name="containerID" id="containerID">
				  {html_options options=$gui->containers}
			</select>
		</p>
		<br />
		<p>
    <input type="checkbox" name="copy_testcase_assignment" id='copy_testcase_assignment' checked="checked">
     {$labels.copy_testcase_assignments}
    </p>
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
            </tr>
        {/foreach}
        </table>
        <br />
    </div>
		<div>
      <input type="hidden" name="doAction" id="doAction"  value="{$gui->doActionButton}" />
			<input type="submit" name="copy" value="{$labels.btn_cp}"
			       onclick="return check_action_precondition('checkbox_region','copy','{$check_msg}');"  />
		</div>

	</form>

	{if isset($gui->refreshTree) && $gui->refreshTree}
 	  {include file="inc_refreshTreeWithFilters.tpl"}
	{/if}

</div>
</body>
</html>
