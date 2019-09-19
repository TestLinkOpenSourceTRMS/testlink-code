{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: freeTestCases.tpl,v 1.5 2010/09/20 13:33:33 mx-julian Exp $

For a test project, list FREE test cases, i.e. not assigned to a test plan.
*}

{lang_get var="labels" 
          s='generated_by_TestLink_on, info_tc_not_assigned_to_any_tplan'}
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
<div class="workBack">
{*<div class="workBack" style="overflow-y: auto;">*}
 {include file="inc_result_tproject_tplan.tpl" 
          arg_tproject_name=$gui->tproject_name arg_tplan_name=''}	

{if $gui->warning_msg == ''}
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value="table_$idx"}
		{$matrix->renderBodySection($tableID)}
	{/foreach}
	<br />
	<p class="italic">{$labels.info_tc_not_assigned_to_any_tplan}</p>
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