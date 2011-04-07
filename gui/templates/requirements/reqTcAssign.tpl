{*
TestLink Open Source Project - http://testlink.sourceforge.net/
Id: reqAssign.tpl,v 1.6 2006/07/15 19:55:30 schlundus Exp $
Purpose: smarty template - assign REQ to one test case

20100403 - franciscom - SCOPE_SHORT_TRUNCATE
20080512 - franciscom - added new parameter to manage "close window" button display.
                        Is used when this feature is called on a new window, not from menu.
                        
20070617 - franciscom - manage checkboxes as arrays
                        added js logic to toggle/untoggle all

20070104 - franciscom -
1. added feedback message when there are not requirements
2. added control via javascrit on quantity of checked checkboxes

*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
          s="please_select_a_req,test_case,req_title_assign,btn_close,
             warning_req_tc_assignment_impossible,req_spec,warning,
             req_title_assigned,check_uncheck_all_checkboxes,
             req_msg_norequirement,btn_unassign,req_title_unassigned,
             check_uncheck_all_checkboxes,req_msg_norequirement,btn_assign"}
          
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
//BUGID 3943: Escape all messages (string)
	var please_select_a_req="{$labels.please_select_a_req|escape:'javascript'}";
	var alert_box_title = "{$labels.warning|escape:'javascript'}";
{literal}

function check_action_precondition(form_id,action)
{
	if(checkbox_count_checked(form_id) <= 0)
	{
		alert_message(alert_box_title,please_select_a_req);
		return false;
	}
	return true;
}
</script>
{/literal}
</head>

<body>

<h1 class="title">
	{$labels.test_case}{$smarty.const.TITLE_SEP}{$gui->tcTitle|escape}
	{include file="inc_help.tpl" helptopic="hlp_requirementsCoverage" show_help_icon=true}
</h1>

<div class="workBack">

{include file="inc_update.tpl" user_feedback=$gui->user_feedback}
{if $gui->arrReqSpec eq "" }
   {$labels.warning_req_tc_assignment_impossible}
{else}

  <h2>{$labels.req_title_assign}</h2>
  <form id="SRS_switch" name="SRS_switch" method="post">
    <p><span class="labelHolder">{$labels.req_spec}</span>
  	<select name="idSRS" onchange="form.submit()">
  		{html_options options=$gui->arrReqSpec selected=$gui->selectedReqSpec}
  	</select>
  </form>
</div>

<div class="workBack">
  <h2>{$labels.req_title_assigned}</h2>
  {if $gui->arrAssignedReq ne ""}
    <form id="reqList" method="post">
    <div id="div_assigned_req">
 	    {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="memory_assigned_req"
                            id="memory_assigned_req"  value="0" />

    <input type="hidden" name="idSRS" value="{$gui->selectedReqSpec}" />
    <table class="simple_tableruler">
    	<tr>
      		<th align="center"  style="width: 5px;background-color:#005498;">
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
      		             onclick='cs_all_checkbox_in_div("div_assigned_req","assigned_req","memory_assigned_req");'
      		             title="{$labels.check_uncheck_all_checkboxes}" />
      		</th>
    		<th>{lang_get s="req_doc_id"}</th>
    		<th>{lang_get s="req"}</th>
    		<th>{lang_get s="scope"}</th>
    	</tr>
    	{section name=row loop=$gui->arrAssignedReq}
    	<tr>
    		<td><input type="checkbox" id="assigned_req{$gui->arrAssignedReq[row].id}" value="{$gui->arrAssignedReq[row].id}"
    		                           name="req_id[{$gui->arrAssignedReq[row].id}]" /></td>
    		<td><span class="bold">{$gui->arrAssignedReq[row].req_doc_id|escape}</span></td>
    		<td><span class="bold"><a href="lib/requirements/reqView.php?requirement_id={$gui->arrAssignedReq[row].id}">
    			{$gui->arrAssignedReq[row].title|escape}</a></span></td>
    		<td>{$gui->arrAssignedReq[row].scope|strip_tags|strip|truncate:#SCOPE_SHORT_TRUNCATE#}</td>
    	</tr>
    	{sectionelse}
    	<tr><td></td><td><span class="bold">{$labels.req_msg_norequirement}</span></td></tr>
    	{/section}
    </table>
   	</div>

    {if $smarty.section.row.total > 0}
    	<div class="groupBtn">
    		<input type="submit" name="unassign" value="{$labels.btn_unassign}"
    		       onclick="return check_action_precondition('reqList','unassign');"/>
    	</div>
    {/if}
  </form>
  {/if}

  </div>


    {if $gui->arrUnassignedReq ne ""}
      <div class="workBack">
      <h2>{$labels.req_title_unassigned}</h2>
      <form id="reqList2" method="post">

       <div id="div_free_req">
 	     {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="memory_free_req"
                            id="memory_free_req"  value="0" />

      <input type="hidden" name="idSRS" value="{$gui->selectedReqSpec}" />
      <table class="simple_tableruler">
      	<tr>
      		<th align="center"  style="width: 5px;background-color:#005498;">
      		    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
      		             onclick='cs_all_checkbox_in_div("div_free_req",
      		                                             "free_req","memory_free_req");'
      		             title="{$labels.check_uncheck_all_checkboxes}" />
      		</th>
      		<th>{lang_get s="req_doc_id"}</th>
      		<th>{lang_get s="req"}</th>
      		<th>{lang_get s="scope"}</th>
      	</tr>
      	{section name=row2 loop=$gui->arrUnassignedReq}
      	<tr>
      		<td><input type="checkbox"
      		           id="free_req{$gui->arrUnassignedReq[row2].id}" value="{$gui->arrUnassignedReq[row2].id}"
      		           name="req_id[{$gui->arrUnassignedReq[row2].id}]" /></td>

      		<td><span class="bold">{$gui->arrUnassignedReq[row2].req_doc_id|escape}</span></td>
      		<td><span class="bold"><a href="lib/requirements/reqView.php?requirement_id={$gui->arrUnassignedReq[row2].id}">
      			{$gui->arrUnassignedReq[row2].title|escape}</a></span></td>
      		<td>{$gui->arrUnassignedReq[row2].scope|strip_tags|strip|truncate:#SCOPE_SHORT_TRUNCATE#}</td>
      	</tr>
      	{sectionelse}
      	<tr><td></td><td><span class="bold">{$labels.req_msg_norequirement66}</span></td></tr>
      	{/section}
      </table>
	  </div>
      <div class="groupBtn">
      	<input type="submit" name="assign" value="{$labels.btn_assign}"
     		       onclick="return check_action_precondition('reqList2','assign');"/>
      </div>
      </form>
      </div>
    {/if}
{/if}
{if $gui->showCloseButton}
	<form name="closeMe">
		<div class="groupBtn">
			<input type="button" value="{$labels.btn_close}" onclick="window.close()" />
		</div>
	</form>
{/if}
</body>
</html>
