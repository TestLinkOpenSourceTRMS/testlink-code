{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsByStatus.tpl,v 1.3 2005/10/05 06:14:26 franciscom Exp $
Purpose: show Test Results and Metrics 

20051004 - fm - added print button
*}
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

{include file="inc_print_button.tpl"}

</body>
</html>