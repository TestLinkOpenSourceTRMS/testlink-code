{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: reqCreateTestCases.tpl,v 1.18.2.1 2010/11/12 07:45:43 mx-julian Exp $

   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

   rev:
   20101111 - Julian - BUGID 4003 - Minor Improvements to table layout
   20100403 - francisco - adding #SCOPE_TRUNCATE#
   20091209 - asimon - contrib for testcase creation, BUGID 2996
*}
{assign var="req_module" value='lib/requirements/'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='select_at_least_one_req' var="check_msg"}
{lang_get var='labels' 
          s="req_doc_id,title,scope,coverage_number,expected_coverage,needed,warning,
             current_coverage,coverage,req_msg_norequirement,req_select_create_tc,
             requirement,status,type"} 


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
     

     <table class="simple" style="width:99%">
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
    		<th>{$labels.coverage_number}</th>
    		{if $gui->req_cfg->expected_coverage_management}
  				<th>{$labels.needed}</th>
  			{/if}
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
    			<span onclick="javascript: openLinkedReqWindow({$gui->all_reqs[row].id});"
      			      style="cursor:  pointer;  color: #059;" >
					{$gui->all_reqs[row].req_doc_id|escape} : {$gui->all_reqs[row].title|escape}
				</span>
			</td>
    		{assign var="req_status" value=$gui->all_reqs[row].status }
    		<td style="padding:2px;">{$gui->reqStatusDomain.$req_status|escape}</td>
    		{assign var="req_type" value=$gui->all_reqs[row].type }
    		<td style="padding:2px;">{$gui->reqTypeDomain.$req_type|escape}</td>
    		<td style="padding:2px;"><input name="testcase_count[{$gui->all_reqs[row].id}]" type="text" size="3" maxlength="3" value="1"></td>
    		{if $gui->req_cfg->expected_coverage_management}
  				<td align="center" style="padding:2px;">{$gui->all_reqs[row].expected_coverage}</td>
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