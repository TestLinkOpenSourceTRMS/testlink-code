{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsNavigator.tpl,v 1.6 2007/01/13 23:44:13 kevinlevy Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
	20050528 - fm - I18N
	20051204 - mht - added print button
*}
{include file="inc_head.tpl" openHead="yes"}
{literal}<script type="text/javascript">
function reportPrint(){
	parent["workframe"].focus();
	parent["workframe"].print();
}
</script>{/literal}
</head>
<body>

<h1>{$title|escape}</h1>

<div class="groupBtn">
	<input type="button" name="print" value="{lang_get s='btn_print'}" 
	onclick="javascript: reportPrint();" style="margin-left:2px;" />
</div>

<div class="tree">
<div>
<form method="get">
	{lang_get s='title_active_build'}
	<select name="build" onchange="this.form.submit();">
		{html_options options=$arrBuilds selected=$selectedBuild}
	</select>
</form>
</div>

<p>
{section name=Row loop=$arrDataB}
	<a href="lib/results/{$arrDataB[Row].href}?build={$selectedBuild}&report_type={$selectedReportType}" target="workframe">{$arrDataB[Row].name}</a><br />
{/section}
<!--
$arrData = array(
	array('name' => lang_get('link_report_general_tp_metrics'), 'href' => 'resultsGeneral.php'), 
	array('name' => lang_get('link_report_overall_build'), 'href' => 'resultsAllBuilds.php'), 
    array('name' => lang_get('link_report_metrics_more_builds'), 'href' => 'resultsMoreBuilds.php'), 
	array('name' => lang_get('link_report_failed'), 'href' => 'resultsByStatus.php?type=f'),
	array('name' => lang_get('link_report_blocked_tcs'), 'href' => 'resultsByStatus.php?type=b'),
	array('name' => lang_get('link_report_test'), 'href' => 'resultsTC.php'),
	array('name' => lang_get('link_report_excel'), 'href' => 'resultsTC.php?format=excel'),
);
-->
</p>
<!--
<hr />
-->
<p>
{section name=Row loop=$arrData}
	<a href="lib/results/{$arrData[Row].href}{$selectedReportType}" target="workframe">{$arrData[Row].name}</a><br />
{/section}
</p>
<!--

<hr />
<p>
	<a href="lib/results/resultsSend.php" target="workframe">{lang_get s='send_results'}</a> {lang_get s='via_email'}
</p>
-->
</div>

<div>
<form method="get">
	<table>
	<tr><td>
	{lang_get s='title_report_type'}
	</td></tr>
	<tr><td>
	<select name="report_type" onchange="this.form.submit();">
		{html_options options=$arrReportTypes selected=$selectedReportType}
	</select>
	</td></tr>
	<tr><td>
	TO:
	</td></tr> 
	<tr><td>
	<input name='to' type='text' size='50' onchange="this.form.submit();"/>
	</td></tr>
	<tr><td>
	SUBJECT : 
	</td></tr>
	<tr><td>
	<input name='subject' type='text' size='50' value="" onchange="this.form.submit();"/>
	</td></tr>
	</table>
</form>
</div>

</body>
</html>