{* 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: reqOverview.tpl,v 1.8 2010/10/11 07:57:12 mx-julian Exp $
 *
 * Purpose: List requirements with (or without) Custom Fields Data in an ExtJS Table.
 * See BUGID 3227 for a more detailed description of this feature.
 * 
 * revisions:
 * 20100823 - asimon - replaced "onchange" in form by "onclick" to get
 *                     it working in IE too
 * 20100821 - asimon - replaced "show all versions" button by checkbox as requested per e-mail
 * 20100310 - asimon - refactoring
 * 20100309 - asimon - initial commit
 *
 *}

{lang_get var="labels" 
          s='testproject_has_no_reqspec, testproject_has_no_requirements, generated_by_TestLink_on,
             all_versions_displayed, latest_version_displayed, show_all_versions_btn, 
             dont_show_all_versions_btn, notes_req_overview, hlp_req_coverage_table'}

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


{*****************************************************************************
 * this code could be used later, when I have figured out how to use this tooltip in combination with table.
 * for the moment it doesn't work, so it's commented out.

{literal}
<script type="text/javascript">

function tTip(reqID)
{
	var fUrl = fRoot+'lib/ajax/gettestcasesummary.php?tcase_id=';
	new Ext.ToolTip({
		target: 'tooltip-'+reqID,
       width: 200,
        autoLoad: {url: fUrl+39+'&tcversion_id=1'}
	});
}

function showTT(e)
{
	alert(e);
}

Ext.onReady(function(){ 
	{/literal}
	{foreach from=$gui->reqIDs key=idx item=reqID}
		tTip({$reqID});
	{/foreach}  
	{literal}
});

</script>
{/literal}
*****************************************************************************}

</head>

<body>

<h1 class="title">{$gui->pageTitle|escape}</h1>

<div class="workBack" style="overflow-y: auto;">

{if $gui->warning_msg == ''}
	
	<p><form method="post">
	<input type="checkbox" name="all_versions" value="all_versions"
	       {if $gui->all_versions} checked="checked" {/if}
	       onclick="this.form.submit();" /> {$labels.show_all_versions_btn}
	<input type="hidden"
	       name="all_versions_hidden"
	       value="{$gui->all_versions}" />
	</form></p><br/>
	
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=table_$idx}
   		{$matrix->renderBodySection($tableID)}
	{/foreach}
	
	<br/>
	
	<p>{$labels.notes_req_overview}</p>
	<br/>
	<p>{$labels.hlp_req_coverage_table}</p>
	<br/><br/>
	<p>{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}</p>
{else}
	<div class="user_feedback">
    {$gui->warning_msg}
    </div>
{/if}

</div>

</body>

</html>
