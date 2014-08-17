{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_controls.tpl,v 1.14.4.1 2010/12/25 11:57:47 franciscom Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

Rev: 
*}	
      {$ResultsStatusCode=$tlCfg->results.status_code}
      {if $args_save_type == 'bulk'}
        {$radio_id_prefix = "bulk_status"}
      {else}
        {$radio_id_prefix = "statusSingle"}
      {/if}

  		<table class="invisible">
  		<tr>
  			<td style="text-align: center;">
  				<div class="title">{$args_labels.test_exec_notes}</div>
          {$args_webeditor} 
  			</td>
  			<td valign="top" style="width: 30%;">			
    				{* status of test *}
            
      			<div class="title" style="text-align: center;">
            {if $args_save_type == 'bulk'} {$args_labels.test_exec_result} {else} &nbsp; {/if}
            </div>

    				<div class="resultBox">
              {if $args_save_type == 'bulk'}
                {foreach key=verbose_status item=locale_status from=$tlCfg->results.status_label_for_exec_ui}
    						      <input type="radio" {$args_input_enable_mgmt} name="{$radio_id_prefix}[{$args_tcversion_id}]" 
    						      id="{$radio_id_prefix}_{$args_tcversion_id}_{$ResultsStatusCode.$verbose_status}" 
    							    value="{$ResultsStatusCode.$verbose_status}"
    											onclick="javascript:set_combo_group('execSetResults','status_','{$ResultsStatusCode.$verbose_status}');"
    							    {if $verbose_status eq $tlCfg->results.default_status}
    							        checked="checked" 
    							    {/if} /> &nbsp;{lang_get s=$locale_status}<br />
    					  {/foreach}
              {else}
                {$args_labels.test_exec_result}&nbsp;
                <select name="statusSingle[{$tcversion_id}]" id="statusSingle_{$tcversion_id}">
                {html_options options=$gui->execStatusValues}
                </select>
              {/if}
                

    					  <br />		
                {$labels.execution_duration}&nbsp;
                <input type="text" name="execution_duration" id="execution_duration"
                       size="{#EXEC_DURATION_SIZE#}" maxlength="{#EXEC_DURATION_MAXLEN#}">  		 			
                <br />
                <br />    
    		 			  {if $args_save_type == 'single'}
    		 			      <input type="submit" name="save_results[{$args_tcversion_id}]" 
    		 			            {$args_input_enable_mgmt}
                          onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};return checkSubmitForStatus('{$ResultsStatusCode.not_run}')"
    		 			            value="{$args_labels.btn_save_tc_exec_results}" />
    		 			         
    		 			      <input type="submit" name="save_and_next[{$args_tcversion_id}]" 
    		 			            {$args_input_enable_mgmt}
                          onclick="document.getElementById('save_button_clicked').value={$args_tcversion_id};return checkSubmitForStatus('{$ResultsStatusCode.not_run}')"
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