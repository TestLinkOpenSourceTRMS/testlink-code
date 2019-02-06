{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: reqCreateTestCases.tpl,v 1.18.2.1 2010/11/12 07:45:43 mx-julian Exp $

   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

   rev:
   20101111 - Julian - BUGID 4003 - Minor Improvements to table layout
   20100403 - francisco - adding #SCOPE_TRUNCATE#
   20091209 - asimon - contrib for testcase creation, BUGID 2996
   20110314 - Julian - BUGID 4317 - Added Contribution from user frl for an easy
                                    way to set amount of test cases to create to
                                    the number of test cases still required to
                                    fully cover the requirement(s)
*}
{assign var="req_module" value='lib/requirements/'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='select_at_least_one_req' var="check_msg"}
{lang_get var='labels' 
          s="req_doc_id,title,scope,coverage_number,expected_coverage,needed,warning,
             current_coverage,coverage,req_msg_norequirement,req_select_create_tc,
             requirement,status,type,toggle_create_testcase_amount,requirement"} 


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
function check_action_precondition(form_id,action,msg)
{
 if( checkbox_count_checked(form_id) > 0) 
 {
    switch(action)
    {
      case 'create':
      return true;
      break;
      
      default:
      return true;
      break
    
    }
 }
 else
 {
    alert_message(alert_box_title,msg);
    return false; 
 }  
}

/* BUGID 4317 - CONTRIB FRL:
  function:  cs_all_coverage_in_div (copied from cs_all_checkbox_in_div)
	Change values of all text inputs with a id prefix with values from another (hidden) text with matching id on a div.
	Note : IDs matching based equality of last part of inputs id (without prefixes)
  args :
	div_id: id of the div container of elements 
	input_id_prefix: input text id prefix (for inputs to be fill)
	default_id_prefix : input hidden id prefix (for inputs containing default values)
	memory_id: id of hidden input used to hold old check value.
   returns:  - 

*/
function cs_all_coverage_in_div(div_id, input_id_prefix, default_id_prefix, memory_id)
{
	var inputs = document.getElementById(div_id).getElementsByTagName('input');
	var memory = document.getElementById(memory_id);

	for(var idx = 0; idx < inputs.length; idx++)
	{
		// look for text input whose id starts with input_id_prefix
		if(inputs[idx].type == "text" && (inputs[idx].id.indexOf(input_id_prefix)==0) )
		{
		// set the value to 1 (if coverage ignored) or default value (retrieved from hidden input with matching id)
		inputs[idx].value = (memory.value == "1") ? "1" : document.getElementById(default_id_prefix+inputs[idx].id.substring(default_id_prefix.length)).value;
		}	
	} // for
	// switch coverage_count flag value 
	memory.value = (memory.value == "1") ? "0" : "1";
}

</script>
{/literal}
</head>

<body>


{assign var="cfg_section" value=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">
 	{$gui->main_descr|escape}   
	{include file="inc_help.tpl" helptopic="hlp_requirementsCoverage" show_help_icon=true}
</h1>



<div class="workBack">
  <h2>{$gui->action_descr}</h2>
  
  {if $gui->array_of_msg != ''}
    <br />
 	  {include file="inc_msg_from_array.tpl" array_of_msg=$gui->array_of_msg arg_css_class="messages"}
  {/if}
  
  <form id="frmReqList" enctype="multipart/form-data" method="post">
    <input type="hidden" name="doAction"  id="doAction"  value="doCreateTestCases" />
    <input type="hidden" name="req_spec_id"  id="req_spec_id"  value="{$gui->req_spec_id}" />
 
 
  {* ------------------------------------------------------------------------------------------ *}
  {if $gui->all_reqs ne ''}  

	 <div id="req_div"  style="margin:0px 0px 0px 0px;">
		{* used as memory for the check/uncheck all checkbox javascript logic *}
		<input type="hidden" name="toggle_req"  id="toggle_req"  value="0" />
		{* BUGID 4317 - CONTRIB FRL : add hidden field as memory to set/unset coverage_number with coverage values *}
		<input type="hidden" name="tc_cov_set"  id="tc_cov_set"  value="0" />
		{* BUGID 4317 - END CONTRIB FRL *}

	 <table class="simple_tableruler">
	<tr>
		{if $gui->grants->req_mgmt == "yes"}
			<th style="width: 15px;">
				<img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" 
					onclick='cs_all_checkbox_in_div("req_div","req_id_cbox","toggle_req");'
					title="{lang_get s='check_uncheck_all_checkboxes'}" class="clickable"/></th>
		{/if}
		<th>{$labels.requirement}</th>
		<th>{$labels.status}</th>
		<th>{$labels.type}</th>
			
		{* contribution for testcase creation, BUGID 2996 *}
		{* BUGID 4317 - CONTRIB FRL : add toogle to set/unset coverage_number with coverage values *}
		<th>{$labels.coverage_number}
			{if $gui->req_cfg->expected_coverage_management}
				<img src="{$smarty.const.TL_THEME_IMG_DIR}/insert_step.png" width="12" height="12"
					onclick='cs_all_coverage_in_div("req_div","testcase_count","coverage_count","tc_cov_set");'
					title="{lang_get s='toggle_create_testcase_amount'}" class="clickable"/></th>
			<th>{$labels.needed}
		{/if}</th>
		{* BUGID 4317 - END CONTRIB FRL : add toogle to set/unset coverage_number with coverage values *}
		<th>{$labels.current_coverage}</th>
		<th>{$labels.coverage}</th>
			
	</tr>
	{section name=row loop=$gui->all_reqs}
	<tr>
		{* 20060110 - fm - managing checkboxes as array and added value *}
			{if $gui->grants->req_mgmt == "yes"}
			<td style="padding:2px;"><input type="checkbox" id="req_id_cbox{$gui->all_reqs[row].id}"
					   name="req_id_cbox[{$gui->all_reqs[row].id}]" 
													   value="{$gui->all_reqs[row].id}"/></td>{/if}
			<td style="padding:2px;">
				<img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/edit_icon.png"
				     onclick="javascript:openLinkedReqWindow({$gui->all_reqs[row].id});"
				     title="{$labels.requirement}" />
				{$gui->all_reqs[row].req_doc_id|escape}{$gsmarty_gui->title_separator_1}{$gui->all_reqs[row].title|escape}
			</td>
			{assign var="req_status" value=$gui->all_reqs[row].status }
			<td style="padding:2px;">{$gui->reqStatusDomain.$req_status|escape}</td>
			{assign var="req_type" value=$gui->all_reqs[row].type }
			<td style="padding:2px;">{$gui->reqTypeDomain.$req_type|escape}</td>
			<td style="padding:2px;"><input name="testcase_count[{$gui->all_reqs[row].id}]" id="testcase_count{$gui->all_reqs[row].id}" type="text" size="3" maxlength="3" value="1"></td>
			{if $gui->req_cfg->expected_coverage_management}
				{* BUGID 4317 - CONTRIB FRL : add hidden field to store coverage_count with coverage values *}
				<td align="center" style="padding:2px;">{$gui->all_reqs[row].expected_coverage}
				<input name="coverage_count[{$gui->all_reqs[row].id}]" id="coverage_count{$gui->all_reqs[row].id}" type="hidden"
					value="{if $gui->all_reqs[row].expected_coverage >=  $gui->all_reqs[row].coverage}{$gui->all_reqs[row].expected_coverage-$gui->all_reqs[row].coverage}{else}0{/if}"></td>
				{* BUGID 4317 - END CONTRIB FRL *}
			{/if}  	
			<td align="center" style="padding:2px;">{$gui->all_reqs[row].coverage}</td>
			<td align="center" style="padding:2px;">{$gui->all_reqs[row].coverage_percent}%</td>
			
		</tr>
		{sectionelse}
		<tr><td></td><td><span class="bold">{$labels.req_msg_norequirement}</span></td></tr>
		{/section}
	 </table>
	 </div>
	 {* ------------------------------------------------------------------------------------------ *}
	
	 {* ------------------------------------------------------------------------------------------ *}
	 {if $gui->grants->req_mgmt == "yes"}
	  <div class="groupBtn">
	   <input type="submit" name="create_tc_from_req" value="{$labels.req_select_create_tc}" 
			  onclick="return check_action_precondition('frmReqList','create','{$check_msg}');"/>
	  </div>
	 {/if}
	 {* ------------------------------------------------------------------------------------------ *}
	
  {/if}  
  {* ------------------------------------------------------------------------------------------ *}
</form>
</div>
</body>
</html>