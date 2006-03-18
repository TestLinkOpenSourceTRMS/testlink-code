{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planAddTC_m1.tpl,v 1.1 2006/03/18 10:12:11 franciscom Exp $ *}
{* Purpose: smarty template - generate a list of TC for adding to Test Plan *}
{include file="inc_head.tpl"}
{include file="inc_jsCheckboxes.tpl"}

<body>

<h1>{lang_get s='title_add_test_to_plan'} '{$testPlanName|escape}'</h1>

<form name='addTcForm' method='post'>
<div style="padding-right: 20px; float: right;">
	<input type='submit' name='addTC' value='{lang_get s='btn_add_selected_tc'}' />
</div>

{include file="inc_update.tpl" result=$sqlResult}
{if $key ne ''}
	<div style="margin-left: 20px; font-size: smaller;"><p>{lang_get s='note_keyword_filter'} '{$key|escape}'</p></div>
{/if}

<div class="workBack">
	{section name=tsuite_idx loop=$arrData}
	<div id="div_{$arrData[tsuite_idx].main.id}">
	    <h3>{$arrData[tsuite_idx].main.name|escape}</h3>

    	{if $arrData[tsuite_idx].write_buttons eq 'yes'}
      	<p>
      	<input type='button' name='{$arrData[tsuite_idx].main.name|escape}_check' 
      	       onclick='javascript: box("div_{$arrData[tsuite_idx].main.id}", true)' 
      	       value='{lang_get s='btn_check'}' />
      	<input type='button' name='{$arrData[tsuite_idx].main.name|escape}_uncheck' 
      	       onclick='javascript: box("div_{$arrData[tsuite_idx].main.id}", false)' 
      	       value='{lang_get s='btn_uncheck'}' />
  			<b> {lang_get s='check_uncheck_tc'}</b>
  			</p>
  			<p>
      {/if}

      {*  
			{section name=tcase_idx loop=$arrData[tsuite_idx].testcases}
			  TC={$tcase_idx}
				<input type='checkbox' name='C{$arrData[tsuite_idx].testcases[tcase_idx].id}' />
      	<input type='hidden' name='H{$arrData[tsuite_idx].testcases[tcase_idx].id}' 
      				 value='{$arrData[tsuite_idx].testcases[tcase_idx].id}' />
      	{$arrData[tsuite_idx].testcases[tcase_idx].name|escape}
      	
				<select name="combo_tcversion">
				{html_options options=$arrData[tsuite_idx].testcases[tcase_idx].tcversions}
				</select>

      	<br>			 
			{/section}
      *}
      
      {foreach from=$arrData[tsuite_idx].testcases item=tcase }
				<input type='checkbox' name='C{$tcase.id}' />
      	<input type='hidden' name='H{$tcase.id}' 
      				 value='{$tcase.id}' />
      	{$tcase.name|escape}
				&nbsp;&nbsp;{lang_get s='version'}
				<select name="combo_tcversion">
				{html_options options=$tcase.tcversions}
				</select>
      	<br>			 
			{/foreach}
      
      
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

</body>
</html>