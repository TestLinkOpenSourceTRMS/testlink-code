{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planRemoveTC_m1.tpl,v 1.6 2007/02/26 08:01:44 franciscom Exp $
generate the list of TC that can be removed from a Test Plan 

*}

{include file="inc_head.tpl"}
{include file="inc_jsCheckboxes.tpl"}

<body>

<h1>{lang_get s='title_remove_test_from_plan'} {$testPlanName|escape}</h1>

{if $user_feedback neq ''}
 <div class="workBack">
  {include file="inc_update.tpl" user_feedback=$user_feedback}
 </div>
{/if}


{if $has_tc && $user_feedback eq ''}
  <form name='removeTcForm' method='post'>
  <div style="padding-right: 20px; float: right;">
  	<input type='submit' name='remove_tc' value='{lang_get s='btn_remove_selected_tc'}' />
  </div>
  
  {include file="inc_update.tpl" result=$sqlResult refresh="yes"}
  {if $key ne ''}
  	<div style="margin-left: 20px; font-size: smaller;"><p>{lang_get s='note_keyword_filter'} '{$key|escape}'</p></div>
  {/if}
  
  
  <div class="workBack">
  	{section name=tsuite_idx loop=$arrData}
  	<div id="div_{$arrData[tsuite_idx].testsuite.id}" style="margin:0px 0px 0px {$arrData[tsuite_idx].level}0px;">
  	    <h3>{$arrData[tsuite_idx].testsuite.name|escape}</h3>
  
      	{if $arrData[tsuite_idx].write_buttons eq 'yes'}
        	<p>
        	<input type='button' name='{$arrData[tsuite_idx].testsuite.name|escape}_check' 
        	       onclick='javascript: box("div_{$arrData[tsuite_idx].testsuite.id}", true)' 
        	       value='{lang_get s='btn_check'}' />
        	<input type='button' name='{$arrData[tsuite_idx].testsuite.name|escape}_uncheck' 
        	       onclick='javascript: box("div_{$arrData[tsuite_idx].testsuite.id}", false)' 
        	       value='{lang_get s='btn_uncheck'}' />
    			<b> {lang_get s='check_uncheck_tc'}</b>
    			</p>
    			<p>
        {/if}
   
        {if $arrData[tsuite_idx].linked_testcase_qty gt 0 }
            <table cellspacing="0" style="font-size:small;" width="100%">
              <tr style="background-color:blue;font-weight:bold;color:white;">
       		      <td class="checkbox_cell">&nbsp;</td>
  			        <td class="tcase_id_cell">{lang_get s='th_id'}</td> 
  				      <td>{lang_get s='th_test_case'}</td>
  				      <td>{lang_get s='version'}</td>
       		      <td>&nbsp;</td>
              </tr>   
  
            {foreach from=$arrData[tsuite_idx].testcases item=tcase }
            	{if $tcase.linked_version_id ne 0}
                <tr>
              	<td>
        				<input type='checkbox' name='achecked_tc[{$tcase.id}]' value='{$tcase.linked_version_id}'>
              	</td>
     			      <td>
      				    {$tcase.id}
     			      </td>
              	<td>
              	<input type='hidden' name='a_tcid[{$tcase.id}]' value='{$tcase.linked_version_id}'>
              	{$tcase.name|escape}
                </td>
                <td>
        				<select name="tcversion_for_tcid[{$tcase.id}]" disabled>
        				{html_options options=$tcase.tcversions selected=$tcase.linked_version_id}
        				</select>
                </td>
                <td>
                  {if $tcase.executed eq 'yes'}
                    {lang_get s='has_been_executed'}
                  {else}
                  &nbsp;
                  {/if}    
                </td>
                </tr>
      	      {/if}		
      			{/foreach}
            </table>
        {/if}
      </div>
  	{/section}
  
  </div>
  </form>
{/if}

{if $refreshTree}
   {include file="inc_refreshTree.tpl"}
{/if}

</body>
</html>