{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: reqCreateTestCases.tpl,v 1.10 2009/01/13 19:34:01 schlundus Exp $

   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

   rev: 
*}
{assign var="req_module" value='lib/requirements/'}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='select_at_least_one_req' var="check_msg"}

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


{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">
 	{$gui->main_descr|escape}   
	{include file="inc_help.tpl" helptopic="hlp_requirementsCoverage"}
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
     

     <table class="simple">
    	 <tr>
    		{if $gui->grants->req_mgmt == "yes"}
    		<th style="width: 15px;">
    						    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" 
                         onclick='cs_all_checkbox_in_div("req_div","req_id_cbox","toggle_req");'
                         title="{lang_get s='check_uncheck_all_checkboxes'}" /></th>
        {/if}
    		
    		<th>{lang_get s="req_doc_id"}</th>
    		<th>{lang_get s="title"}</th>
    		<th>{lang_get s="scope"}</th>
    	 </tr>
    	{section name=row loop=$gui->all_reqs}
    	<tr>
    	  {* 20060110 - fm - managing checkboxes as array and added value *}
    		{if $gui->grants->req_mgmt == "yes"}
    		<td><input type="checkbox" id="req_id_cbox{$gui->all_reqs[row].id}"
    		           name="req_id_cbox[{$gui->all_reqs[row].id}]" 
    		                                           value="{$gui->all_reqs[row].id}"/></td>{/if}
    		<td><span class="bold">{$gui->all_reqs[row].req_doc_id|escape}</span></td>
    		<td><span class="bold">{$gui->all_reqs[row].title|escape}</a></span></td>
    		<td>{$gui->all_reqs[row].scope|strip_tags|strip|truncate:100}</td>
    	</tr>
    	{sectionelse}
    	<tr><td></td><td><span class="bold">{lang_get s='req_msg_norequirement'}</span></td></tr>
    	{/section}
     </table>
     </div>
     {* ------------------------------------------------------------------------------------------ *}
    
     {* ------------------------------------------------------------------------------------------ *}
     {if $gui->grants->req_mgmt == "yes"}
      <div class="groupBtn">
       <input type="submit" name="create_tc_from_req" value="{lang_get s='req_select_create_tc'}" 
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
