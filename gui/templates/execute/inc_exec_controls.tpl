{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_controls.tpl,v 1.9 2009/11/17 18:11:15 franciscom Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

Rev: 20080503 - franciscom - use of tlCfg 

*}	
      {assign var="ResultsStatusCode" value=$tlCfg->results.status_code}
      {if $args_save_type == 'bulk' }
        {assign var="radio_id_prefix" value="bulk_status"}
      {else}
        {assign var="radio_id_prefix" value="status"}
      {/if}

  		<table class="invisible">
  		<tr>
  			<th rowspan="2" style="text-align: center;">
  				<div class="title">{$args_labels.test_exec_notes}</div>
          {$args_webeditor} 
  			</th>
  			<td valign="top" style="width: 30%;">			
    				{* status of test *}
      			<div class="title" style="text-align: center;">{$args_labels.test_exec_result}</div>
    				
    				<div class="resultBox">
                {foreach key=verbose_status item=locale_status from=$tlCfg->results.status_label_for_exec_ui}
    						      <input type="radio" {$args_input_enable_mgmt} name="{$radio_id_prefix}[{$args_tcversion_id}]" 
    						      id="{$radio_id_prefix}_{$args_tcversion_id}_{$ResultsStatusCode.$verbose_status}" 
    							    value="{$ResultsStatusCode.$verbose_status}"
    						      {if $args_save_type == 'bulk'}
            							onclick="javascript:set_combo_group('execSetResults','status_','{$ResultsStatusCode.$verbose_status}');"
    						      {/if}
    							    {if $verbose_status eq $tlCfg->results.default_status}
    							        checked="checked" 
    							    {/if} /> &nbsp;{lang_get s=$locale_status}<br />
    					  {/foreach}		
    					  <br />		
    		 			
    		 			  {if $args_save_type == 'single'}
    		 			      <input type="submit" name="save_results[{$args_tcversion_id}]" 
    		 			            {$args_input_enable_mgmt}
                          onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};return checkSubmitForStatus('{$ResultsStatusCode.not_run}')"
    		 			            value="{$args_labels.btn_save_tc_exec_results}" />
    		 			         
    		 			      <input type="submit" name="save_and_next[{$args_tcversion_id}]" 
    		 			            {$args_input_enable_mgmt}
                          onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};return checkSubmitForStatus('{$ResultsStatusCode.not_run}')"
    		 			            value="{$args_labels.btn_save_exec_and_movetonext}Save and Go to next" />

    		 			  {else}
     	    	        <input type="submit" id="do_bulk_save" name="do_bulk_save"
      	    	             value="{$args_labels.btn_save_tc_exec_results}"/>
    		 			  {/if}       
    				</div>
    			</td>
    		</tr>
  		</table>
      <div class="messages" style="align:center;">
      {$labels.exec_not_run_result_note}
      </div>
