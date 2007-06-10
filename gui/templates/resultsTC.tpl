{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsTC.tpl,v 1.9 2007/06/10 20:02:47 kevinlevy Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
20051204 - mht - removed obsolete print button
*}
{include file="inc_head.tpl"}

<body>

{if $printDate == ''}
<h1>{$title|escape}</h1>

{else}{* print data to excel *}
<table style="font-size: larger;font-weight: bold;">
	<tr><td>{lang_get s='title'}</td><td>{$title|escape}</td><tr>
	<tr><td>{lang_get s='date'}</td><td>{$printDate|escape}</td><tr>
	<tr><td>{lang_get s='printed_by'}</td><td>{$user|escape}</td><tr>
</table>
{/if}

<div class="workBack">
{* {lang_get s="testproject"} {$tproject_name}</font><br>
{lang_get s="testplan"} {$tplan_name|escape}</font><br> *}

{* comments out above 2 lines,improve readability for results inspection. 2007-05-31 *jacky *} 
{lang_get s="testproject"}{':'} <font style="color:black; font-weight:bold; text-decoration: underline;">{$tproject_name}</font><br>
{lang_get s="testplan"}{':'} <font style="color:black; font-weight:bold; text-decoration:underline;">{$tplan_name|escape}</font><br>

<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
	<tr>
		<th>{lang_get s='title_test_suite_name'}</th>
		<th>{lang_get s='title_test_case_title'}</th>
		
		{foreach key=row item=buildid from=$arrBuilds}
			<th>{$arrBuilds[$row].name|escape}</th>
		{/foreach}
	</tr>
{section name=Row loop=$arrData}
	<tr>
	{section name=Item loop=$arrData[Row]}

	{* add different font styles for tc status for improve readability. 2007-05-31 *jacky *}
		{if $arrData[Row][Item] == 'Passed'} 
		<td style="color: green; font-weight:bold">{$arrData[Row][Item]}</td>

		{elseif $arrData[Row][Item] == 'Failed'} 
		<td style="color: red; font-weight:bold">{$arrData[Row][Item]}</td>

		{elseif $arrData[Row][Item] == 'Not Run'}
		<td style="color: gray;">{$arrData[Row][Item]}</td> 

		{elseif $arrData[Row][Item] == 'Blocked'}
		<td style="color:blue;">{$arrData[Row][Item]}</td>
		{else} 
		<td>{$arrData[Row][Item]}</td>
		{/if}
	{/section}
	</tr>
{/section}
</table>

{lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$smarty.const.TL_TIMESTAMP_FORMAT}
</div>

</body>
</html>
