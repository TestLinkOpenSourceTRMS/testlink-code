{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_test_spec.tpl,v 1.22.2.1 2010/11/18 15:17:45 mx-julian Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

Rev:
    20101024 - Julian - BUGID 3928 - Custom fields before steps not using complete width of table
    20100917 - amitkhullar - BUGID 3780
	20100802 - amitkhullar - Moved the Design time Custom Fields before Exec CF. 
    20100621 - eloff - BUGID 3241 - Implement vertical layout
    20100105 - franciscom - Test Case Steps
    20090901 - franciscom - preconditions + exec_cfg->steps_results_layout
    20090718 - franciscom - added design time custom field location management
    20090526 - franciscom - added testplan_design custom field management
*}	
    {assign var="tableColspan" value="4"}

    {assign var="getReqAction" value="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
	  {assign var="testcase_id" value=$args_tc_exec.testcase_id}
    {assign var="tcversion_id" value=$args_tc_exec.id}
    
    {if isset($args_req_details)}
	  <div class="exec_test_spec">
		  <table class="test_exec"  >
		  <tr>
		  	<th colspan="{$tableColspan}" class="title">{$args_labels.reqs}</th>
		  </tr>
		  	
		  {foreach from=$args_req_details key=id item=req_elem}
		  <tr>
		  	<td>
		  	<span class="bold">
		  	 {$tlCfg->gui_separator_open}{$req_elem.req_spec_title}{$tlCfg->gui_separator_close}&nbsp;
		  	 <a href="javascript:openLinkedReqWindow({$req_elem.id})"  
		  	    title="{$args_labels.click_to_open}">
	  	    {$req_elem.req_doc_id|escape}{$tlCfg->gui_title_separator_1}{$req_elem.title|escape}
	  	   </a>
	  	  </span>
	  	 </td>
		  </tr>
		  {/foreach}
		  </table>
  	  </div>
	    <br />
		 {/if}
     
    <div class="exec_test_spec">
		<table class="simple">
		<tr>
			<th colspan="{$tableColspan}" class="title">{$args_labels.test_exec_summary}</th>
		</tr>
		<tr>
			<td colspan="{$tableColspan}">{$args_tc_exec.summary}</td>
		</tr>

		<tr>
			<th colspan="{$tableColspan}" class="title">{$args_labels.preconditions}</th>
		</tr>
		<tr>
			<td colspan="{$tableColspan}">{$args_tc_exec.preconditions}</td>
		</tr>

		<tr>
      		<td colspan="{$tableColspan}">{$args_labels.execution_type}
			                {$smarty.const.TITLE_SEP}
			                {$args_execution_types[$args_tc_exec.execution_type]}</td>
		</tr>

		{* 20090718 - franciscom - CF location management*}
    {if $args_design_time_cf[$testcase_id].before_steps_results != ''}
		<tr>
			{* 20101024 - BUGID 3928 *}
        	<td colspan="{$tableColspan}"> {$args_design_time_cf[$testcase_id].before_steps_results}</td>
		</tr>
		{/if}

	{if $args_tc_exec.steps != ''}
		{include file="testcases/inc_steps.tpl"
		         layout=$args_cfg->exec_cfg->steps_results_layout
		         edit_enabled=false
		         steps=$args_tc_exec.steps}
	{/if}
  	<tr>
		  <td colspan="{$tableColspan}">
		  {* 20090718 - franciscom - CF location management*}
      {if $args_design_time_cf[$testcase_id].standard_location != ''}
					<div id="cfields_design_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
					style="background-color:#dddddd;">{$args_design_time_cf[$testcase_id].standard_location}
					</div>
		  {/if} 
			</td>
		</tr>
 
  	<tr>
    {* ------------------------------------------------------------------------------------- *}
    {if $args_enable_custom_field and $args_tc_exec.active == 1}
  	  {if isset($args_execution_time_cf[$testcase_id]) && $args_execution_time_cf[$testcase_id] != ''}
  	 		<tr>
  				<td colspan="{$tableColspan}">
  					<div id="cfields_exec_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
  						style="background-color:#dddddd;">{$args_execution_time_cf[$testcase_id]}
  					</div>
  				</td>
  			</tr>
  		{/if}
    {/if} {* if $args_enable_custom_field *}
    {* ------------------------------------------------------------------------------------- *}
    
    

		  <td colspan="{$tableColspan}">
      {if $args_testplan_design_time_cf[$testcase_id] != ''}
					<div id="cfields_testplan_design_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
					style="background-color:#dddddd;">{$args_testplan_design_time_cf[$testcase_id]}
					</div>
		  {/if} 
			</td>
		</tr>
 		
		<tr>
			<td colspan="{$tableColspan}">
			{if $args_tcAttachments[$testcase_id] neq null}
				{include file="inc_attachments.tpl" 
				         attach_tableName="nodes_hierarchy" 
				         attach_downloadOnly=true 
						     attach_attachmentInfos=$args_tcAttachments[$testcase_id] 
						     attach_tableClassName="bordered"
						     attach_tableStyles="background-color:#dddddd;width:100%"}
			{/if}
			</td>
		</tr>
		</table>
		</div>
