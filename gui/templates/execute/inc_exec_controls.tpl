{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	inc_exec_controls.tpl
Purpose: draw execution controls (input for notes and results)

@internal revisions
20101225 - franciscom - fixed event viewer warning due to typo error
BUGID 3479: Bulk Execution - Custom Fields Bulk Assignment
*}	
      {$resultsStatusCode=$tlCfg->results.status_code}
      {$radio_id_prefix="status"}
      {if $args_save_type == 'bulk'}
        {$radio_id_prefix="bulk_"|cat:$radio_id_prefix}
      {/if}
  		<table class="invisible">
  		<tr>
  			<td style="text-align: center;">
  				<div class="title">{$args_labels.test_exec_notes}</div>
          {$args_webeditor} 
  			</td>
  			<td valign="top" style="width: 30%;">			
    				{* status of test *}
      			<div class="title" style="text-align: center;">{$args_labels.test_exec_result}</div>
    				
    				<div class="resultBox">
                {foreach key=verbose_status item=locale_status from=$tlCfg->results.status_label_for_exec_ui}
    						      <input type="radio" {$args_input_enable_mgmt} name="{$radio_id_prefix}[{$args_tcversion_id}]" 
    						      id="{$radio_id_prefix}_{$args_tcversion_id}_{$resultsStatusCode.$verbose_status}" 
    							    value="{$resultsStatusCode.$verbose_status}"
    						      {if $args_save_type == 'bulk'}
            							onclick="javascript:set_combo_group('execSetResults','status_','{$resultsStatusCode.$verbose_status}');"
    						      {/if}
    							    {if $verbose_status eq $tlCfg->results.default_status}
    							        checked="checked" 
    							    {/if} /> &nbsp;{lang_get s=$locale_status}<br />
    					  {/foreach}		
    					  <br />		
    		 			
    		 			  {if $args_save_type == 'single'}
    		 			      <input type="submit" name="save_results[{$args_tcversion_id}]" 
    		 			            {$args_input_enable_mgmt}
                          onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};return checkSubmitForStatus('{$resultsStatusCode.not_run}')"
    		 			            value="{$args_labels.btn_save_tc_exec_results}" />
    		 			         
    		 			      <input type="submit" name="save_and_next[{$args_tcversion_id}]" 
    		 			            {$args_input_enable_mgmt}
                          onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};return checkSubmitForStatus('{$resultsStatusCode.not_run}')"
    		 			            value="{$args_labels.btn_save_exec_and_movetonext}" />

    		 			  {else}
     	    	        <input type="submit" id="do_bulk_save" name="do_bulk_save"
      	    	             value="{$args_labels.btn_save_tc_exec_results}"/>

    		 			  {/if}       
    				</div>
    			</td>
    		</tr>
        {if $args_save_type == 'bulk' && $args_execution_time_cfields != ''}
          <tr><td colspan="2">
  					<div id="cfields_exec_time_tcversionid_{$args_tcversion_id}" class="custom_field_container" 
  						style="background-color:#dddddd;">
            {$args_labels.testcase_customfields}
            {$args_execution_time_cfields.0} {* 0 => bulk *}
            </div> 
          </td></tr>
        {/if}
  		</table>
      <div class="messages" style="align:center;">
      {$labels.exec_not_run_result_note}
      </div>
