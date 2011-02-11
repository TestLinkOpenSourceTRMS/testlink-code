{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsReqs.tpl,v 1.25.2.1 2011/02/11 08:28:40 mx-julian Exp $
Purpose: report REQ coverage 
Author : Martin Havlat 

rev:
    20110207 - asimon - BUGID 4227 - Allow to choose status of requirements to be evaluated
    20110207 - Julian - BUGID 4228 - Add more requirement evaluation states
    20110207 - Julian - BUGID 4206 - Jump to latest execution for linked test cases
    20110207 - Julian - BUGID 4205 - Add Progress bars for a quick overview
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
          s='title_result_req_testplan, show_only_finished_reqs, caption_nav_settings,
          generated_by_TestLink_on, info_resultsReqs, platform, status, btn_apply,
          info_resultsReqsProgress, title_resultsReqsProgress, title_resultsReqs'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
	{assign var=tableID value=table_$idx}
	{if $smarty.foreach.initializer.first}
		{$matrix->renderCommonGlobals()}
		{if $matrix instanceof tlExtTable}
			{include file="inc_ext_table.tpl"}
		{/if}
	{/if}
	{$matrix->renderHeadSection($tableID)}
{/foreach}

{assign var=total_reqs value=$gui->total_reqs}

<script type="text/javascript">
Ext.onReady(function() {ldelim}
	{foreach key=key item=value from=$gui->summary}
	{assign var=label value=$value.label}
	{assign var=count value=$value.count}
	{* only show progress bar if at least 1 item exists for this status *}
	{if $count != 0}
	    new Ext.ProgressBar({ldelim}
	        text:'&nbsp;&nbsp;{$label}: {$count} of {$total_reqs}',
	        width:'400',
	        cls:'left-align',
	        renderTo:'{$key}',
	        value:'{$count/$total_reqs}'
	    {rdelim});
	{/if}
    {/foreach}
{rdelim});
</script>

</head>

<body>

<h1 class="title">{$gui->pageTitle|escape}</h1>

<div class="workBack" style="overflow-y: auto;">

<h2 class="title">{$labels.caption_nav_settings}</h2>
<br />
<p><form method="post">
<table>
	{if $gui->platforms}
	<tr>
		<td>
		{* BUGID 3856 *}
		{$labels.platform}
		</td>
		<td>
		<select name="platform" onchange="this.form.submit()">
		{html_options options=$gui->platforms
		              selected=$gui->selected_platform}
		</select>
		</td>
	</tr>
	{/if}
	<tr>
		<td>{$labels.status}</td>
		<td> <select id="states_to_show" 
	                         name="states_to_show[]"
	                         multiple="multiple"
	                         size="4" >
			{html_options options=$gui->states_to_show->items
			              selected=$gui->states_to_show->selected}
							</select>
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit"
			       name="send_states_to_show"
			       value="{$labels.btn_apply}" />       
		</td>
	</tr>
</table>
</form></p><br/>

{if $gui->warning_msg == ''}

	<h2>{$labels.title_resultsReqsProgress}</h2>
	<br />

	{foreach from=$gui->summary key=key item=metric}
		<div id="{$key}"></div>
	{/foreach}

	<br />
		<p class="italic">{$labels.info_resultsReqsProgress}</p>
	<br />
	
	<h2>{$labels.title_resultsReqs}</h2>
	<br />
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