{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planAddTC_m1.tpl,v 1.2 2006/03/20 18:02:11 franciscom Exp $
Purpose: smarty template - generate a list of TC for adding to Test Plan 

20060319 - franciscom
*}

{include file="inc_head.tpl"}
{include file="inc_jsCheckboxes.tpl"}

<body>

<h1>{lang_get s='title_add_test_to_plan'} '{$testPlanName|escape}'</h1>


{if $has_tc }

<form name='addTcForm' method='post'>
<div style="padding-right: 20px; float: right;">
	<input type='submit' name='link_tc' value='{lang_get s='btn_add_selected_tc'}' />
</div>

{include file="inc_update.tpl" result=$sqlResult}
{if $key ne ''}
	<div style="margin-left: 20px; font-size: smaller;"><p>{lang_get s='note_keyword_filter'} '{$key|escape}'</p></div>
{/if}


<div class="workBack">
	{section name=tsuite_idx loop=$arrData}
	<div id="div_{$arrData[tsuite_idx].testsuite.id}">
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

      <table>
      {foreach from=$arrData[tsuite_idx].testcases item=tcase }
				<tr {if $tcase.linked_version_id ne 0} style="background-color:yellow" {/if}>
				<td>
				<input type='checkbox' name='achecked_tc[{$tcase.id}]' value='{$tcase.id}' 
				       {if $tcase.linked_version_id ne 0} checked disabled{/if}	/>
      	<input type='hidden' name='a_tcid[{$tcase.id}]' value='{$tcase.id}' />
      	{$tcase.name|escape}
        </td>
        <td>
				&nbsp;&nbsp;{lang_get s='version'}
				<select name="tcversion_for_tcid[{$tcase.id}]"
				        {if $tcase.linked_version_id ne 0}disabled{/if} >
				{html_options options=$tcase.tcversions selected=$tcase.linked_version_id}
				</select>
        </td>
        <tr>
			{/foreach}
      </table>
      
      </p>


	  {*
		{section name=row loop=$arrData[idx].tc}
			{if $arrData[idx].tc[row].added == '0'}
				<input type='checkbox' name='C{$arrData[idx].tc[row].id}' />
      			<input type='hidden' name='H{$arrData[idx].tc[row].id}' 
      				value='{$arrData[idx].tc[row].id}' />
			{else}
      			<img src='icons/checkmark.gif' height='12px' width='12px' 
      				style='margin-left:5px;' />
      		{/if}
			<b>{$arrData[idx].tc[row].id}</b>: 
			<a href='lib/testcases/archiveData.php?edit=testcase&data={$arrData[idx].tc[row].id}'>
				{$arrData[idx].tc[row].name|escape}</a><br/>
		{/section}
      	</p>
    *}
    </div>
	{/section}

</div>
</form>

{else}
 <h2>{lang_get s='no_testcase_available'}</h2>
{/if}

</body>
</html>