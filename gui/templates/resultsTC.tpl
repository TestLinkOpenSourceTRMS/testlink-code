{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsTC.tpl,v 1.7 2007/02/23 01:00:36 kevinlevy Exp $ *}
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
? = {lang_get s='trep_not_run'}
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
		<td>{$arrData[Row][Item]}</td>
	{/section}
	</tr>
{/section}
</table>
</div>

</body>
</html>