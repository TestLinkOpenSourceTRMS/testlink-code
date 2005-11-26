{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planAddTC.tpl,v 1.3 2005/11/26 19:58:21 schlundus Exp $ *}
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

{* include file="inc_update.tpl" result=$sqlResult item="Test Plan" action="create" *}

<div class="workBack">

	{if $nameCOM ne ''}
	<div id="COM">
		<h2>{$nameCOM|escape}</h2>
		<p>
			<input type='button' name='{$nameCOM}_check' value='{lang_get s='btn_check'}' onclick='javascript: box("COM", true);' />
			<input type='button' name='{$nameCOM}_uncheck' onclick='javascript: box("COM", false);' value='{lang_get s='btn_uncheck'}' />
			<b> {lang_get s='check_uncheck_tc_in_categories'}</b>
		</p>
	{/if}

	{section name=number loop=$arrData}
	<hr />
	<div id="CAT_{$arrData[number].id}">
	    <h3>{$smarty.section.number.rownum}. {$arrData[number].name|escape}</h3>
    	<p>
    	<input type='button' name='{$arrData[number].name|escape}_check' onclick='javascript: box("CAT_{$arrData[number].id}", true)' value='{lang_get s='btn_check'}' />
    	<input type='button' name='{$arrData[number].name|escape}_uncheck' onclick='javascript: box("CAT_{$arrData[number].id}", false)' value='{lang_get s='btn_uncheck'}' />
			<b> {lang_get s='check_uncheck_tc'}</b>
		</p>
		<p>
		{section name=row loop=$arrData[number].tc}
			{if $arrData[number].tc[row].added == '0'}
				<input type='checkbox' name='C{$arrData[number].tc[row].id}' />
      			<input type='hidden' name='H{$arrData[number].tc[row].id}' 
      				value='{$arrData[number].tc[row].id}' />
			{else}
      			<img src='icons/checkmark.gif' height='12px' width='12px' 
      				style='margin-left:5px;' />
      		{/if}
			<b>{$arrData[number].tc[row].id}</b>: 
			<a href='lib/testcases/archiveData.php?edit=testcase&data={$arrData[number].tc[row].id}'>
				{$arrData[number].tc[row].name|escape}</a><br/>
		{/section}
      	</p>
    </div>
	{/section}

	{if $nameCOM ne ''}
	</div>
	{/if}
</div>
</form>

</body>
</html>