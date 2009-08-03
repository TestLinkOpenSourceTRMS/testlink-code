{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsTC.tpl,v 1.7 2009/08/03 08:14:55 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
   20070919 - franciscom - BUGID
   20051204 - mht - removed obsolete print button
*}

{lang_get var="labels"
          s="title,date,printed_by,title_test_suite_name,
             title_test_case_title,version,generated_by_TestLink_on, priority"}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{$table->renderHeadSection()}
</head>
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

{$table->renderBodySection()}

{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
</div>

</body>
</html>
