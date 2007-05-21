{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsByStatus.tpl,v 1.7 2007/05/21 06:39:38 franciscom Exp $
Purpose: show Test Results and Metrics 
*}
{include file="inc_head.tpl"}

<body>

<h1>{$title|escape}</h1>

<div class="workBack">
	<table class="simple" style="width: 100%; text-align: left; margin-left: 0px;">
		<tr>
			<th>{lang_get s='th_test_suite'}</th>
			<th>{lang_get s='th_title'}</th>
      {if $type != $gsmarty_tc_status.not_run}
			  <th>{lang_get s='th_build'}</th>
			  <th>{lang_get s='th_run_by'}</th>
			  <th>{lang_get s='th_date'}</th>
 			  <th>{lang_get s='th_notes'}</th>
				<th>{lang_get s='th_bugs'}</th>
			{/if}
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
	
{lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$smarty.const.TL_TIMESTAMP_FORMAT}
</div>
</body>
</html>