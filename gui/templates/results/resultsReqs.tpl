{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsReqs.tpl,v 1.19 2010/08/19 16:21:21 asimon83 Exp $
Purpose: report REQ coverage 
Author : Martin Havlat 

rev:
    20100819 - asimon - BUGIDs 3261, 3439, 3488, 3569, 3299, 3259, 3687: 
                        complete redesign/rewrite of requirement based report 
    20100311 - franciscom - BUGID 3267
    20090402 - amitkhullar - added TC version while displaying the Req -> TC Mapping 
    20090305 - franciscom - added test case path on displayy
    20090114 - franciscom - BUGID 1977
    20090111 - franciscom - BUGID 1967 + Refactoring
*}
{lang_get var='labels'
          s='title_result_req_testplan, show_only_finished_reqs, generated_by_TestLink_on'}

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

{if $gui->warning_msg == ''}
	
	<p><form method="post">
	<input type="checkbox" name="show_only_finished" value="show_only_finished"
	       {if $gui->show_only_finished} checked="checked" {/if}
	       onchange="this.form.submit()" /> {$labels.show_only_finished_reqs}
	<input type="hidden"
	       name="show_only_finished_hidden"
	       value="{$gui->show_only_finished}" />
	</form></p><br/>
	
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=table_$idx}
   		{$matrix->renderBodySection($tableID)}
	{/foreach}
{else}
	<div class="user_feedback">
    {$gui->warning_msg}
    </div>
{/if}    

</div>

{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}

</body>

</html>