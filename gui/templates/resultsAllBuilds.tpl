{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsAllBuilds.tpl,v 1.6 2007/05/21 06:39:38 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
20070114 - franciscom - used class to set colors
20051204 - mht - removed obsolete print button
*}
{include file="inc_head.tpl"}

<body>

<h1>{$title|escape}</h1>


<div class="workBack">
{lang_get s="testproject"} {$tproject_name}<br>
{lang_get s="testplan"} {$tplan_name|escape}<br>

<table class="simple" style="width: 90%; text-align: center;">
	<tr>
		<th style="width: 10%;">{lang_get s='th_build'}</th>
    <th>{lang_get s='th_tc_total'}</th>
		<th class="{$tcs_css.passed}">{lang_get s='test_status_passed'}</th>
    <th class="{$tcs_css.passed}">[%]</th>
		<th class="{$tcs_css.failed}">{lang_get s='test_status_failed'}</th>
    <th class="{$tcs_css.failed}">[%]</th>
		<th class="{$tcs_css.blocked}">{lang_get s='test_status_blocked'}</th>
    <th class="{$tcs_css.blocked}">[%]</th>
		<th>{lang_get s='test_status_not_run'}</th><th>[%]</th>
	</tr>
{section name=Row loop=$arrData}
	<tr>
	{section name=Item loop=$arrData[Row]}
		<td>{$arrData[Row][Item]|escape}</td>
	{/section}
	</tr>
{/section}
</table>

{lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$smarty.const.TL_TIMESTAMP_FORMAT}
</div>

</body>
</html>
