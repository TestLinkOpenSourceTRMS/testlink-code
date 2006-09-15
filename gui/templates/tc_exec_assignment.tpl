{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tc_exec_assignment.tpl,v 1.1 2006/09/15 13:22:10 franciscom Exp $
generate the list of TC that can be removed from a Test Plan 

20060319 - franciscom
*}

{include file="inc_head.tpl"}
{include file="inc_jsCheckboxes.tpl"}

<body>

<h1>{lang_get s='title_tc_exec_assignment'}  {$testPlanName|escape}</h1>


{if $has_tc }

<form name='removeTcForm' method='post'>
<div style="padding-right: 20px; float: right;">
	<input type='submit' name='assign_tc' value='{lang_get s='btn_update_selected_tc'}' />
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

      {if $arrData[tsuite_idx].testcase_qty gt 0 }
          <table cellspacing="0" style="font-size:small;" width="100%">
           <tr style="background-color:blue;font-weight:bold;">
          	<td>&nbsp;</td>
          	<td align="center">&nbsp;&nbsp;{lang_get s='version'}</td>
          	<td align="center">&nbsp;&nbsp;{lang_get s='user'}</td>
          </tr>
          {foreach from=$arrData[tsuite_idx].testcases item=tcase }
          	{if $tcase.linked_version_id ne 0}

          	  <tr>
            	  <td>
      				  <input type='checkbox' name='achecked_tc[{$tcase.id}]' value='{$tcase.linked_version_id}'>
            	  <input type='hidden' name='a_tcid[{$tcase.id}]' value='{$tcase.linked_version_id}'>
            	  {$tcase.name|escape}
                </td>
                <td align="center">
        				{$tcase.tcversions[$tcase.linked_version_id]}
                </td>
                <td align="center">
      		  		<select name="tester_for_tcid[{$tcase.id}]">
      			  	{html_options options=$users selected=$tcase.user_id}
      				  </select>
      				  <input type="hidden" name="has_prev_assignment[{$tcase.id}]" value="{$tcase.user_id}"}>
      				  <input type="hidden" name="feature_id[{$tcase.id}]" value="{$tcase.feature_id}"}>
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

{else}
 <h2>{lang_get s='no_testcase_available'}</h2>
{/if}

</body>
</html>