{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsNavigator.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* I18N: 20050528 - fm *}
{include file="inc_head.tpl"}

<body>

<h1>{$title|escape}</h1>


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