{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsNavigator.tpl,v 1.14 2007/06/28 06:11:10 kevinlevy Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Revisions:
   20070113 - franciscom - use of smarty config file
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
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>{$title|escape}</h1>

<div class="groupBtn">
	<input type="button" name="print" value="{lang_get s='btn_print'}" 
	onclick="javascript: reportPrint();" style="margin-left:2px;" />
</div>

<p>

<a href="lib/results/{$arrDataB[Row].href}?build={$selectedBuild}&amp;report_type={$selectedReportType|escape}" 
	   target="workframe">{$arrDataB[Row].name}</a><br />

{*
{assign var="my_build_name" value=$arrBuilds[$selectedBuild]}
{$my_build_name}
*}

{section name=Row loop=$arrData}
	<a href="lib/results/{$arrData[Row].href}{$selectedReportType}&amp;build={$selectedBuild}" target="workframe">{$arrData[Row].name}</a><br />
{/section}
</p>

</div>

<div>

<form method="get">
	<table>
		<tr><td>
			{lang_get s='title_active_build'}
		</td></tr>
		<tr><td>
			<select name="build" onchange="this.form.submit();">
				{html_options options=$arrBuilds selected=$selectedBuild}
			</select>
		</td></tr>
		<tr><td>
			{lang_get s='title_report_type'}
		</td></tr>
		<tr><td>
			<select name="report_type" onchange="this.form.submit();">
				{html_options options=$arrReportTypes selected=$selectedReportType}
			</select>
		</td></tr>
	</table>
</form>
</div>

</body>
</html>
