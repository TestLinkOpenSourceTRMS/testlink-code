{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

Absolute Latest Execution Results on Test Plan
Build & Platform ARE IGNORED

@filesource	resultsTC.tpl
*}

{lang_get var="labels"
          s="title,date,printed_by,title_test_suite_name,platform,builds,
             title_test_case_title,version,generated_by_TestLink_on, priority,
             info_resultsTCAbsoluteLatest_report,
             elapsed_seconds,export_as_spreadsheet,
             send_spreadsheet_by_email"}

{include file="inc_head.tpl" openHead="yes"}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {$tableID=$matrix->tableID}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_js.tpl" bResetEXTCss=1}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

</head>
<body>

<!-- To avoid refresh when sending mail --> 
<iframe name="avoidPageRefreshWhenSendingMail" style="display:none;"></iframe>


{if $gui->printDate == ''}
{* +++++++++++++++++++++++++++ *}
{* Form to launch Excel Export *}
{*  target="avoidPageRefreshWhenSendingMail" *}
<form name="resultsTCAbsoluteLatest" 
      id="resultsTCAbsoluteLatest" METHOD="POST"
      
      action="lib/results/resultsTCAbsoluteLatest.php?format=3&doAction=result&tplan_id={$gui->tplan_id}&tproject_id={$gui->tproject_id}">

  <input type="hidden" 
         name="platform_id" id="platform_id" 
         value="{$gui->platform_id}">


  {if $gui->apikey != ''}
  <input type="hidden" name="apikey" id="apikey" value="{$gui->apikey}">
  {/if}

<h1 class="title">{$gui->title|escape}
</h1>

  &nbsp;&nbsp;
  <input type="image" name="exportSpreadSheet" id="exportSpreadSheet" 
         src="{$tlImages.export_excel}" title="{$labels.export_as_spreadsheet}">

  <input type="image" 
         name="sendSpreadSheetByMail" id="sendSpreadSheetByMail" 
         src="{$tlImages.email}" title="{$labels.send_spreadsheet_by_email}">
</form>

{else}{* print data to excel *}
<table style="font-size: larger;font-weight: bold;">
	<tr><td>{$labels.title}</td><td>{$gui->title|escape}</td><tr>
	<tr><td>{$labels.date}</td><td>{$gui->printDate|escape}</td><tr>
	<tr><td>{$labels.printed_by}</td><td>{$user|escape}</td><tr>
</table>
{/if}

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name 
         arg_tplan_name=$gui->tplan_name 
         arg_build_set=''}	

<br />
<p class="italic">{$labels.info_resultsTCAbsoluteLatest_report}</p>
<br />

{foreach from=$gui->tableSet key=idx item=matrix}
  {$tableID="table_$idx"}
  {if $idx != 0}
  <h2>{$labels.platform}: {$gui->platforms[$idx]|escape}</h2>
  {/if}
  {$matrix->renderBodySection()}
{/foreach}

<br />
<p class="italic">{$labels.info_resultsTCAbsoluteLatest_report}</p>
<br />

{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}

{* 
<p>{$labels.elapsed_seconds} {$gui->elapsed_time}</p>
*}
</div>


</body>
</html>