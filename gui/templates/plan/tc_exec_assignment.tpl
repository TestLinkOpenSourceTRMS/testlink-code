{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tc_exec_assignment.tpl,v 1.5 2008/01/14 21:43:23 franciscom Exp $
generate the list of TC that can be removed from a Test Plan 

rev :
     20070930 - franciscom - BUGID 
     tcase name href to open window with test case spec.
     
     20070407 - franciscom - gui refactoring
     20070120 - franciscom - BUGID 530
*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
</head>
<body>

<h1>{lang_get s='title_tc_exec_assignment'}  {$testPlanName|escape}</h1>


{if $has_tc }

{lang_get var="labels" s='user_bulk_assignment,btn_do,check_uncheck_all_checkboxes,th_id,
                          btn_update_selected_tc,show_tcase_spec,can_not_execute,
                          th_test_case,version,assigned_to,assign_to,note_keyword_filter'}





{include file="inc_update.tpl" result=$sqlResult refresh="yes"}
{if $key ne ''}
	<div style="margin-left: 20px; font-size: smaller;"><p>{$labels.note_keyword_filter} '{$key|escape}'</p></div>
{/if}

{* prefix for checkbox name ADD*}   
{assign var="add_cb" value="achecked_tc"}
 
  
<form id='tc_exec_assignment' name='tc_exec_assignment' method='post'>

{* 20070406 *}
<div class="workBack" style="height: 450px; overflow-y: auto;">
	
	{foreach from=$arrData item=ts}
	  {assign var="ts_id" value=$ts.testsuite.id}
	  {assign var="div_id" value=div_$ts_id}
	  
	  <div id="{$div_id}" style="margin:0px 0px 0px {$ts.level}0px;">
	    <h3>{$ts.testsuite.name|escape}</h3>

     {* used as memory for the check/uncheck all checkbox javascript logic *}
     <input type="hidden" name="add_value_{$ts_id}"  id="add_value_{$ts_id}"  value="0" />

    	{if $ts.write_buttons eq 'yes'}
  			<br />
  			{$labels.user_bulk_assignment}
  			
  			{* Bulk Tester Object ID (BTOID)*}
  			{assign var=btoid value=bulk_tester_div_$ts_id}
  			
  			<select name="bulk_tester_div[{$ts_id}]"  id="{$btoid}">
      		{html_options options=$testers selected=0}
      	</select>
      	<input type='button' name='{$ts.testsuite.name|escape}_mua' 
      	      onclick='javascript: set_combo_if_checkbox("{$div_id}",
      	                                                 "tester_for_tcid_",
      	                                                 document.getElementById("{$btoid}").value)' 
      	       value="{$labels.btn_do}" />
  			<br />
              	     

      {if $ts.testcase_qty gt 0 }
          <table cellspacing="0" style="font-size:small;" width="100%">
           <tr style="background-color:blue;font-weight:bold;color:white">
			     <td width="5" align="center">
			         <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			              onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}_","add_value_{$ts_id}");'
                    title="{$labels.check_uncheck_all_checkboxes}" />
			     </td>
          	<td class="tcase_id_cell">{$labels.th_id}</td> 
            <td>{$labels.th_test_case}</td>
          	<td align="center">&nbsp;&nbsp;{$labels.version}</td>
          	<td align="center">&nbsp;&nbsp;{$labels.assigned_to}</td>
          	<td align="center">&nbsp;&nbsp;{$labels.assign_to}</td>
          </tr>
          {foreach from=$ts.testcases item=tcase }
          	{if $tcase.linked_version_id ne 0}
          	  <tr>
            	  <td>
						<input type="hidden" name="a_tcid[{$tcase.id}]" value="{$tcase.linked_version_id}" />
						<input type="hidden" name="has_prev_assignment[{$tcase.id}]" value="{$tcase.user_id}" />
						<input type="hidden" name="feature_id[{$tcase.id}]" value="{$tcase.feature_id}" />
        				<input type="checkbox"  name='{$add_cb}[{$tcase.id}]' 
    				                            id='{$add_cb}_{$tcase.id}' 
        				                        value="{$tcase.linked_version_id}" />
       				  </td>
            	  <td>
            	  {$testCasePrefix}{$tcase.external_id}
                </td>
            	  <td title="{$labels.show_tcase_spec}">
            	    <a href="javascript:openTCaseWindow({$tcase.id})">{$tcase.name|escape}</a>
                </td>
                <td align="center">
        				{$tcase.tcversions[$tcase.linked_version_id]}
                </td>
                <td align="center">
                {$users[$tcase.user_id]}
                {if $users[$tcase.user_id] != '' && $testers[$tcase.user_id] == ''}{$labels.can_not_execute}{/if} 
                </td>

                <td align="center">
      		  		<select name="tester_for_tcid[{$tcase.id}]" 
      		  		        id="tester_for_tcid_{$tcase.id}"
      		  		        onchange='javascript: set_checkbox("achecked_tc_{$tcase.id}",1)' >
      			  	{html_options options=$testers selected=$tcase.user_id}
      				  </select>
              </td>
              </tr>
    	      {/if}		
    			{/foreach}
          </table>
      {/if}
    
    {/if} {* write buttons*}
    </div>
	{/foreach}

</div>

<div class="workBack">    
	<input type='submit' name='doAction' value='{$labels.btn_update_selected_tc}' />
</div>

</form>

{else}
 <h2>{lang_get s='no_testcase_available'}</h2>
{/if}

</body>
</html>