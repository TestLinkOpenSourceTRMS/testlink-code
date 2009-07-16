{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tc_exec_assignment.tpl,v 1.17 2009/07/16 14:05:47 havlat Exp $
generate the list of TC that can be removed from a Test Plan 

rev :
     20090215 - franciscom - BUGID 2114
     20070930 - franciscom - BUGID 
     tcase name href to open window with test case spec.
     20070120 - franciscom - BUGID 530
*}

{lang_get var="labels" s='user_bulk_assignment,btn_do,check_uncheck_all_checkboxes,th_id,
                          btn_update_selected_tc,show_tcase_spec,can_not_execute,
                          send_mail_to_tester,
                          exec_assign_no_testcase,warning,check_uncheck_children_checkboxes,
                          th_test_case,version,assigned_to,assign_to,note_keyword_filter'}

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
<body>

<h1 class="title">{$gui->main_descr|escape}</h1>

{if $gui->has_tc }

{include file="inc_update.tpl" result=$sqlResult refresh="yes"}
{* 20081221 - franciscom
{if $key ne ''}
	<div style="margin-left: 20px; font-size: smaller;"><p>{$labels.note_keyword_filter} '{$key|escape}'</p></div>
{/if}
*}

{* prefix for checkbox name ADD*}   
{assign var="add_cb" value="achecked_tc"}
  
<form id='tc_exec_assignment' name='tc_exec_assignment' method='post'>
<div class="workBack">

	<div class="groupBtn">    
		<input type='submit' name='doAction' value='{$labels.btn_update_selected_tc}' />
		<span style="margin-left:20px;"><input type="checkbox" name="send_mail" id="send_mail" {if $gui->send_mail eq 1} checked="checked" {/if}/>
		{$labels.send_mail_to_tester}
		</span>
	</div>

	<div style="height: 650px; overflow-y: auto;">	
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
  		{assign var=btoid value=bulk_tester_div_$ts_id}
  		
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
			<tr style="background-color:#059; font-weight:bold; color:white">
				<td width="5" align="center">
			           <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			                onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}_{$ts_id}_","add_value_{$ts_id}");'
                      title="{$labels.check_uncheck_all_checkboxes}" />
				</td>
            	<td class="tcase_id_cell">{$labels.th_id}</td> 
            	<td>{$labels.th_test_case}&nbsp;{$gsmarty_gui->role_separator_open}
            		{$labels.version}{$gsmarty_gui->role_separator_close}</td>
            	<td align="center">&nbsp;&nbsp;{$labels.assigned_to}</td>
            	<td align="center">&nbsp;&nbsp;{$labels.assign_to}</td>
            </tr>
            {foreach from=$ts.testcases item=tcase }
            	{if $tcase.linked_version_id ne 0}
				<tr>
					<td>
          				<input type="checkbox"  name='{$add_cb}[{$tcase.id}]' align="middle"
    	  			                            id='{$add_cb}_{$ts_id}_{$tcase.id}' 
          				                        value="{$tcase.linked_version_id}" />
			  			<input type="hidden" name="a_tcid[{$tcase.id}]" value="{$tcase.linked_version_id}" />
			  			<input type="hidden" name="has_prev_assignment[{$tcase.id}]" value="{$tcase.user_id}" />
			  			<input type="hidden" name="feature_id[{$tcase.id}]" value="{$tcase.feature_id}" />
					</td>
					<td>
						{$gui->testCasePrefix|escape}{$tcase.external_id|escape}
					</td>
					<td title="{$labels.show_tcase_spec}">
						&nbsp;<a href="javascript:openTCaseWindow({$tcase.id})">{$tcase.name|escape}</a>
						&nbsp;{$gsmarty_gui->role_separator_open} {$tcase.tcversions[$tcase.linked_version_id]}
						{$gsmarty_gui->role_separator_close}
					</td>
					<td align="center">
					{if $tcase.user_id > 0}
						{$gui->users[$tcase.user_id]|escape}
						{if $gui->users[$tcase.user_id] != '' && $gui->testers[$tcase.user_id] == ''}{$labels.can_not_execute}{/if}
					{/if}
					</td>
                  	<td align="center">
        		  		<select name="tester_for_tcid[{$tcase.id}]" 
        		  		        id="tester_for_tcid_{$tcase.id}"
        		  		        onchange='javascript: set_checkbox("{$add_cb}_{$ts_id}_{$tcase.id}",1)' >
        			   	{html_options options=$gui->testers selected=$tcase.user_id}
        				  </select>
                	</td>
                </tr>
    	        {/if}		
    	  		{/foreach}
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
</div>
</form>

{else}
	<div class="workBack">{lang_get s='no_testcase_available'}</div>
{/if}

</body>
</html>