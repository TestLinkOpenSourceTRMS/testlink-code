{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_test_spec.tpl,v 1.8 2008/12/31 15:06:00 franciscom Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

Rev:
*}	

	  {assign var="testcase_id" value=$args_tc_exec.testcase_id}
    {assign var="tcversion_id" value=$args_tc_exec.id}
    
    <div class="exec_test_spec">
		<table class="test_exec">
		<tr>
			<th colspan="2" class="title">{$args_labels.test_exec_summary}</th>
		</tr>
		<tr>
			<td colspan="2">{$args_tc_exec.summary}</td>
		</tr>
		<tr>
			<th width="50%">{$args_labels.test_exec_steps}</th>
			<th width="50%">{$args_labels.test_exec_expected_r}</th>
		</tr>
		<tr>
			<td style="vertical-align:top;">{$args_tc_exec.steps}</td>
			<td style="vertical-align:top;">{$args_tc_exec.expected_results}</td>
		</tr>
		<tr>
      		<td colspan="2">{$args_labels.execution_type}
			                {$smarty.const.TITLE_SEP}
			                {$args_execution_types[$args_tc_exec.execution_type]}</td>
		</tr>

		
    {* ------------------------------------------------------------------------------------- *}
    {if $args_enable_custom_field and $args_tc_exec.active eq 1}
  	  {if $args_execution_time_cf[$testcase_id]}
  	 		<tr>
  				<td colspan="2">
  					<div id="cfields_exec_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
  						style="background-color:#dddddd;">{$args_execution_time_cf[$testcase_id]}
  					</div>
  				</td>
  			</tr>
  		{/if}
    {/if} {* if $args_enable_custom_field *}
    {* ------------------------------------------------------------------------------------- *}
    
    
  	<tr>
		  <td colspan="2">
      {if $args_design_time_cf[$testcase_id] neq ''}
					<div id="cfields_design_time_tcversionid_{$tcversion_id}" class="custom_field_container" 
					style="background-color:#dddddd;">{$args_design_time_cf[$testcase_id]}
					</div>
		  {/if} 
			</td>
		</tr>
 		
		<tr>
			<td colspan="2">
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