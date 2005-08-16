{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planRemoveTC.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - generate a TC list for removing from Test Plan *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_remove_tc_from_tp'} '{$testPlanName|escape}'</h1>

{if $resultString != ''}
	<p class="info">{$resultString}</p>
{/if}

<form name='removeTcForm' method='post' action='lib/plan/testSetRemove.php?data={$id}&level={$level}'>
<div style="padding-right: 20px; padding-top: 10px; padding-bottom: 10px; float: right;">
	<input type='submit' name='deleteTC' value='Remove Test Case(s)' style="margin-right:10px;" /> 
{if $level == 'component' || $level == 'category'}
	<input type='submit' name='delete{$level}' 
	       value="{lang_get s='btn_remove_entire'} {$level}">
{/if}
</div>

<div class="workBack" style="clear: both;">
	<table class="hidden" style="width: 80%;">
	{section name=number loop=$arrData}
    <tr>
	    <td><input type="hidden" name="tcid{$arrData[number].id}" value="{$arrData[number].id}">
	    {$arrData[number].container|escape}</td>
	    <td><span class="bold">{$arrData[number].name|escape}</span></td>
	    <td><input type="checkbox" name="delete{$arrData[number].id}">
	    <input type="hidden" name="break{$arrData[number].id}" value="break"></td>
   	</tr>
   	{sectionelse}
    <tr><td>{lang_get s='no_data_avail'}</td></tr>
	{/section}
	</table>
</div>
</form>

</body>
</html>