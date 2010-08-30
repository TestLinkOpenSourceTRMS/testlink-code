{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: testCasesWithoutTester.tpl,v 1.3 2010/08/30 14:41:25 mx-julian Exp $

Purpose: For a test plan, list test cases that has no tester assigned

rev: 
	20100830 - Julian - added information for users about report
	20081227 - franciscom - BUGID
*}

{lang_get var="labels" 
          s='no_uncovered_testcases,testproject_has_no_reqspec,
             testproject_has_no_requirements,generated_by_TestLink_on,
             testCasesWithoutTester_info'}
{include file="inc_head.tpl" openHead="yes"}
</head>
<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack" style="overflow-y: auto;">

 {include file="inc_result_tproject_tplan.tpl" 
          arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	

{if $gui->warning_msg == ''}
	{if $gui->tableSet}
		{$gui->tableSet[0]->renderCommonGlobals()}
		{if $gui->tableSet[0] instanceof tlExtTable}
			{include file="inc_ext_js.tpl" bResetEXTCss=1}
			{include file="inc_ext_table.tpl"}
		{/if}
		{$gui->tableSet[0]->renderHeadSection()}
		{$gui->tableSet[0]->renderBodySection()}
		
		<br />
		<p class="italic">{$labels.testCasesWithoutTester_info}</p>
		<br />
		
		{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
		
	{else}
		<h2>{$labels.no_testcases_without_tester}</h2>
	{/if}
{else}
    {$gui->warning_msg}
{/if}
</div>
</body>
</html>
