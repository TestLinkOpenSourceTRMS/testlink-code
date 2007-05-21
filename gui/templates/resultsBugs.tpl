{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsBugs.tpl,v 1.12 2007/05/21 06:39:38 franciscom Exp $ *}
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
<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
	<tr>
		<th>{lang_get s='title_test_suite_name'}</th>
		<th>{lang_get s='title_test_case_title'}</th>
		<th>{lang_get s='title_test_case_bugs'}</th>	
		{* <th>original date</th> *}
	
	</tr>
{section name=Row loop=$arrData}
	<tr>
	{section name=Item loop=$arrData[Row]}
		<td>{$arrData[Row][Item]}</td>
	{/section}
	</tr>
{/section}
</table>

{lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$smarty.const.TL_TIMESTAMP_FORMAT}
</div>

</body>
</html>