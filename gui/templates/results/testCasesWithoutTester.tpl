{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: testCasesWithoutTester.tpl,v 1.5 2010/11/06 14:07:57 mx-julian Exp $

Purpose: For a test plan, list test cases that has no tester assigned

*}

{lang_get var="labels" 
          s='no_uncovered_testcases,testproject_has_no_reqspec,
             testproject_has_no_requirements,generated_by_TestLink_on,
             testCasesWithoutTester_info'}
{include file="inc_head.tpl" openHead="yes"}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {assign var=tableID value=$matrix->tableID}
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
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack" style="overflow-y: auto;">

{include file="inc_result_tproject_tplan.tpl" 
          arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	

{if $gui->warning_msg == ''}
	{if $gui->tableSet}
		{foreach from=$gui->tableSet key=idx item=matrix}
			{assign var=tableID value="table_$idx"}
   			{$matrix->renderBodySection($tableID)}
		{/foreach}
		
		<br />
		<p class="italic">{$labels.testCasesWithoutTester_info}</p>
		<br />
		
		{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
		
	{else}
		<h2>{$labels.no_testcases_without_tester}</h2>
	{/if}
{else}
	<br />
    {$gui->warning_msg}
{/if}
</div>
</body>
</html>