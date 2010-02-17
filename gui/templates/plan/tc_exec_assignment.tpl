{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tc_exec_assignment.tpl,v 1.22 2010/02/17 21:32:44 franciscom Exp $
generate the list of TC that can be removed from a Test Plan 

rev :
     20100209 - franciscom - minor code layout refactoring
     20100121 - eloff - BUGID 3078 - buttons always visible on top
     20090215 - franciscom - BUGID 2114
     20070120 - franciscom - BUGID 530
*}

{lang_get var="labels" s='user_bulk_assignment,btn_do,check_uncheck_all_checkboxes,th_id,
                          btn_update_selected_tc,show_tcase_spec,can_not_execute,
                          send_mail_to_tester,platform,no_testcase_available,
                          exec_assign_no_testcase,warning,check_uncheck_children_checkboxes,
                          th_test_case,version,assigned_to,assign_to,note_keyword_filter, priority'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
	var check_msg="{$labels.exec_assign_no_testcase}";
	var alert_box_title = "{$labels.warning}";
{literal}

function check_action_precondition(container_id,action)
{
	if(checkbox_count_checked(container_id) <= 0)
	{
		alert_message(alert_box_title,check_msg);
		return false;
	}
	return true;
}
</script>
{/literal}

</head>
{* prefix for checkbox name ADD*}   
{assign var="add_cb" value="achecked_tc"}

<body class="fixedheader">
<form id='tc_exec_assignment' name='tc_exec_assignment' method='post'>

  {* --------------------------------------------------------------------------------------------------------------- *}
  <div id="header-wrap"> <!-- header-wrap -->
	<h1 class="title">{$gui->main_descr|escape}</h1>
  {if $gui->has_tc }
    {include file="inc_update.tpl" result=$sqlResult refresh="yes"}
	  <div class="groupBtn">    
	  	<input type='submit' name='doAction' value='{$labels.btn_update_selected_tc}' />
	  	<span style="margin-left:20px;"><input type="checkbox" name="send_mail" id="send_mail" {$gui->send_mail_checked} />
	  	{$labels.send_mail_to_tester}
	  	</span>
	  </div>
  {else}
	  <div class="workBack">{$labels.no_testcase_available'}</div>
  {/if}
	</div> <!-- header-wrap -->

  {if $gui->has_tc }
   <div class="workBack">
	  {assign var=top_level value=$gui->items[0].level}
	  {foreach from=$gui->items item=ts key=idx name="div_drawing"}
	    {assign var="ts_id" value=$ts.testsuite.id}
	    {assign var="div_id" value=div_$ts_id}
	    {if $ts_id != '' }
	      <div id="{$div_id}" style="margin-left:{$ts.level}0px; border:1;">
        <br />
        {* check/uncheck on ALL contained test suites is implemented with this clickable image *}
	      <h3 class="testlink"><img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			                            onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}_","add_value_{$ts_id}");'
                                  title="{$labels.check_uncheck_children_checkboxes}" />
        {$ts.testsuite.name|escape}
	      </h3>

        {* used as memory for the check/uncheck all checkbox javascript logic *}
        <input type="hidden" name="add_value_{$ts_id}"  id="add_value_{$ts_id}"  value="0" />
  		  {$labels.user_bulk_assignment}
  		
  		  {* Bulk Tester Object ID (BTOID)*}
  		  {assign var=btoid value="bulk_tester_div_$ts_id"}
  		
  		  <select name="bulk_tester_div[{$ts_id}]"  id="{$btoid}">
      	  {html_options options=$gui->testers selected=0}
        </select>
  		  <input type='button' name='{$ts.testsuite.name|escape}_mua' 
               onclick='if(check_action_precondition("{$div_id}","default"))
                           set_combo_if_checkbox("{$div_id}","tester_for_tcid_",
                                                 document.getElementById("{$btoid}").value)' 
               value="{$labels.btn_do}" />
  		  <br />

    	  {if $ts.write_buttons eq 'yes'}
          {if $ts.testcase_qty gt 0 }
            <table cellspacing="0" style="font-size:small;" width="100%">
            {* ---------------------------------------------------------------------------------------------------- *}
			      {* Heading *}
			      <tr style="background-color:#059; font-weight:bold; color:white">
			      	<td width="5" align="center">
			          <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			               onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}_{$ts_id}_","add_value_{$ts_id}");'
                     title="{$labels.check_uncheck_all_checkboxes}" />
			      	</td>
              <td class="tcase_id_cell">{$labels.th_id}</td> 
              <td>{$labels.th_test_case}&nbsp;{$gsmarty_gui->role_separator_open}
              	{$labels.version}{$gsmarty_gui->role_separator_close}</td>
              	
              {if $gui->platforms != ''}
			      	  <td>{$labels.platform}</td>
              {/if}	
			      	{if $session['testprojectOptions']->testPriorityEnabled}
			      	  <td align="center">{$labels.priority}</td>
			      	{/if}
              <td align="center">&nbsp;&nbsp;{$labels.assigned_to}</td>
              <td align="center">&nbsp;&nbsp;{$labels.assign_to}</td>
            </tr>
            {* ---------------------------------------------------------------------------------------------------- *}
      
            {foreach from=$ts.testcases item=tcase }
              {* loop over platforms *}
              {foreach from=$tcase.feature_id key=platform_id item=feature}
                {if $tcase.linked_version_id != 0}
                  {assign var="userID" value=0 }
           	    	{if isset($tcase.user_id[$platform_id]) }
            	    	  {assign var="userID" value=$tcase.user_id[$platform_id] } 
                  {/if} 
            	    <tr>
            	    	<td>
                    		<input type="checkbox"  name='{$add_cb}[{$tcase.id}][{$platform_id}]' align="middle"
                  			                        id='{$add_cb}_{$ts_id}_{$tcase.id}_{$platform_id}' 
                    		                        value="{$tcase.linked_version_id}" />
                  			<input type="hidden" name="a_tcid[{$tcase.id}][{$platform_id}]" 
                  			                     value="{$tcase.linked_version_id}" />
                  			<input type="hidden" name="has_prev_assignment[{$tcase.id}][{$platform_id}]" 
                  			                     value="{$userID}" />
                  			<input type="hidden" name="feature_id[{$tcase.id}][{$platform_id}]" 
                  			                     value="{$tcase.feature_id[$platform_id]}" />
            	    	</td>
            	    	<td>
            	    		{$gui->testCasePrefix|escape}{$tcase.external_id|escape}
            	    	</td>
            	    	<td title="{$labels.show_tcase_spec}">
            	    		&nbsp;<a href="javascript:openTCaseWindow({$tcase.id})"><strong>{$tcase.name|escape}</strong></a>
            	    		&nbsp;{$gsmarty_gui->role_separator_open} {$tcase.tcversions[$tcase.linked_version_id]}
            	    		{$gsmarty_gui->role_separator_close}
            	    	</td>
                    {if $gui->platforms != ''}
			      	        <td>{$gui->platforms[$platform_id]|escape}</td>
                    {/if}	

            	    	{if $session['testprojectOptions']->testPriorityEnabled}
            	    		<td align="center">{if isset($gui->priority_labels[$tcase.priority])}{$gui->priority_labels[$tcase.priority]}{/if}</td>
            	    	{/if}
            	    	<td align="center">
            	    	{if isset($tcase.user_id[$platform_id]) }
            	    	  {assign var="userID" value=$tcase.user_id[$platform_id] } 
                      userID::{$userID}
            	    		{$gui->users[$userID]|escape}
            	    		{if $gui->users[$userID] != '' && $gui->testers[$userID] == ''}{$labels.can_not_execute}{/if}
            	    	{/if}
            	    	</td>
                    <td align="center">
                  		  		<select name="tester_for_tcid[{$tcase.id}][{$platform_id}]" 
                  		  		        id="tester_for_tcid_{$tcase.id}_{$platform_id}"
                  		  		        onchange='javascript: set_checkbox("{$add_cb}_{$ts_id}_{$tcase.id}_{$platform_id}",1)' >
                  			   	{*  {html_options options=$gui->testers selected=$tcase.user_id} *}
                  			   	{html_options options=$gui->testers selected=$userID}
                  				  </select>
                    </td>
                  </tr>
                  {/if}		
              {/foreach}            
              {if $gui->platforms != ''}
                <td colspan="8"><hr></td>
              {/if}
            {/foreach} {* {foreach from=$ts.testcases item=tcase } *}
          </table>
          {/if}
      {/if} {* write buttons*}

      {if $gui->items_qty eq $smarty.foreach.div_drawing.iteration }
          {assign var=next_level value=0}
      {else}
          {assign var=next_level value=$gui->items[$smarty.foreach.div_drawing.iteration].level}
      {/if}
      {if $ts.level gte $next_level}
          {assign var="max_loop" value=$next_level}
          {assign var="max_loop" value=$ts.level-$max_loop+1}
          {section name="div_closure" loop=$gui->support_array max=$max_loop} </div> {/section}
      {/if}
      {if $smarty.foreach.div_drawing.last}</div> {/if}
    
    {/if} {* $ts_id != '' *}
	{/foreach}
	</div>
  {/if}
  
</form>
</body>
</html>
