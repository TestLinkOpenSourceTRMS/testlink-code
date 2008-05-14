{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsTC.tpl,v 1.3 2008/05/14 08:53:00 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
   20070919 - franciscom - BUGID
   20051204 - mht - removed obsolete print button
*}

{lang_get var="labels"
          s="title,date,printed_by,title_test_suite_name,
             title_test_case_title,version,generated_by_TestLink_on"}

{include file="inc_head.tpl"}

<body>

{if $printDate == ''}
<h1 class="title">{$title|escape}</h1>

{else}{* print data to excel *}
<table style="font-size: larger;font-weight: bold;">
	<tr><td>{$labels.title}</td><td>{$title|escape}</td><tr>
	<tr><td>{$labels.date}</td><td>{$printDate|escape}</td><tr>
	<tr><td>{$labels.printed_by}</td><td>{$user|escape}</td><tr>
</table>
{/if}

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

<table class="simple" style="width: 100%; margin-left: 0px;">
	<tr>
		<th>{$labels.title_test_suite_name}</th>
		<th>{$labels.title_test_case_title}</th>
		<th>{$labels.version}</th>

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

{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div>

</body>
</html>
