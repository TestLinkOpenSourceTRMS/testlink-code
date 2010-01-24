{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsBugs.tpl,v 1.4 2010/01/24 13:10:57 erikeloff Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
   20070826 - franciscom - localization
*}
{lang_get var='labels'
          s='title,date,printed_by,bugs_open,
		         title_test_suite_name,title_test_case_title,
		         title_test_case_bugs,
             generated_by_TestLink_on,bugs_resolved,bugs_total,tcs_with_bugs'}

{include file="inc_head.tpl"}

<body>

{if $printDate == ''}
<h1 class="title">{$title|escape}</h1>

{else}{* print data to excel *}
<table style="font-size: larger;font-weight: bold;">
	<tr><td>{$labels.title}</td><td>{$title|escape}</td><tr>
	<tr><td>{$labels.date}</td><td>{$printDate|escape}</td><tr>
	<tr><td>{$labels.printed_by}</td><td>{$user->getDisplayName()|escape}</td><tr>
</table>
{/if}

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
     <tr>
         <th>{$labels.bugs_open}</th>
         <th>{$labels.bugs_resolved}</th>
         <th>{$labels.bugs_total}</th>
         <th>{$labels.tcs_with_bugs}</th>
     </tr>
     
     <tr>
         <td>{$totalOpenBugs}</td>
         <td>{$totalResolvedBugs}</td>
         <td>{$totalBugs}</td>
         <td>{$totalCasesWithBugs}</td>
     </tr>
</table>

<table class="simple" style="width: 100%; margin-left: 0px;">
	<tr>
		<th>{$labels.title_test_suite_name}</th>
		<th>{$labels.title_test_case_title}</th>
		<th>{$labels.title_test_case_bugs}</th>	
	</tr>
{section name=Row loop=$arrData}
	<tr>
	{section name=Item loop=$arrData[Row]}
		<td>{$arrData[Row][Item]}</td>
	{/section}
	</tr>
{/section}
</table>

{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div>

</body>
</html>