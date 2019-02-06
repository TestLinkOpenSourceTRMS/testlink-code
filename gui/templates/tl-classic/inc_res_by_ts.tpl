{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_res_by_ts.tpl,v 1.6 2007/05/15 13:56:38 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
<h2>{lang_get s='title_res_by_ts'}</h2>
<table class="simple" style="text-align: center; margin-left: 0px;">
	<tr>
	  <th>{lang_get s='trep_ts'}</th>
		<th>{lang_get s='trep_total'}</th>
	  <th>{lang_get s='trep_passed'}</th>
	  <th>{lang_get s='trep_failed'}</th>
	  <th>{lang_get s='trep_blocked'}</th>
	  <th>{lang_get s='trep_not_run'}</th>
		<th>{lang_get s='trep_comp_perc'}</th>

	</tr>
{section name=Row loop=$arrDataAllSuites}
	<tr>
	{section name=Item loop=$arrDataAllSuites[Row]}
		<td>{$arrDataAllSuites[Row][Item]|escape}</td>
	{/section}
	</tr>
{/section}
</table>
