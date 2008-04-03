{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planAddTC_m1.tpl,v 1.8 2008/04/03 06:52:32 franciscom Exp $
Purpose: smarty template - generate a list of TC for adding to Test Plan 
*}

{config_load file="input_dimensions.conf" section="planAddTC"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
</head>
<body>
<h1 class="title">{lang_get s='test_plan'}{$smarty.const.TITLE_SEP}{$testPlanName|escape}</h1>

{if $has_tc }
<form name='addTcForm' id='addTcForm' method='post'>
   <h1 class="title">
    {if $full_control eq 1}
      {if $has_linked_items eq 0} 
  		   {lang_get s='title_add_test_to_plan'}
      {else}
  		   {lang_get s='title_add_remove_test_to_plan'}
      {/if}     
    {else}
  	   {lang_get s='title_remove_test_from_plan'}
    {/if}    
    
    </h1>
    {include file="inc_update.tpl" result=$sqlResult}

  {if $key != ''}
	  <div style="margin-left: 20px; font-size: smaller;">
		  <br />{lang_get s='note_keyword_filter'}{$key|escape}</p>
	  </div>
  {/if}
  
<div class="workBack" style="height: 380px; overflow-y: auto;">
     
  {* prefix for checkbox named , ADD and ReMove *}   
  {assign var="add_cb" value="achecked_tc"} 
  {assign var="rm_cb" value="remove_checked_tc"}
  
  
  {assign var="item_number" value=0}
  <input type="hidden" name="add_all_value"  id="add_all_value"  value="0" />
  <input type="hidden" name="rm_all_value"  id="rm_all_value" value="0" />
  
	{foreach from=$arrData item=ts}
	  {assign var="item_number" value=$item_number+1}
	
	  {assign var="ts_id" value=$ts.testsuite.id}
	  {assign var="div_id" value=div_$ts_id}
	  
	
	<div id="{$div_id}"  style="margin:0px 0px 0px {$ts.level}0px;">
	    <h3 class="testlink">{$ts.testsuite.name|escape}</h3> 
	        {if $item_number ==1}
	          <br />
            <table cellspacing="0" style="font-size:small;background-color:blue;font-weight:bold;color:white" 
                   width="100%">
            <tr>
				<td align="center">
	            {if $full_control }
		          <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" border="0" 
		               alt="{lang_get s='check_uncheck_all_checkboxes_for_add'}" 
	                 title="{lang_get s='check_uncheck_all_checkboxes_for_add'}" 
	                 onclick="cs_all_checkbox_in_div('addTcForm','{$add_cb}','add_all_value');" />
	            {lang_get s='add'}
	            {else} &nbsp;
	            {/if}
	            </td>
	            <td  {if $full_control } align="center" {else} align="left" {/if}>
		          <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" border="0" 
		               alt="{lang_get s='check_uncheck_all_checkboxes_for_rm'}" 
	                 title="{lang_get s='check_uncheck_all_checkboxes_for_rm'}" 
	                 onclick="cs_all_checkbox_in_div('addTcForm','{$rm_cb}','rm_all_value');" />
	            {lang_get s='remove'}
	            </td>
            </tr>
            </table>
	        {/if}
   
     {* used as memory for the check/uncheck all checkbox javascript logic *}
     <input type="hidden" name="add_value_{$ts_id}"  id="add_value_{$ts_id}"  
            value="0" />
     <input type="hidden" name="rm_value_{$ts_id}"  id="rm_value_{$ts_id}"  
            value="0" />
            
     {* ------------------------------------------------------------------------- *}      
     {if ($full_control && $ts.testcase_qty gt 0) || $ts.linked_testcase_qty gt 0 }
        
        <table cellspacing="0" style="font-size:small;" width="100%">
          <tr style="background-color:blue;font-weight:bold;color:white">

			     <td width="5" align="center">
              {if $full_control}
			         <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			              onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}","add_value_{$ts_id}");'
                    title="{lang_get s='check_uncheck_all_checkboxes'}" />
    			    {else}
    			     &nbsp;
		    	    {/if}
			     </td>
			     
			     <td>{lang_get s='th_id'}</td> 
			     <td>{lang_get s='th_test_case'}</td>
			     <td>{lang_get s='version'}</td>
           <td>{lang_get s='execution_order'}</td>
           {if $ts.linked_testcase_qty gt 0 }
				    <td>&nbsp;</td>
				    <td>
				    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" 
                 onclick='cs_all_checkbox_in_div("{$div_id}","{$rm_cb}","rm_value_{$ts_id}");'
                 title="{lang_get s='check_uncheck_all_checkboxes'}" />
				    {lang_get s='remove_tc'}
				    </td>
           {/if}
          </tr>   
          
          {foreach from=$ts.testcases item=tcase}
            
            {assign var='is_active' value=0}
            {if $tcase.linked_version_id neq 0 }
               {if $tcase.tcversions_active_status[$tcase.linked_version_id] eq 1}             
                 {assign var='is_active' value=1}
               {/if}
            {else}
               {if $tcase.tcversions_qty neq 0}
                 {assign var='is_active' value=1}
               {/if}
            {/if}      


            {if $is_active || $tcase.linked_version_id ne 0 }  
   				    {if $full_control || $tcase.linked_version_id ne 0 }
    			    <tr {if $tcase.linked_version_id ne 0}
    			         style="{$smarty.const.TL_STYLE_FOR_ADDED_TC}" {/if}>
    			      <td width="20">
    				    {if $full_control}
	      				    {if $is_active eq 0 || $tcase.linked_version_id ne 0 }
	      				       &nbsp;&nbsp;
	      				    {else}
	      				      <input type="checkbox" 
	      				             name="{$add_cb}[{$tcase.id}]" 
	      				             id="{$add_cb}{$tcase.id}" 
	      				             value="{$tcase.id}" /> 
	      				    {/if}
      				    
	      				    <input type='hidden' name='a_tcid[{$tcase.id}]' value='{$tcase.id}' />
    				    {else}
							&nbsp;&nbsp;
    				    {/if}
    			      </td>
    			      
    			      <td>
    				    {$testCasePrefix}{$tcase.external_id}
    			      </td>
    			      {* 20070930 - franciscom - REQ - BUGID 1078 *}
    				    <td title="{lang_get s='show_tcase_spec'}">
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
                         style="text-align:right;"
                  			 size="{#EXECUTION_ORDER_SIZE#}" 
 			                   maxlength="{#EXECUTION_ORDER_MAXLEN#}" 
                         value="{$tcase.execution_order}">
                  
                  {if $tcase.linked_version_id ne 0}          
                    <input type="hidden" name="linked_exec_order[{$tcase.id}]"
                                         value="{$tcase.id}">
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
                            &nbsp;&nbsp;&nbsp;{lang_get s='has_been_executed'}
                     {/if}    
                     {if $is_active eq 0}
                           &nbsp;&nbsp;&nbsp;{lang_get s='inactive_testcase'}
                     {/if}
          				</td>
                {/if}
                {* ------------------------------------------------------------------------- *}      
 
              </tr>
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
      <input type="hidden" name="doAction" id="doAction" value="default">
      
      <br /><input type="submit" name="doAddRemove" style="padding-right: 20px;"
                   onclick="doAction.value=this.name"
         {if $full_control}
  		     {if $has_linked_items eq 0}
  	      	   value='{lang_get s='btn_add_selected_tc'}'
  		     {else}
               value='{lang_get s='btn_add_remove_selected_tc'}' 
  		     {/if}
		     {else}
               value='{lang_get s='btn_remove_selected_tc'}' 
		     {/if}
         />
      		<input type="submit" name="doReorder" value="{lang_get s='btn_save_exec_order'}" 
                 onclick="doAction.value=this.name"/>

   </div>

</form>

{else}
	<h2>{lang_get s='no_testcase_available'}</h2>
{/if}

{* 
 refresh is useful when operating in full_control=0 => just remove,
 because tree is test plan tree.
*}
{if $refreshTree}
   {include file="inc_refreshTree.tpl"}
{/if}

</body>
</html>
