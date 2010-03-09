{* 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * $Id: reqOverview.tpl,v 1.1 2010/03/09 09:45:02 asimon83 Exp $
 *
 * Purpose: List requirements with (or without) Custom Fields Data in an ExtJS Table.
 * See BUGID 3227 for a more detailed description of this feature.
 * 
 * revisions:
 * 20100309 - asimon - initial commit
 *
 *}

{lang_get var="labels" 
          s='testproject_has_no_reqspec, testproject_has_no_requirements, generated_by_TestLink_on,
             all_versions_displayed, latest_version_displayed, show_all_versions_btn, 
             dont_show_all_versions_btn'}

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
	{if $gui->all_versions}
		<input type="submit" name="latest_version" value="{$labels.dont_show_all_versions_btn}" id="dont_show_all_versions_btn" />
	{else}
		<input type="submit" name="all_versions" value="{$labels.show_all_versions_btn}" id="show_all_versions_btn" />
	{/if}
	</form></p><br/>
	
	{foreach from=$gui->tableSet key=idx item=matrix}
		{assign var=tableID value=table_$idx}
   		{$matrix->renderBodySection($tableID)}
	{/foreach}
	
	<br/>
	
	<p>{lang_get s='hlp_req_coverage_table'}</p>
{else}
    {$gui->warning_msg}
{/if}    

</div>

{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}

</body>

</html>
