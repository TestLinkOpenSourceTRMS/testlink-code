{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planAddTC_m1.tpl,v 1.11 2007/04/02 08:20:20 franciscom Exp $
Purpose: smarty template - generate a list of TC for adding to Test Plan 

20070402 - franciscom - BUGID 765
20070224 - franciscom - BUGID 600

20061105 - franciscom
added logic to manage active/inactive tcversions

*}

{include file="inc_head.tpl"}
{include file="inc_jsCheckboxes.tpl"}

<body>
<h1>{lang_get s='test_plan'}{$smarty.const.TITLE_SEP}{$testPlanName|escape}
</h1>


{if $has_tc }

<form name='addTcForm' method='post'>
   <h1>
    {if $has_linked_items eq 0} 
		   {lang_get s='title_add_test_to_plan'}
    {else}
		   {lang_get s='title_add_remove_test_to_plan'}
    {/if}     
    </h1>
    {include file="inc_update.tpl" result=$sqlResult}

  {if $key ne ''}
	  <div style="margin-left: 20px; font-size: smaller;">
		  <br />{lang_get s='note_keyword_filter'}{$key|escape}</p>
	  </div>
  {/if}
  
<div class="workBack" style="height: 380px; overflow-y: auto;">
     
  {* prefix for checkbox named , ADD and ReMove *}   
  {assign var="add_cb" value="achecked_tc"} 
  {assign var="rm_cb" value="remove_checked_tc"}
  
	{foreach from=$arrData item=ts}
	  {assign var="ts_id" value=$ts.testsuite.id}
	  {assign var="div_id" value=div_$ts_id}
	  
	
	<div id="{$div_id}"  style="margin:0px 0px 0px {$ts.level}0px;">
	    <h3>{$ts.testsuite.name|escape}</h3>
 
     {* used as memory for the check/uncheck all checkbox javascript logic *}
     <input type="hidden" name="add_value_{$ts_id}"  id="add_value_{$ts_id}"  
            value="0" />
     <input type="hidden" name="rm_value_{$ts_id}"  id="rm_value_{$ts_id}"  
            value="0" />
            
     {* ------------------------------------------------------------------------- *}      
     {if $ts.testcase_qty gt 0 }
        
        <table cellspacing="0" style="font-size:small;" width="100%">
          <tr style="background-color:blue;font-weight:bold;color:white">
			     <td width="5px" align="center">
			         <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			              onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}","add_value_{$ts_id}");'
                    title="{lang_get s='check_uncheck_all_checkboxes'}">
			     </td>
			     <td class="tcase_id_cell">{lang_get s='th_id'}</td> 
			     <td>{lang_get s='th_test_case'}</td>
			     <td>{lang_get s='version'}</td>
           {if $ts.linked_testcase_qty gt 0 }
            <td>&nbsp;</td>
				    <td>
				    <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif" 
                 onclick='cs_all_checkbox_in_div("{$div_id}","{$rm_cb}","rm_value_{$ts_id}");'
                 title="{lang_get s='check_uncheck_all_checkboxes'}">
				    {lang_get s='remove_tc'}</td>
           {/if}
          </tr>   
          
          {foreach from=$ts.testcases item=tcase}
            {if $tcase.tcversions_qty neq 0}  
            
    			    <tr {if $tcase.linked_version_id ne 0} 
    			        style="{$smarty.const.TL_STYLE_FOR_ADDED_TC}" {/if}>
    			      <td width="20px">
    				    {if $tcase.tcversions_qty eq 0 || $tcase.linked_version_id ne 0 }
    				       &nbsp;&nbsp;
    				    {else}
    				      <input type='checkbox' 
    				             name='{$add_cb}[{$tcase.id}]' 
    				             id='{$add_cb}{$tcase.id}' 
    				             value='{$tcase.id}'> 
    				    {/if}
    				    
    				    <input type='hidden' name='a_tcid[{$tcase.id}]' value='{$tcase.id}' />
    			      </td>
    			      
    			      <td>
    				    {$tcase.id}
    			      </td>
    			      <td>
    				    {$tcase.name|escape}
    			      </td>
    			      
                <td>
         				{if $tcase.tcversions_qty eq 0}
                  {lang_get s='inactive_testcase'}              
                {else}
         				  <select name="tcversion_for_tcid[{$tcase.id}]"
      			          {if $tcase.linked_version_id ne 0} disabled	{/if}>
         				      {html_options options=$tcase.tcversions selected=$tcase.linked_version_id}
         				  </select>
    				    
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
          				</td>
                {/if}
                {* ------------------------------------------------------------------------- *}      
 
              </tr>
            {/if}  {* $tcase.tcversions_qty *}
  	      {/foreach}
      
        </table>
        
        <br />
     {/if}  {* there are test cases to show ??? *}
    </div>

	{/foreach}
</div>

  <div class="workBack"    
      <br /><input type='submit' name='do_action' style="padding-right: 20px;"
		     {if $has_linked_items eq 0}
	      	   value='{lang_get s='btn_add_selected_tc'}'
		     {else}
             value='{lang_get s='btn_add_remove_selected_tc'}' 
		     {/if}
         />
   </div>

</form>

{else}
	<h2>{lang_get s='no_testcase_available'}</h2>
{/if}

</body>
</html>
