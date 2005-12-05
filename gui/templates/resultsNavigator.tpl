{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsNavigator.tpl,v 1.3 2005/12/05 01:30:59 havlat Exp $ *}
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
	<a href="lib/results/{$arrDataB[Row].href}?build={$selectedBuild}" target="workframe">{$arrDataB[Row].name}</a><br />
{/section}
</p>
<hr />
<p>
{section name=Row loop=$arrData}
	<a href="lib/results/{$arrData[Row].href}" target="workframe">{$arrData[Row].name}</a><br />
{/section}
</p>
<hr />
<p>
	<a href="lib/results/resultsSend.php" target="workframe">{lang_get s='send_results'}</a> {lang_get s='via_email'}
</p>
</div>

</body>
</html>