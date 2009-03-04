{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsTC.tpl,v 1.5 2009/03/04 20:30:55 schlundus Exp $ *}
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
			<th>xx{$arrBuilds[$row].name|escape}xx</th>
		{/foreach}
	</tr>
{section name=Row loop=$arrData}
	<tr>
	{section name=Item loop=$arrData[Row]}
		{assign var=result value=$arrData[Row][Item]}
		{* add different font styles for tc status for improve readability. 2007-05-31 *jacky *}
		{if is_array($result)}
			{if $result[0] == 'p'} 
				<td style="color: green; font-weight:bold">
			{elseif $result[0] == 'f'} 
				<td style="color: red; font-weight:bold">
			{elseif $result[0] == 'n'}
				<td style="color: gray;">
			{elseif $result[0] == 'b'}
				<td style="color:blue;">
			{else}
				<td>
			{/if}
			{$result[1]|escape}</td>
		{else}
			<td>{$result|escape}</td>
		{/if}
	{/section}
	</tr>
{/section}
</table>

{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div>

</body>
</html>
