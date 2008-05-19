{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_controls.tpl,v 1.5 2008/05/19 10:23:53 havlat Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

Rev: 20080503 - franciscom - use of tlCfg 

*}	
      {assign var="ResultsStatusCode" value=$tlCfg->results.status_code}
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
    						<input type="radio" {$args_input_enable_mgmt} name="status[{$args_tcversion_id}]" 
    							value="{$ResultsStatusCode.$verbose_status}"
    							{if $verbose_status eq $tlCfg->results.default_status}
    							checked="checked" 
    							{/if} /> &nbsp;{lang_get s=$locale_status}<br />
    					 {/foreach}		
    					<br />		
    		 			<input type="submit" name="save_results[{$args_tcversion_id}]" 
    		 			       {$args_input_enable_mgmt}
    		 			       value="{$args_labels.btn_save_tc_exec_results}" />
    				</div>
    			</td>
    		</tr>
  		</table>
