{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsByStatus.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{include file="inc_head.tpl"}

<body>

<h1>{$title|escape}</h1>

<div class="workBack">
<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
	<tr>
		<th>{lang_get s='th_test_suite'}</th><th>{lang_get s='th_title'}</th>
		<th>{lang_get s='th_build'}</th><th>{lang_get s='th_run_by'}</th>
		<th>{lang_get s='th_date'}</th><th>{lang_get s='th_notes'}</th><th>{lang_get s='th_bugs'}</th>
	</tr>
{section name=Row loop=$arrData}
	<tr>
	{section name=Item loop=$arrData[Row]}
		<td>{$arrData[Row][Item]}</td>
	{/section}
	</tr>
{/section}
</table>
<p class="italic">{lang_get s='info_test_results'}</p>
</div>

</body>
</html>