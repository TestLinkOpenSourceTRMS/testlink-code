{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planUpdateTC.tpl,v 1.2 2008/01/26 17:55:11 franciscom Exp $
Purpose: smarty template - generate a list of TC for adding to Test Plan 

*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}
</head>

{lang_get var='labels'
          s='test_plan,update_testplan,note_keyword_filter,check_uncheck_all,
            check_uncheck_all_checkboxes,th_id,has_been_executed,
            update_to_version,inactive_testcase,btn_update_testplan_tcversions'}

<body>
<h1>{$labels.test_plan}{$smarty.const.TITLE_SEP}{$testPlanName|escape}
</h1>


{if $has_tc }
<form name='updateTcForm' id='updateTcForm' method='post'>
   <h1>{$labels.update_testplan}</h1>
    {include file="inc_update.tpl" result=$sqlResult}

  {if $key ne ''}
	  <div style="margin-left: 20px; font-size: smaller;">
		  <br />{$labels.note_keyword_filter}{$key|escape}</p>
	  </div>
  {/if}
  
<div class="workBack" style="height: 380px; overflow-y: auto;">
     
  {* prefix for checkboxs *}   
  {assign var="update_cb" value="achecked_tc"} 
  {assign var="item_number" value=0}
  <input type="hidden" name="update_all_value"  id="update_all_value"  value="0" />
  
	{foreach from=$arrData item=ts}
	  {assign var="item_number" value=$item_number+1}
	  {assign var="ts_id" value=$ts.testsuite.id}
	  {assign var="div_id" value=div_$ts_id}
	  
	
	<div id="{$div_id}"  style="margin:0px 0px 0px {$ts.level}0px;">
	    <h3>
      {if $item_number ==1}
	    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" border="0" 
	               alt="{$labels.check_uncheck_all}" 
                 title="{$labels.check_uncheck_all}" 
                 onclick="cs_all_checkbox_in_div('updateTcForm','{$update_cb}','update_all_value');">
      {/if}
      {$ts.testsuite.name|escape} 
	    </h3> 
   
     {* used as memory for the check/uncheck all checkbox javascript logic *}
     <input type="hidden" name="update_value_{$ts_id}"  id="update_value_{$ts_id}"  value="0" />
            
     {* ------------------------------------------------------------------------- *}      
     {if ($full_control && $ts.testcase_qty gt 0) || $ts.linked_testcase_qty gt 0 }
        
        <table cellspacing="0" style="font-size:small;" width="100%">
          <tr style="background-color:blue;font-weight:bold;color:white">

			     <td width="5" align="center">
              {if $full_control || 1==1}
			         <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			              onclick='cs_all_checkbox_in_div("{$div_id}","{$update_cb}","update_value_{$ts_id}");'
                    title="{$labelscheck_uncheck_all_checkboxes}" />
    			    {else}
    			     &nbsp;
		    	    {/if}
			     </td>
			     
			     <td class="tcase_id_cell">{$labels.th_id}</td> 
			     <td>{lang_get s='th_test_case'}</td>
			     <td>{lang_get s='version'}</td>
			     <td>{$labels.update_to_version}</td>
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
            
              {assign var="draw_update_inputs" value="0"}
              {if $tcase.executed == 'no' && $is_active==1} 
                {assign var="draw_update_inputs" value="1"}
              {/if}    
              
   				    {if $full_control || $tcase.linked_version_id ne 0 }
    			    <tr {if $tcase.linked_version_id ne 0} 
    			        style="{$smarty.const.TL_STYLE_FOR_ADDED_TC}" {/if}>
    			      <td width="20">
    				
    				    {if $full_control}
      				    {if $is_active eq 0 || $tcase.linked_version_id ne 0 }
      				       &nbsp;&nbsp;
      				    {else}
      				      <input type='checkbox' 
      				             name='{$update_cb}[{$tcase.id}]' 
      				             id='{$update_cb}{$tcase.id}' 
      				             value='{$tcase.id}'> 
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

                <td>
                  {if $draw_update_inputs } 
                    <select name="new_tcversion_for_tcid[{$tcase.id}]"
         				       {html_options options=$tcase.tcversions disabled=disabled}
         				    </select>
                  {/if}
                </td>
        
                {* ------------------------------------------------------------------------- *}      
                {if $ts.linked_testcase_qty gt 0 }
          				<td>&nbsp;</td>
          				<td>
          				   {if $draw_update_inputs && $tcase.linked_version_id != 0} 
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
      <br /><input type='submit' name='do_action' style="padding-right: 20px;"
         {if $full_control}
 	      	   value='{$labels.btn_update_testplan_tcversions}'
         {else}
 	      	   value='{$labels.btn_update_testplan_tcversions}'
		     {/if}
         />
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
