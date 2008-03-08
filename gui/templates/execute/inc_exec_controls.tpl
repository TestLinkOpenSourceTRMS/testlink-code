{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_exec_controls.tpl,v 1.3 2008/03/08 10:24:57 franciscom Exp $
Purpose: draw execution controls (input for notes and results)
Author : franciscom

Rev:
*}	

  		<table border="0" width="100%">
  		<tr>
  			<td rowspan="2" align="center">
  				<div class="title">{$args_labels.test_exec_notes}</div>
          {$args_webeditor} 
  			</td>
  			<td valign="top" style="width:30%">			
    				{* status of test *}
      			<div class="title" style="text-align: center;">{$args_labels.test_exec_result}</div>
    				
    				<div class="resultBox">
                  {foreach key=verbose_status item=locale_status from=$gsmarty_tc_status_for_ui}
    						<input type="radio" {$args_input_enable_mgmt} name="status[{$args_tcversion_id}]" 
    							value="{$gsmarty_tc_status.$verbose_status}"
    							{if $gsmarty_tc_status.$verbose_status eq $gsmarty_tc_status.$default_status}
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
