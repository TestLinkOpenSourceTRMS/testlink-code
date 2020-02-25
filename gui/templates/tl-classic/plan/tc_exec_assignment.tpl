{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 

generate the list of TC that can be removed from a Test Plan 

@filesource tc_exec_assignment.tpl
@internal revisions
@since 1.9.12
*}

{lang_get var="labels" s='user_bulk_assignment,btn_do,check_uncheck_all_checkboxes,th_id,
                          btn_update_selected_tc,show_tcase_spec,can_not_execute,
                          send_mail_to_tester,platform,no_testcase_available,chosen_blank_option,
                          exec_assign_no_testcase,warning,check_uncheck_children_checkboxes,
                          th_test_case,version,assigned_to,assign_to,note_keyword_filter,priority,
                          check_uncheck_all_tc,execution,design,execution_history,remove'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
// Escape all messages (string)
var check_msg="{$labels.exec_assign_no_testcase|escape:'javascript'}";
var alert_box_title = "{$labels.warning|escape:'javascript'}";

// loop2do=0;   // needed for the convert grid logic
function check_action_precondition(container_id,action)
{
	if(checkbox_count_checked(container_id) <= 0)
	{
		alert_message(alert_box_title,check_msg);
		return false;
	}
	return true;
}

/**
 * Uses JQuery needed if select uses chosen plugin !!!
 */
function setComboIfCbx(oid,combo_id_prefix,value_to_assign)
{
  var f=document.getElementById(oid);
  var all_inputs = f.getElementsByTagName('input');
  var input_element;
  var check_id='';
  var apieces='';
  var combo_id_suffix='';
  var cb_id= new Array();
  var jdx=0;
  var idx=0;
    
  // Build an array with the html select ids
  //  
  for(idx = 0; idx < all_inputs.length; idx++)
  {
    input_element=all_inputs[idx];    
    if(input_element.type == "checkbox" &&  
       input_element.checked  &&
       !input_element.disabled)
    {
      check_id=input_element.id;
      
      // Consider the id a list with '_' as element separator
      apieces=check_id.split("_");
      
      // apieces.length-2 => test case id
      // apieces.length-1 => platform id
      combo_id_suffix=apieces[apieces.length-2] + '_' + apieces[apieces.length-1];
      cb_id[jdx]=combo_id_prefix + combo_id_suffix;
      jdx++;
    } 
  }

  // To avoid issues with $  
  jQuery.noConflict();

  // now set the combos
  for(idx = 0; idx < cb_id.length; idx++)
  {
     // debug - alert(cb_id[idx] + " will be" + value_to_assign);
     // input_element=document.getElementById(cb_id[idx]);
     // input_element.value=value_to_assign;
     jQuery('#' + cb_id[idx]).val(value_to_assign);
     jQuery('#' + cb_id[idx]).trigger("chosen:updated");  // needed by chosen
  }
}
</script>

</head>
{* prefix for checkbox name ADD*}   
{$add_cb="achecked_tc"}

<body class="fixedheader">
<form id='tc_exec_assignment' name='tc_exec_assignment' method='post'>

  {* --------------------------------------------------------------------------------------------------------------- *}
  {* added z-index to avoid problems with scrolling when using EXT-JS *}
  <div id="header-wrap" style="z-index:999;height:110px;"> <!-- header-wrap -->
	<h1 class="title">{$gui->main_descr|escape}</h1>
  {if $gui->has_tc}
    {include file="inc_update.tpl" result=$sqlResult refresh="yes"}
	<div class="groupBtn">
		<div>
			{$labels.check_uncheck_all_tc}
			{if $gui->usePlatforms}
			<select id="select_platform">
				{html_options options=$gui->bulk_platforms}
			</select>
			{else}
			<input type="hidden" id="select_platform" value="0">
			{/if}
			<button onclick="cs_all_checkbox_in_div_with_platform('tc_exec_assignment_cb', '{$add_cb}', Ext.get('select_platform').getValue()); return false">{$labels.btn_do}</button>
		</div>
		<div>
			{$labels.user_bulk_assignment}
			<select name="bulk_tester_div"  id="bulk_tester_div">
				{html_options options=$gui->testers selected=0}
			</select>
			<input type='button' name='bulk_user_assignment' id='bulk_user_assignment'
				onclick='if(check_action_precondition("tc_exec_assignment","default"))
						        setComboIfCbx("tc_exec_assignment_cb","tester_for_tcid_",Ext.get("bulk_tester_div").getValue())'
				value="{$labels.btn_do}" />
		</div>
		<div>
			<input type='submit' name='doActionButton' id='doActionButton' value='{$labels.btn_update_selected_tc}' />
      <input type="hidden" name="doAction" id="doAction" value='std' />
			<span style="margin-left:20px;"><input type="checkbox" name="send_mail" id="send_mail" {$gui->send_mail_checked} />
			{$labels.send_mail_to_tester}
			</span>
		</div>
	</div>
  {else}
	  <div class="workBack">{$labels.no_testcase_available}</div>
  {/if}
	</div> <!-- header-wrap -->

  {if $gui->has_tc}
   <div class="workBack" id="tc_exec_assignment_cb">
    <input type="hidden" name="targetFeature" id="targetFeature" value="0"/>
    <input type="hidden" name="targetUser" id="targetUser" value="0"/>

	  {$table_counter=0}
	  {$top_level=$gui->items[0].level}
	  {foreach from=$gui->items item=ts key=idx name="div_drawing"}
	    {$ts_id=$ts.testsuite.id}
	    {$div_id="div_$ts_id"}
	    {if $ts_id != ''}
	      <div id="{$div_id}" style="margin-left:{$ts.level}0px; border:1;">
        <br />
        {* check/uncheck on ALL contained test suites is implemented with this clickable image *}
	      <h3 class="testlink"><img class="clickable" src="{$tlImages.toggle_all}"
			                            onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}_","add_value_{$ts_id}");'
                                  title="{$labels.check_uncheck_children_checkboxes}" />
        {$ts.testsuite.name|escape}
	      </h3>

        {* used as memory for the check/uncheck all checkbox javascript logic *}
        <input type="hidden" name="add_value_{$ts_id}"  id="add_value_{$ts_id}"  value="0" />

    	  {if $ts.write_buttons eq 'yes'}
          {if $ts.testcase_qty gt 0}
	          {$table_counter=$table_counter+1}
            <table cellspacing="0" style="font-size:small;" width="100%" id="the-table-{$table_counter}">
            {* ---------------------------------------------------------------------------------------------------- *}
			      {* Heading *}
			      <thead>
			      <tr style="background-color:#059; font-weight:bold; color:white">
			      	<th width="35px" align="center">
			          <img class="clickable" src="{$tlImages.toggle_all}"
			               onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}_{$ts_id}_","add_value_{$ts_id}");'
                     title="{$labels.check_uncheck_all_checkboxes}" />
			      	</th>
              <th>{$labels.th_test_case}&nbsp;{$gsmarty_gui->role_separator_open}
              	{$labels.version}{$gsmarty_gui->role_separator_close}</th>
              	
              {if $gui->platforms != ''}
			      	  <th>{$labels.platform}</th>
              {/if}	
			      	{if $session['testprojectOptions']->testPriorityEnabled}
			      	  <th align="center">{$labels.priority}</th>
			      	{/if}
              <th style="align:left;">&nbsp;&nbsp;{$labels.assigned_to}</th>
              <th style="align:center;">&nbsp;&nbsp;{$labels.assign_to}</th>
            </tr>
			      </thead>
            {* ---------------------------------------------------------------------------------------------------- *}
            <tbody>  
            {foreach from=$ts.testcases item=tcase}

              {* loop over platforms - ATTENTION al least platform_id=0 always exists *}
              {foreach from=$tcase.feature_id key=platform_id item=feature}
                {if $tcase.linked_version_id != 0}
                  {foreach from=$tcase.user_id[$platform_id] key=udx item=userItem name="testerSet"}
                    {$userID=0}
             	      {if isset($tcase.user_id[$platform_id][$udx])} 
                      {$userID=$tcase.user_id[$platform_id][$udx]} 
                    {/if} 

              	    <tr>
                    {if $smarty.foreach.testerSet.iteration == 1}
              	    	<td>
                      		<input type="checkbox" name='{$add_cb}[{$tcase.id}][{$platform_id}]' align="middle"
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
              	    		<img class="clickable" src="{$tlImages.history_small}"
              	    		     onclick="javascript:openExecHistoryWindow({$tcase.id});"
              	    		     title="{$labels.execution_history}" />
              	    		<img class="clickable" src="{$tlImages.exec_icon}"
              	    		     onclick="javascript:openExecutionWindow({$tcase.id},{$tcase.linked_version_id},{$gui->build_id},{$gui->tplan_id},{$platform_id});"
              	    		     title="{$labels.execution}" />
              	    		<img class="clickable" src="{$tlImages.edit}"
              	    		     onclick="javascript:openTCaseWindow({$tcase.id},{$tcase.linked_version_id});"
              	    		     title="{$labels.design}" />
              	    		{$gui->testCasePrefix|escape}{$tcase.external_id|escape}{$gsmarty_gui->title_separator_1}{$tcase.name|escape}
              	    		&nbsp;{$gsmarty_gui->role_separator_open} {$tcase.tcversions[$tcase.linked_version_id]}
              	    		{$gsmarty_gui->role_separator_close}
              	    	</td>

                      {if $gui->platforms != ''}
  			      	        <td>{$gui->platforms[$platform_id]|escape}</td>
                      {/if}	

              	    	{if $session['testprojectOptions']->testPriorityEnabled}
              	    		<td align="center">
                        {if isset($gui->priority_labels[$tcase.priority])}{$gui->priority_labels[$tcase.priority]}{/if}</td>
              	    	{/if}
                      
                    {else}
                        <td>&nbsp;</td><td>&nbsp;</td>
                        {if $gui->platforms != ''}<td>&nbsp;</td>{/if} 
                        {if $session['testprojectOptions']->testPriorityEnabled}<td>&nbsp;</td>{/if}
                    {/if} {* do it JUST ON first iteration *}

              	    	<td style="align:left;">
                        &nbsp;&nbsp;&nbsp;&nbsp;
              	    		{if $userID >0 && $gui->users[$userID] != ''}
                        <img class="clickable" src="{$tlImages.remove}"
                             onclick="doAction.value='doRemove';targetFeature.value={$tcase.feature_id[$platform_id]};targetUser.value={$userID};tc_exec_assignment.submit();"
                             title="{$labels.remove}" /> 
                          {$gui->users[$userID]|escape}
                          {if $gui->testers[$userID] == ''}{$labels.can_not_execute}{/if} {* user is a Tester? *}
                        {/if}                          
              	    	</td>
                      
                      {if $smarty.foreach.testerSet.iteration == 1}
                        <td align="center">
                      		  		<select class="chosen-select"
                                        data-placeholder="{$labels.chosen_blank_option}"
                                        name="tester_for_tcid[{$tcase.id}][{$platform_id}]" 
                      		  		        id="tester_for_tcid_{$tcase.id}_{$platform_id}"
                      		  		        onchange='javascript: set_checkbox("{$add_cb}_{$ts_id}_{$tcase.id}_{$platform_id}",1)' >
                                 {html_options options=$gui->testers}
                      				  </select>
                        </td>
                      {else}
                        <td>&nbsp;</td>
                      {/if}

                    </tr>
                  {/foreach} {* $tcase.user_id[$platform_id] *}
                {/if} {* $tcase.linked_version_id != 0 *}		
              {/foreach}   
            {/foreach} {* {foreach from=$ts.testcases item=tcase} *}
            </tbody>
          </table>
          {/if}
      {/if} {* write buttons*}

      {if $gui->items_qty eq $smarty.foreach.div_drawing.iteration}
          {$next_level=0}
      {else}
          {$next_level=$gui->items[$smarty.foreach.div_drawing.iteration].level}
      {/if}
      {if $ts.level gte $next_level}
          {$max_loop = $next_level+1}
          {$max_loop=$ts.level-$max_loop}
          {section name="div_closure" loop=$gui->support_array max=$max_loop} </div> {/section}
      {/if}
      {if $smarty.foreach.div_drawing.last}</div> {/if}
    
    {/if} {* $ts_id != '' *}
	{/foreach}

	</div>
  
 {/if}
  
</form>
<script>
jQuery( document ).ready(function() {
jQuery(".chosen-select").chosen({ width: "85%" });
});
</script>
</body>
</html>
