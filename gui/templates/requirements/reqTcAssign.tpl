{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource reqTCAssign.tpl
assign REQ to one test case
*}
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
          s="please_select_a_req,test_case,req_title_assign,btn_close,
             warning_req_tc_assignment_impossible,req_spec,warning,
             req_title_assigned,check_uncheck_all_checkboxes,version,
             version_short,
             req_msg_norequirement,btn_unassign,req_title_unassigned,
             check_uncheck_all_checkboxes,req_msg_norequirement,btn_assign,
             req_doc_id,req,scope,assigned_by,timestamp,requirement"}
          
{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var please_select_a_req="{$labels.please_select_a_req|escape:'javascript'}";
var alert_box_title = "{$labels.warning|escape:'javascript'}";
function check_action_precondition(form_id,action) {
	if(checkbox_count_checked(form_id) <= 0) {
		alert_message(alert_box_title,please_select_a_req);
		return false;
	}
	return true;
}

/**
 *
 */
function refreshAndClose(tcase_id,callback) {
  if(callback == 'a') {
    target = fRoot+'lib/testcases/archiveData.php?tcase_id=' + tcase_id + '&show_mode=';  
    window.opener.location.href = target;
  } else {
    window.opener.location.reload(true);
  }  
  window.close();
}
</script>
</head>

{$reqLinkingEnabled = 0} 
{if $gui->req_tcase_link_management}
  {$reqLinkingEnabled = 1}
{/if}    

{if $tlCfg->testcase_cfg->reqLinkingDisabledAfterExec == 1 &&
    $gui->tcaseHasBeenExecuted == 1}
  {$reqLinkingEnabled = 0}
{/if}    



<body>
{$sep = $smarty.const.TITLE_SEP}
{$tcIdentity = "{$gui->tcTitle|escape}  "}
{$tcIdentity = "$tcIdentity [{$labels.version_short}{$gui->tcVersion}]"}

<h1 class="title">
{$labels.test_case} {$sep} {$tcIdentity}
</h1>

<div class="workBack">
{include file="inc_update.tpl" user_feedback=$gui->user_feedback}
{if $gui->arrReqSpec eq "" }
   {$labels.warning_req_tc_assignment_impossible}
{else}
  <h2>{$labels.req_title_assign} {$sep} {$tcIdentity}</h2>
  <form id="SRS_switch" name="SRS_switch" method="post">
    <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />
    {if $gui->tcase_id != 0}
      <input type="hidden" name="tcase_id" id="tcase_id" value="{$gui->tcase_id}" />
      <input type="hidden" name="callback" id="callback" value="{$gui->callback}" />
    {/if}

    <p><span class="labelHolder">{$labels.req_spec}</span>   
  	<select name="idSRS" id="idSRS" onchange="form.submit()">
  		{html_options options=$gui->arrReqSpec selected=$gui->selectedReqSpec}
  	</select>
  </form>
{if $gui->showCloseButton}
  <form name="closeMeTop">
    <div class="groupBtn">
      <input type="button" value="{$labels.btn_close}" 
        onclick="refreshAndClose({$gui->tcase_id},'{$gui->callback}');" />
    </div>
  </form>
{/if}

</div>

<div class="workBack">
  <h2>{$labels.req_title_assigned}</h2>
  {if $gui->assignedReq ne ""}
    <form id="reqList" method="post">
    <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />
    <div id="div_assigned_req">
 	    {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="memory_assigned_req"
                            id="memory_assigned_req"  value="0" />

    <input type="hidden" name="idSRS" value="{$gui->selectedReqSpec}" />
    <table class="simple_tableruler">
    	<tr>
      		<th align="center"  style="width: 5px;background-color:#005498;">
      		    <img src="{$tlImages.toggle_all}"
      		             onclick='cs_all_checkbox_in_div("div_assigned_req","assigned_req","memory_assigned_req");'
      		             title="{$labels.check_uncheck_all_checkboxes}" />
      		</th>
    		<th>{$labels.req_doc_id}</th>
    		<th>{$labels.req}</th>
    		<th>{$labels.scope}</th>
        <th>{$labels.assigned_by}</th>
        <th>{$labels.timestamp}</th>
    	</tr>
    	{section name=row loop=$gui->assignedReq}

      {$cbDisabled = 0}
      {* Has become complex & weird!! *}
      {if $tlCfg->reqTCLink->freezeeLinkOnNewREQVersion != FALSE }
        {if $gui->assignedReq[row].reqver_is_open == 0 || 
            $gui->assignedReq[row].can_be_removed == 0 }
          {$cbDisabled = 1}
        {/if}
      {/if}      
    	<tr>
    		<td>
          {if $cbDisabled == 1}
            &nbsp;
          {else}
            <input type="checkbox"  
            id="assigned_req{$gui->assignedReq[row].link_id}" value="{$gui->assignedReq[row].link_id}"
                    name="link_id[{$gui->assignedReq[row].link_id}]" />
          {/if}
        </td>
    		
        <td><span class="bold">{$gui->assignedReq[row].req_doc_id|escape}</span></td>
    		<td><span class="bold"><a href="lib/requirements/reqView.php?requirement_id={$gui->assignedReq[row].id}">
    			{$gui->assignedReq[row].title|escape}</a></span></td>
			<td>{if $gui->reqEditorType == 'none'}{$gui->assignedReq[row].scope|nl2br}{else}{$gui->assignedReq[row].scope|strip_tags|strip|truncate:#SCOPE_SHORT_TRUNCATE#}{/if}</td>	
        <td>{$gui->assignedReq[row].coverage_author}</td>
        <td>{localize_timestamp ts=$gui->assignedReq[row].coverage_ts}</td>
    	</tr>
    	{sectionelse}
    	<tr><td></td><td><span class="bold">{$labels.req_msg_norequirement}</span></td></tr>
    	{/section}
    </table>
   	</div>

    {if $smarty.section.row.total > 0 && $reqLinkingEnabled}
    	<div class="groupBtn">
    		<input type="submit" name="unassign" value="{$labels.btn_unassign}"
    		       onclick="return check_action_precondition('reqList','unassign');"/>
    	</div>
    {/if}
  </form>
  {/if}

  </div>
    {if $gui->unassignedReq ne "" && $reqLinkingEnabled == 1}
      <div class="workBack">
      <h2>{$labels.req_title_unassigned}</h2>
      <form id="reqList2" method="post">
        <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}" />

       <div id="div_free_req">
 	     {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="memory_free_req"
                            id="memory_free_req"  value="0" />

      <input type="hidden" name="idSRS" value="{$gui->selectedReqSpec}" />
      <table class="simple_tableruler">
      	<tr>
      		<th align="center"  style="width: 5px;background-color:#005498;">
      		    <img src="{$tlImages.toggle_all}"
      		             onclick='cs_all_checkbox_in_div("div_free_req",
      		                                             "free_req","memory_free_req");'
      		             title="{$labels.check_uncheck_all_checkboxes}" />
      		</th>
      		<th>{lang_get s="req_doc_id"}</th>
      		<th>{lang_get s="req"}</th>
      		<th>{lang_get s="scope"}</th>
      	</tr>
      	{section name=row2 loop=$gui->unassignedReq}
          {$freeReq = $gui->unassignedReq[row2]}
      	<tr>
      		<td><input type="checkbox" {if $freeReq.reqver_is_open == 0} disabled="disabled" {/if}
      		           id="free_req{$freeReq.id}" value="{$freeReq.id}"
      		           name="req_id[{$freeReq.id}]" /></td>

      		<td><span class="bold">{$freeReq.req_doc_id|escape}</span></td>
      		<td><span class="bold"><a href="lib/requirements/reqView.php?requirement_id={$freeReq.id}&req_version_id={$freeReq.version_id}">
      			{$freeReq.title|escape} [{$labels.version_short}{$freeReq.version}] </a></span></td>
      		<td>{if $gui->reqEditorType == 'none'}{$freeReq.scope|nl2br}{else}{$freeReq.scope|strip_tags|strip|truncate:#SCOPE_SHORT_TRUNCATE#}{/if}</td>
      	</tr>
      	{sectionelse}
      	<tr><td></td><td><span class="bold">{$labels.req_msg_norequirement}</span></td></tr>
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
			<input type="button" value="{$labels.btn_close}" 
        onclick="refreshAndClose({$gui->tcase_id},'{$gui->callback}');" />
		</div>
	</form>
{/if}
</body>
</html>