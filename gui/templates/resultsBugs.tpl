{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsBugs.tpl,v 1.15 2007/08/27 06:37:31 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
   20070826 - franciscom - localization
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
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
     <tr>
         <th>{lang_get s='bugs_open'}</th>
         <th>{lang_get s='bugs_resolved'}</th>
         <th>{lang_get s='bugs_total'}</th>
         <th>{lang_get s='tcs_with_bugs'}</th>
     </tr>
     
     <tr>
         <td>{$totalOpenBugs}</td>
         <td>{$totalResolvedBugs}</td>
         <td>{$totalBugs}</td>
         <td>{$totalCasesWithBugs}</td>
     </tr>
</table>

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

{lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div>

</body>
</html>