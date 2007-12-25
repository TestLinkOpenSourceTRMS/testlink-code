{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_controls.tpl,v 1.1 2007/12/25 20:16:18 franciscom Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

Rev:
*}	

  		<table border="0" width="100%">
  		<tr>
  			<td rowspan="2" align="center">
  				<div class="title">{$args_labels.test_exec_notes}</div>
  				<textarea {$args_input_enabled_disabled} class="tcDesc" name='notes[{$args_tcversion_id}]' 
  					rows="10" style="width:99%"></textarea>			
  			</td>
  			<td valign="top" style="width:30%">			
    				{* status of test *}
      			<div class="title" style="text-align: center;">{$args_labels.test_exec_result}</div>
    				
    				<div class="resultBox">
                  {foreach key=verbose_status item=locale_status from=$gsmarty_tc_status_for_ui}
    						<input type="radio" {$args_input_enabled_disabled} name="status[{$args_tcversion_id}]" 
    							value="{$gsmarty_tc_status.$verbose_status}"
    							{if $gsmarty_tc_status.$verbose_status eq $gsmarty_tc_status.$default_status}
    							checked="checked" 
    							{/if} /> &nbsp;{lang_get s=$locale_status}<br />
    					 {/foreach}		
    					<br />		
    		 			<input type="submit" name="save_results[{$args_tcversion_id}]" 
    		 			       {$args_input_enabled_disabled}
    		 			       value="{$args_labels.btn_save_tc_exec_results}" />
    				</div>
    			</td>
    		</tr>
  		</table>
