{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecAnalyse.tpl,v 1.8 2005/12/05 00:07:00 kevinlevy Exp $ *}
{* Purpose: smarty template - Analyse REQ coverage *}
{include file="inc_head.tpl"}

<body>

<h1>
	<img alt="{lang_get s='help'}: {lang_get s='req_spec'}" class="help" 
	src="icons/sym_question.gif" style="float: right;"
	onclick="javascript:open_popup('{$helphref}requirementsCoverage.html');" />
	{lang_get s='req_title_analyse'} {$arrReqSpec[$selectedReqSpec]|escape}
</h1>

{include file="inc_update.tpl" result=$sqlResult action=$action}

<div class="workBack">

<div class="onright">
<form method="get">{lang_get s='req_spec_change'}<br />
	<select name="idSRS" onchange="form.submit()">
	{html_options options=$arrReqSpec selected=$selectedReqSpec}</select>
	<span class="bold"><a href="lib/req/reqSpecView.php?idSRS={$selectedReqSpec}">{lang_get s='edit'}</a></span>
</form>
</div>

{* METRICS *}
<table class="invisible">
<tr><td>{lang_get s='req_total_count'}</td><td>{$arrMetrics.expectedTotal}</td></tr>
<tr><td>{lang_get s='req_title_covered'}</td><td>{$arrMetrics.covered}</td></tr>
<tr><td>{lang_get s='req_title_uncovered'}</td><td>{$arrMetrics.uncovered}</td></tr>
<tr><td>{lang_get s='req_title_nottestable'}</td><td>{$arrMetrics.notTestable}</td></tr>
</table>

</div>


<div class="workBack">
<h2>{lang_get s='req_title_covered'} - {$arrMetrics.coveredTestPlan}</h2>

{section name=row loop=$arrCoverage.covered}
{if $smarty.section.row.first}
<table class="simple">
	<tr>
		<th>{lang_get s="req"}</th>
		<th>{lang_get s="testcases"}</th>
	</tr>
{/if}
	<tr>
		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrCoverage.covered[row].id}&idSRS={$selectedReqSpec}">
			{$arrCoverage.covered[row].title|escape}</a></span></td>
		<td>{section name=subrow loop=$arrCoverage.covered[row].coverage}
<a href="lib/testcases/archiveData.php?data={$arrCoverage.covered[row].coverage[subrow].id|escape}&edit=testcase&allow_edit=0">{$arrCoverage.covered[row].coverage[subrow].id|escape}</a>:{$arrCoverage.covered[row].coverage[subrow].title|escape}<br />
		{/section}</td>
	</tr>
{if $smarty.section.row.last}
</table>
{/if}
{sectionelse}
	<p class="bold">{lang_get s='none'}</p>
{/section}
</div>


<div class="workBack">
<h2>{lang_get s='req_title_uncovered'} - {$arrMetrics.coveredTestPlan}</h2>
{section name=row2 loop=$arrCoverage.uncovered}
{if $smarty.section.row2.first}
<table class="simple">
	<tr>
		<th>{lang_get s="req"}</th>
	</tr>
{/if}
	<tr>
		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrCoverage.uncovered[row2].id}&idSRS={$selectedReqSpec}">{$arrCoverage.uncovered[row2].title|escape}</a></span></td>
	</tr>
{if $smarty.section.row2.last}
</table>
{/if}
{sectionelse}
	<p class="bold">{lang_get s='none'}</p>
{/section}
</div>

<div class="workBack">
<h2>{lang_get s='req_title_nottestable'}</h2>

{section name=row3 loop=$arrCoverage.nottestable}
{if $smarty.section.row3.first}
<table class="simple">
	<tr>
		<th>{lang_get s="req"}</th>
	</tr>
{/if}
	<tr>
		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrCoverage.nottestable[row3].id}&idSRS={$selectedReqSpec}">
			{$arrCoverage.nottestable[row3].title|escape}</a></span></td>
	</tr>
{if $smarty.section.row3.last}
</table>
{/if}
{sectionelse}
	<p class="bold">{lang_get s='none'}</p>
{/section}
</div>


</body>
</html>
