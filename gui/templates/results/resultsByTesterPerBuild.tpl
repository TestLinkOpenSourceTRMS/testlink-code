{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource resultsByTesterPerBuild.tpl

Lists results and progress by tester per build in a grouping ExtJS table.
*}
 
{lang_get var="labels"
         s="generated_by_TestLink_on,hlp_results_by_tester_per_build_table,show_closed_builds_btn"}

{include file="inc_head.tpl" openHead="yes"}

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
	{assign var=tableID value="table_$idx"}
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

<br /><p>
<form method="post">
	<input type="checkbox" id="show_closed_builds" name="show_closed_builds" value="show_closed_builds"
		   {if $gui->show_closed_builds} checked="checked" {/if}
		   onclick="this.form.submit();" /> {$labels.show_closed_builds_btn}
	<input type="hidden" id="show_closed_builds_hidden" name="show_closed_builds_hidden" 
	       value="{$gui->show_closed_builds}" />
</form>
</p>
<br />

{if $gui->warning_message == ''}
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value="table_$idx"}
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