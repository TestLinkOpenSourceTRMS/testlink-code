{* TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource resultsBugs.tpl
Purpose: smarty template - show Test Results and Metrics
*}
{lang_get var='labels'
          s='title,date,printed_by,bugs_open,
             title_test_suite_name,title_test_case_title,
             title_test_case_bugs, info_bugs_per_tc_report,
             generated_by_TestLink_on,bugs_resolved,bugs_total,tcs_with_bugs'}

{include file="inc_head.tpl"}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {$tableID="$matrix->tableID"}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_js.tpl" bResetEXTCss=1}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

<body>

{if $gui->printDate == ''}
<h1 class="title">{$gui->title|escape}</h1>

{else}{* print data to excel *}
<table style="font-size: larger;font-weight: bold;">
  <tr><td>{$labels.title}</td><td>{$gui->title|escape}</td><tr>
  <tr><td>{$labels.date}</td><td>{$gui->printDate|escape}</td><tr>
  <tr><td>{$labels.printed_by}</td><td>{$gui->user->getDisplayName()|escape}</td><tr>
</table>
{/if}

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name} 

{if $gui->hint != ''}
  <h1><center>{$gui->hint}</center></h1>
{/if}

{if $gui->warning_msg == ''}
  <table class="simple" style="text-align: center; margin-left: 0px;">
    <tr>
      <th>{$labels.bugs_open}</th>
      <th>{$labels.bugs_resolved}</th>
      <th>{$labels.bugs_total}</th>
      <th>{$labels.tcs_with_bugs}</th>
    </tr>
       
    <tr>
      <td>{$gui->totalOpenBugs}</td>
      <td>{$gui->totalResolvedBugs}</td>
      <td>{$gui->totalBugs}</td>
      <td>{$gui->totalCasesWithBugs}</td>
    </tr>
  </table>
  
  <br />
  
  {foreach from=$gui->tableSet key=idx item=matrix}
    {$tableID="table_$idx"}
      {$matrix->renderBodySection($tableID)}
  {/foreach}
  
  <br />
  <p class="italic">{$labels.info_bugs_per_tc_report}</p>
  <br />
  {$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
{else}
  <div class="user_feedback">
    {$gui->warning_msg}
    </div>
{/if}
</div>

</body>
</html>