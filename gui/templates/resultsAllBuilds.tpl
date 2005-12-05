{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsAllBuilds.tpl,v 1.4 2005/12/05 01:46:52 havlat Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
20051204 - mht - removed obsolete print button
*}
{include file="inc_head.tpl"}

<body>

<h1>{$title|escape}</h1>


<div class="workBack">
<table class="simple" style="width: 90%; text-align: center;">
	<tr>
		<th style="width: 10%;">{lang_get s='th_build'}</th>
    <th>{lang_get s='th_tc_total'}</th>
		<th style="color: $tcs_color.passed;">{lang_get s='test_status_passed'}</th>
    <th style="color: $tcs_color.passed;">[%]</th>
		<th style="color: $tcs_color.failed;">{lang_get s='test_status_failed'}</th>
    <th style="color: $tcs_color.failed;">[%]</th>
		<th style="color: $tcs_color.blocked;">{lang_get s='test_status_blocked'}</th>
    <th style="color: $tcs_color.blocked;">[%]</th>
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
</div>

</body>
</html>
