{* 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: resultsByTesterPerBuild.tpl,v 1.3 2010/10/19 13:48:38 asimon83 Exp $
 *
 * Lists results and progress by tester per build in a grouping ExtJS table.
 * 
 * revisions:
 * 20101019 - asimon - BUGID 3911: show warning message instead of table if table is empty
 * 20100731 - asimon - initial commit
 *
 *}
 
{lang_get var="labels"
         s="generated_by_TestLink_on,hlp_results_by_tester_per_build_table"}

{include file="inc_head.tpl" openHead="yes"}

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
	{assign var=tableID value=table_$idx}
	{if $smarty.foreach.initializer.first}
		{$matrix->renderCommonGlobals()}
		{if $matrix instanceof tlExtTable}
			{include file="inc_ext_js.tpl" bResetEXTCss=1}
			{include file="inc_ext_table.tpl"}
		{/if}
	{/if}
	{$matrix->renderHeadSection($tableID)}
{/foreach}

</head>
<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack" style="overflow-y: auto;">

{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}

{* BUGID 3911: show warning message instead of table if table is empty *}
{if $gui->warning_message == ''}
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=table_$idx}
   		{$matrix->renderBodySection($tableID)}
	{/foreach}
	
	<br />
		<p class="italic">{$labels.hlp_results_by_tester_per_build_table}</p>
	<br />

	{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
{else}
	<div class="user_feedback">
    {$gui->warning_message}
    </div>
{/if}

</div>
</body>
</html>