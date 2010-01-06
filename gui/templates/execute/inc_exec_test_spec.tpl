{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_test_spec.tpl,v 1.14 2010/01/06 18:34:10 franciscom Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

Rev:
    20100105 - franciscom - Test Case Steps
    20090901 - franciscom - preconditions + exec_cfg->steps_results_layout
    20090718 - franciscom - added design time custom field location management
    20090526 - franciscom - added testplan_design custom field management
*}	
    {assign var="tableColspan" value="4"}

    {assign var="getReqAction" value="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
	  {assign var="testcase_id" value=$args_tc_exec.testcase_id}
    {assign var="tcversion_id" value=$args_tc_exec.id}
    
    {if isset($args_req_details) }
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
		  	 <a href="javascript: void(0)"  title="{$args_labels.click_to_open}"
		  	       onclick="window.open(fRoot+'{$getReqAction}{$req_elem.id}','{$args_labels.requirement}', 
		  	                            'width=700, height=500'); return false;">
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
		{* <table class="simple"> *}
		{* <table class="mainTable-x" width="100%"> *} 
		<table class="simple" width="100%">
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
      <td> {$args_design_time_cf[$testcase_id].before_steps_results}</td>
		</tr>
		{/if}

   	<tr>
		<th width="5">{$args_labels.step_number}</th>
		<th>{$args_labels.step_actions}</th>
		<th>{$args_labels.expected_results}</th>
		<th width="25">{$args_labels.execution_type_short_descr}</th>
  	</tr>
  	{if $args_tc_exec.steps != ''}
 	    {foreach from=$args_tc_exec.steps item=step_info }
    	<tr>
		  <td style="text-align:righ;">{$step_info.step_number}</td>
		  <td>{$step_info.actions}</td>
		  <td>{$step_info.expected_results}</td>
		  <td>{$execution_types[$step_info.execution_type]}</td>
	    </tr>
 	    {/foreach}
    {/if}
  	

    {* ------------------------------------------------------------------------------- 
      {if $args_cfg->exec_cfg->steps_results_layout == 'horizontal'}
		    <tr>
		    	<th width="50%">{$args_labels.test_exec_steps}</th>
		    	<th width="50%">{$args_labels.test_exec_expected_r}</th>
		    </tr>
		    <tr>
		    	<td style="vertical-align:top;">{$args_tc_exec.steps}</td>
		    	<td style="vertical-align:top;">{$args_tc_exec.expected_results}</td>
		    </tr>
		  {else}
		    <tr>
		    	<th width="100%">{$args_labels.test_exec_steps}</th>
		    </tr>
		    <tr>
		    	<td style="vertical-align:top;">{$args_tc_exec.steps}</td>
		    </tr>
		    <tr>
		    	<th width="50%">{$args_labels.test_exec_expected_r}</th>
		    </tr>
		    <tr>
		    	<td style="vertical-align:top;">{$args_tc_exec.expected_results}</td>
		    </tr>
		  {/if}
		 ------------------------------------------------------------------------------- 
    *}

		
    {* ------------------------------------------------------------------------------------- *}
    {if $args_enable_custom_field and $args_tc_exec.active eq 1}
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
    
    
  	<tr>
		  <td colspan="{$tableColspan}">
		  {* 20090718 - franciscom - CF location management*}
      {if $args_design_time_cf[$testcase_id].standard_location neq ''}
					<div id="cfields_design_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
					style="background-color:#dddddd;">{$args_design_time_cf[$testcase_id].standard_location}
					</div>
		  {/if} 
			</td>
		</tr>
 
    {* 20090526 - franciscom *}
  	<tr>
		  <td colspan="{$tableColspan}">
      {if $args_testplan_design_time_cf[$testcase_id] neq ''}
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
