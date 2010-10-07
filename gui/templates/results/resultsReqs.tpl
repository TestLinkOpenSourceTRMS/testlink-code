{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsReqs.tpl,v 1.25 2010/10/07 13:27:59 asimon83 Exp $
Purpose: report REQ coverage 
Author : Martin Havlat 

rev:
    20101007 - asimon - BUGID 3856: Requirement based report should regard platforms
    20100823 - asimon - replaced "onchange" in form by "onclick" to get
                        it working in IE too
    20100819 - asimon - BUGIDs 3261, 3439, 3488, 3569, 3299, 3259, 3687: 
                        complete redesign/rewrite of requirement based report 
    20100311 - franciscom - BUGID 3267
    20090402 - amitkhullar - added TC version while displaying the Req -> TC Mapping 
    20090305 - franciscom - added test case path on displayy
    20090114 - franciscom - BUGID 1977
    20090111 - franciscom - BUGID 1967 + Refactoring
*}
{lang_get var='labels'
          s='title_result_req_testplan, show_only_finished_reqs, 
          generated_by_TestLink_on, info_resultsReqs, platform'}

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

<p><form method="post">
{if $gui->platforms}
{* BUGID 3856 *}
{$labels.platform} <select name="platform" onchange="this.form.submit()">
	{html_options options=$gui->platforms
	              selected=$gui->selected_platform}
</select><br/>
{/if}
<input type="checkbox" name="show_only_finished" value="show_only_finished"
       {if $gui->show_only_finished} checked="checked" {/if}
       onclick="this.form.submit();" /> {$labels.show_only_finished_reqs}
<input type="hidden"
       name="show_only_finished_hidden"
       value="{$gui->show_only_finished}" />
</form></p><br/>

{if $gui->warning_msg == ''}
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=table_$idx}
   		{$matrix->renderBodySection($tableID)}
	{/foreach}
	
	<br />
		<p class="italic">{$labels.info_resultsReqs}</p>
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