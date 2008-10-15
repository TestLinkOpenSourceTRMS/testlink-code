{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planAddTC_m1.tpl,v 1.16 2008/10/15 20:36:52 schlundus Exp $
Purpose: smarty template - generate a list of TC for adding to Test Plan 
*}

{lang_get var="labels" 
          s='note_keyword_filter,check_uncheck_all_checkboxes_for_add,
             th_id,th_test_case,version,execution_order,
             no_testcase_available,btn_save_custom_fields,
             has_been_executed,inactive_testcase,btn_save_exec_order,
             check_uncheck_all_checkboxes,remove_tc,show_tcase_spec,
             check_uncheck_all_checkboxes_for_rm'}

{assign var="show_write_custom_fields" value=0}
{if $gui->full_control eq 1}
  {assign var="execution_order_html_disabled" value=''}

  {if $gui->has_linked_items eq 0} 
	   {lang_get s='title_add_test_to_plan' var="actionTitle"}
  	 {lang_get s='btn_add_selected_tc' var="buttonValue"}
  {else}
	   {lang_get s='title_add_remove_test_to_plan' var="actionTitle"}
	   {lang_get s='btn_add_remove_selected_tc' var="buttonValue"}
  {/if}     
{else}
   {lang_get s='title_remove_test_from_plan' var="actionTitle"}
   {lang_get s='btn_remove_selected_tc' var="buttonValue"}
   {assign var="execution_order_html_disabled" value='disabled="disabled"'}
{/if}    

{config_load file="input_dimensions.conf" section="planAddTC"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
</head>
<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>

{if $gui->has_tc }
<form name="addTcForm" id="addTcForm" method="post">
	<h1 class="title">{$actionTitle}</h1>
    {include file="inc_update.tpl" result=$sqlResult}

	{if $gui->keywords_filter != ''}
		<div style="margin-left: 20px; font-size: smaller;">
			<br />{$labels.note_keyword_filter}{$gui->keywords_filter|escape}</p>
		</div>
	{/if}
  
	<div class="workBack" style="height: 380px; overflow-y: auto;">
     
	{* prefix for checkbox named , ADD and ReMove *}   
	{assign var="add_cb" value="achecked_tc"} 
	{assign var="rm_cb" value="remove_checked_tc"}
  
	{assign var="item_number" value=0}
	<input type="hidden" name="add_all_value" id="add_all_value"  value="0" />
	<input type="hidden" name="rm_all_value" id="rm_all_value" value="0" />
  
	{foreach from=$gui->items item=ts}
		{assign var="item_number" value=$item_number+1}
		{assign var="ts_id" value=$ts.testsuite.id}
		{assign var="div_id" value=div_$ts_id}
	  
		<div id="{$div_id}"  style="margin:0px 0px 0px {$ts.level}0px;">
	    	<h3 class="testlink">{$ts.testsuite.name|escape}</h3> 
	        {if $item_number == 1}
	          	<br />
            	<table cellspacing="0" style="font-size:small;background-color:blue;font-weight:bold;color:white" 
                   width="100%">
            	<tr>
					<td align="center">
	            		{if $gui->full_control}
		          		<img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" border="0" 
		               		alt="{$labels.check_uncheck_all_checkboxes_for_add}" 
	                 		title="{$labels.check_uncheck_all_checkboxes_for_add}" 
	                 onclick="cs_all_checkbox_in_div('addTcForm','{$add_cb}','add_all_value');" />
	            {lang_get s='add'}
	            {else} &nbsp;
	            {/if}
	            </td>
	            <td  {if $gui->full_control } align="center" {else} align="left" {/if}>
		          <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" border="0" 
		               alt="{$labels.check_uncheck_all_checkboxes_for_rm}" 
	                 title="{$labels.check_uncheck_all_checkboxes_for_rm}" 
	                 onclick="cs_all_checkbox_in_div('addTcForm','{$rm_cb}','rm_all_value');" />
	            {lang_get s='remove'}
	            </td>
            </tr>
            </table>
	        {/if}
   
     {* used as memory for the check/uncheck all checkbox javascript logic *}
     <input type="hidden" name="add_value_{$ts_id}"  id="add_value_{$ts_id}"  value="0" />
     <input type="hidden" name="rm_value_{$ts_id}"  id="rm_value_{$ts_id}"  value="0" />
            
     {* ------------------------------------------------------------------------- *}      
     {if ($gui->full_control && $ts.testcase_qty gt 0) || $ts.linked_testcase_qty gt 0 }
        
        <table cellspacing="0" style="font-size:small;" width="100%">
          <tr style="background-color:blue;font-weight:bold;color:white">

			     <td width="5" align="center">
              {if $gui->full_control}
			         <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			              onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}","add_value_{$ts_id}");'
                    title="{$labels.check_uncheck_all_checkboxes}" />
    			    {else}
    			     &nbsp;
		    	    {/if}
			     </td>
			     
			     <td>{$labels.th_id}</td> 
			     <td>{$labels.th_test_case}</td>
			     <td>{$labels.version}</td>
           <td>{$labels.execution_order}</td>
           {if $ts.linked_testcase_qty gt 0 }
				    <td>&nbsp;</td>
				    <td>
				    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" 
                 onclick='cs_all_checkbox_in_div("{$div_id}","{$rm_cb}","rm_value_{$ts_id}");'
                 title="{$labels.check_uncheck_all_checkboxes}" />
				    {$labels.remove_tc}
				    </td>
           {/if}
          </tr>   
          
          {foreach from=$ts.testcases item=tcase}
            
            {assign var='is_active' value=0}
            {if $tcase.linked_version_id neq 0}
               {if $tcase.tcversions_active_status[$tcase.linked_version_id] eq 1}             
                 {assign var='is_active' value=1}
               {/if}
            {else}
               {if $tcase.tcversions_qty neq 0}
                 {assign var='is_active' value=1}
               {/if}
            {/if}      


            {if $is_active || $tcase.linked_version_id ne 0 }  
   				    {if $gui->full_control || $tcase.linked_version_id ne 0 }
    			    <tr {if $tcase.linked_version_id ne 0}
    			       	style="{$smarty.const.TL_STYLE_FOR_ADDED_TC}" {/if}>
    			      <td width="20">
    				    {if $gui->full_control}
	      				    {if $is_active eq 0 || $tcase.linked_version_id ne 0 }
	      				       &nbsp;&nbsp;
	      				    {else}
	      				      <input type="checkbox" 
	      				             name="{$add_cb}[{$tcase.id}]" 
	      				             id="{$add_cb}{$tcase.id}" 
	      				             value="{$tcase.id}" /> 
	      				    {/if}
      				    
	      				    <input type="hidden" name="a_tcid[{$tcase.id}]" value="{$tcase.id}" />
    				    {else}
							&nbsp;&nbsp;
    				    {/if}
    			      </td>
    			      
    			      <td>
    				    {$gui->testCasePrefix|escape}{$tcase.external_id}
    			      </td>
    			      {* 20070930 - franciscom - REQ - BUGID 1078 *}
    				    <td title="{$labels.show_tcase_spec}">
     				     <a href="javascript:openTCaseWindow({$tcase.id})">{$tcase.name|escape}</a>
    			      </td>
    			      
                <td>
         				  <select name="tcversion_for_tcid[{$tcase.id}]"
      			          {if $tcase.linked_version_id ne 0} disabled	{/if}>
         				      {html_options options=$tcase.tcversions selected=$tcase.linked_version_id}
         				  </select>
                </td>

                <td style="text-align:center;">
                  <input type="text" name="exec_order[{$tcase.id}]"
                         {$execution_order_html_disabled}
                         style="text-align:right;"
                  			 size="{#EXECUTION_ORDER_SIZE#}" 
 			                   maxlength="{#EXECUTION_ORDER_MAXLEN#}" 
                         value="{$tcase.execution_order}" />
                  
                  {if $tcase.linked_version_id != 0}  
                    <input type="hidden" name="linked_version[{$tcase.id}]"
                                         value="{$tcase.linked_version_id}" />
                          
                    <input type="hidden" name="linked_exec_order[{$tcase.id}]"
                                         value="{$tcase.execution_order}" />
                  {/if}
                </td>

        
                {* ------------------------------------------------------------------------- *}      
                {if $ts.linked_testcase_qty gt 0 }
          				<td>&nbsp;</td>
          				<td>
          				   {if $tcase.linked_version_id ne 0} 
          						<input type='checkbox' 
          						       name='{$rm_cb}[{$tcase.id}]' 
          						       id='{$rm_cb}{$tcase.id}' 
          				           value='{$tcase.linked_version_id}' />
          				   {else}
          						&nbsp;
          				   {/if}
                     {if $tcase.executed eq 'yes'}
                            &nbsp;&nbsp;&nbsp;{$labels.has_been_executed}
                     {/if}    
                     {if $is_active eq 0}
                           &nbsp;&nbsp;&nbsp;{$labels.inactive_testcase}
                     {/if}
          				</td>
                {/if}
                {* ------------------------------------------------------------------------- *}      
 
              </tr>
              {* 20080813 - franciscom - BUGID 1650 (REQ) *}
              {*
              {if $tcase.tcversions_execution_type[$tcase.linked_version_id] == {$smarty.const.TESTCASE_EXECUTION_TYPE_AUTO} &&
                  $tcase.custom_fields != ''}
      				*}
      				{if isset($tcase.custom_fields)}
      				    <input type='hidden' name='linked_with_cf[{$tcase.feature_id}]' value='{$tcase.feature_id}' />
                  {assign var="show_write_custom_fields" value=1}
              <tr> <td colspan="7">{$tcase.custom_fields}</td> </tr>
              {/if}
            {/if}  {* $tcase.tcversions_qty *}
           {/if} 
  	      {/foreach}
      
        </table>
        
        <br />
     {/if}  {* there are test cases to show ??? *}
    </div>

	{/foreach}
</div>

  <div class="workBack">   
      <input type="hidden" name="doAction" id="doAction" value="default" />
      
      <br /><input type="submit" name="doAddRemove" style="padding-right: 20px;"
                   onclick="doAction.value=this.name" value="{$buttonValue}" />
          
          {if $gui->full_control eq 1}
           	<input type="submit" name="doReorder" value="{$labels.btn_save_exec_order}" 
                   onclick="doAction.value=this.name" />

            {if $show_write_custom_fields eq 1}
             	<input type="submit" name="doSaveCustomFields" value="{$labels.btn_save_custom_fields}" 
                     onclick="doAction.value=this.name" />
            {/if}

          {/if}
   </div>

</form>

{else}
	<h2>{$labesl.no_testcase_available}</h2>
{/if}

{* 
 refresh is useful when operating in full_control=0 => just remove,
 because tree is test plan tree.
*}
{if $gui->refreshTree}
   {include file="inc_refreshTree.tpl"}
{/if}

</body>
</html>
