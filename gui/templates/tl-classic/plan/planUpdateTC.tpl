{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 

Purpose: generate a list of Test Cases linked to Test Plan 
         that have a newer available version.

@filesource	planUpdateTC.tpl,v 1.15 2010/11/06 11:42:47 amkhullar Exp $
@author		franciscom
@internal revisions
*}
{lang_get var='labels'
          s='no_testcase_available,test_plan,update_testcase_versions,
             update_all_testcase_versions,th_test_case,
             warning,no_testcase_checked,
             version,linked_version,newest_version,
             note_keyword_filter,check_uncheck_all,
             check_uncheck_all_checkboxes,th_id,has_been_executed,show_tcase_spec,
             update_to_version,inactive_testcase,btn_update_testplan_tcversions,
             compare,design,execution_history'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}
{include file="inc_jsCheckboxes.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_no_testcase_checked = "{$labels.no_testcase_checked|escape:'javascript'}";
{literal}
function validateForm(f)
{
  if( checkbox_count_checked(f.id) == 0)
  {
      alert_message(alert_box_title,warning_no_testcase_checked);
      return false;
  } 
 
  return true;
}
</script>
{/literal}

</head>

{assign var="update_cb" value="achecked_tc"} {* prefix for checkboxs *}
{assign var="item_number" value=0}

<body class="testlink">
<h1 class="title">{$labels.test_plan}{$smarty.const.TITLE_SEP}{$gui->testPlanName|escape}</h1>

{if $gui->hasItems}
  <form name="updateTcForm" id="updateTcForm" method="post"
        onSubmit="javascript:return validateForm(this);">
     <h1 class="title">{$gui->action_descr}</h1>
     {include file="inc_update.tpl" result=$sqlResult}

    <div class="workBack">
    {if $gui->instructions != ''}
      {$gui->instructions}
      {if $gui->user_feedback != ''}
         <br>{$gui->user_feedback}
      {/if}     
    {/if}     


  {if $gui->operationType == 'standard'}
    <input type="hidden" name="update_all_value"  id="update_all_value"  value="0" />

  	{foreach from=$gui->items item=ts}
  	  {assign var="item_number" value=$item_number+1}
  	  {assign var="ts_id" value=$ts.testsuite.id}
  	  {assign var="div_id" value="div_$ts_id"}
  	
  	  <div id="{$div_id}"  style="margin:0px 0px 0px {$ts.level}0px;">
  	    <h3 class="testlink">
        {if $item_number ==1}
  	    <img src="{$tlImages.toggle_all}" border="0" alt="{$labels.check_uncheck_all}" 
                   title="{$labels.check_uncheck_all}" 
                   onclick="cs_all_checkbox_in_div('updateTcForm','{$update_cb}','update_all_value');" />
        {/if}
        {$ts.testsuite.name|escape} 
  	    </h3> 
     
       {* used as memory for the check/uncheck all checkbox javascript logic *}
       <input type="hidden" name="update_value_{$ts_id}"  id="update_value_{$ts_id}"  value="0" />
              
       {* ------------------------------------------------------------------------- *}      
       {if $ts.testcase_qty gt 0 || $ts.linked_testcase_qty gt 0 }
          <table border="0" cellspacing="0" cellpadding="2" style="font-size:small;" width="100%">
            <tr style="background-color:blue;font-weight:bold;color:white">
  			     <th class="clickable_icon">
  			         <img src="{$tlImages.toggle_all}" title="{$labels.check_uncheck_all_checkboxes}"
  			              onclick='cs_all_checkbox_in_div("{$div_id}","{$update_cb}","update_value_{$ts_id}");' />
  			     </th>
  			     <th style="width:45%">{$labels.th_test_case}</th>
  			     <th class="clickable_icon">{$labels.version}</th>
  			     <th>&nbsp;</th>
  			     <th style="width:15%">{$labels.update_to_version}</th>
  			     <th>&nbsp;</th>
            </tr>   
            
            {foreach from=$ts.testcases item=tcase}
              
              {* some conditional design logic *}
              {assign var='is_active' value=0}
              {assign var='is_linked' value=0}
              {if $tcase.linked_version_id != 0 }
                 {assign var='is_linked' value=1}
              {/if}
              
              {* ------------------------------------------------ *}
     		  {if $is_linked }
      			    <tr class="testlink">
      			      <td width="20">
        				    <input type='hidden' name='a_tcid[{$tcase.id}]' value='{$tcase.linked_version_id}' />
        				    {if $tcase.canUpdateVersion }
        				      <input type='checkbox' name='{$update_cb}[{$tcase.id}]' 
        				             id='{$update_cb}{$tcase.id}' value='{$tcase.linked_version_id}' /> 
        				    {/if}
      			      </td>
					<td>
						<img class="clickable" src="{$tlImages.history_small}"
						     onclick="javascript:openExecHistoryWindow({$tcase.id});"
						     title="{$labels.execution_history}" />
						<img class="clickable" src="{$tlImages.edit}"
						     onclick="javascript:openTCaseWindow({$tcase.id});"
						     title="{$labels.design}" />
						{$gui->testCasePrefix|escape}{$tcase.external_id|escape}{$gsmarty_gui->title_separator_1}{$tcase.name|escape}
      			      </td>
  
                  <td style="text-align:center;">
                  	{$tcase.tcversions[$tcase.linked_version_id]}
                  </td>
                  <td style="text-align:center;">
                  	&nbsp;
                  </td>
  
                  <td style="text-align:center;">
                  	  {if $tcase.updateTarget != ''}	
                      <select name="new_tcversion_for_tcid[{$tcase.id}]">
           				       {html_options options=$tcase.updateTarget disabled=disabled}
           			  </select>
           			  {/if}
                  </td>
         
                  {* ------------------------------------------------------------------------- *}      
                  {if $ts.linked_testcase_qty gt 0 }
           				<td>
                       {if $tcase.executed == 'yes'}
                              &nbsp;&nbsp;&nbsp;{$labels.has_been_executed}
                       {/if}    
           				</td>
                  {/if}
                  {* ------------------------------------------------------------------------- *}      
                </tr>
             {/if} 
    	     {/foreach}
          </table>
          
          <br />
       {/if}  {* there are test cases to show ??? *}
      </div>
  
  	{/foreach}
  </div>
  {/if}  

  {if $gui->operationType == 'bulk'}
  <input type="hidden" name="update_all_value"  id="update_all_value"  value="1" />
	    <br/><table class="simple_tableruler">
	      <tr>
			    
			    <th class="clickable_icon">
             <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" border="0" 
	                alt="{$labels.check_uncheck_all}" title="{$labels.check_uncheck_all}" 
                  onclick="cs_all_checkbox_in_div('updateTcForm','{$update_cb}','update_all_value');" />
       
	        </th>
			    <th>{$labels.th_test_case}</th>
			    <th>{$labels.linked_version}</th>
			    <th>{$labels.newest_version}</th>
			    <th>{$labels.compare}</th>
	      </tr>   
	    
	      {foreach from=$gui->testcases item=tc}
	     	{assign var="item_number" value=$item_number+1}
	      <tr class="testlink">
	      	<td width="20">
      		 	<input type='checkbox' name='{$update_cb}[{$tc.tc_id}]' id='{$update_cb}{$tc.tc_id}' 
      				     value='{$tc.tcversion_id}' checked="checked" /> 
      			<input type='hidden' name='a_tcid[{$tc.tc_id}]' value='{$tc.newest_tcversion_id}' />
    		</td>
	      
			  <td>
			  <img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/history_small.png"
			       onclick="javascript:openExecHistoryWindow({$tc.tc_id});"
			       title="{$labels.execution_history}" />
			  <img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/edit_icon.png"
			       onclick="javascript:openTCaseWindow({$tc.tc_id});"
			       title="{$labels.design}" />
			    {$tc.path}{$gui->testCasePrefix|escape}{$tc.tc_external_id|escape}:{$tc.name|escape} </td>  
			  <td align="center"> {$tc.version|escape} </td>
			  <td align="center">
			  {$tc.newest_version|escape} 
			  <input type="hidden" name="new_tcversion_for_tcid[{$tc.tc_id}]" value="{$tc.newest_tcversion_id}" />
			  </td>
			  <td align="center">
			  <a href="lib/testcases/tcCompareVersions.php?testcase_id={$tc.tc_id}&version_left={$tc.version}&version_right={$tc.newest_version}&compare_selected_versions=1&use_html_comp=1" target="_blank">
			  <img src="{$smarty.const.TL_THEME_IMG_DIR}/magnifier.png"></img></a>
			  </td>
	      </tr>
	  	  {/foreach}
	  	</table>

  {/if}
 
    <br>   
    <input type="submit" id="update_btn" name="update_btn" style="padding-right: 20px;"
           value='{$labels.btn_update_testplan_tcversions}'  />
    <input type="hidden" name="doAction" id="doAction" value="{$gui->buttonAction}" />  
  </form>
{else}
  	<h2>{$gui->user_feedback}</h2>
{/if}


{* 
 refresh is useful when operating in full_control=0 => just remove,
 because tree is test plan tree.
*}
{if $gui->refreshTree}
	{include file="inc_refreshTreeWithFilters.tpl"}
	{*include file="inc_refreshTree.tpl"*}
{/if}

</body>
</html>
