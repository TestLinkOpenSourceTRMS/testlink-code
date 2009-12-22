{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsTC.tpl,v 1.10 2009/12/22 15:14:15 erikeloff Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
   20070919 - franciscom - BUGID
   20051204 - mht - removed obsolete print button
*}

{lang_get var="labels"
          s="title,date,printed_by,title_test_suite_name,platform,
             title_test_case_title,version,generated_by_TestLink_on, priority"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}
{include file="inc_ext_table.tpl"}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {assign var=tableID value=table_$idx}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
  {/if}
  {$matrix->renderHeadSection($tableID)}
{/foreach}

</head>
<body>

{if $gui->printDate == ''}
<h1 class="title">{$gui->title|escape}</h1>

{else}{* print data to excel *}
<table style="font-size: larger;font-weight: bold;">
	<tr><td>{$labels.title}</td><td>{$gui->title|escape}</td><tr>
	<tr><td>{$labels.date}</td><td>{$gui->printDate|escape}</td><tr>
	<tr><td>{$labels.printed_by}</td><td>{$user|escape}</td><tr>
</table>
{/if}

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	

{foreach from=$gui->tableSet key=idx item=matrix}
  {assign var=tableID value=table_$idx}
  {if $idx != 0}
  <h2>{$labels.platform}: {$gui->platforms[$idx]|escape}</h2>
  {/if}
  {$matrix->renderBodySection($tableID)}
{/foreach}


{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div>

</body>
</html>
