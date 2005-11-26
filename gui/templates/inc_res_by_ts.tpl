{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_res_by_ts.tpl,v 1.3 2005/11/26 19:58:21 schlundus Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* I18N: 20050528 - fm *}
{*
	20051126 - scs - added escaping of all items
*}

<h2>{lang_get s='title_res_by_ts'}</h2>
<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
	<tr>
		<th>{lang_get s='trep_ts'}</th>
		<th>{lang_get s='trep_risk'}</th>
		<th>{lang_get s='trep_imp'}</th>
	  <th>{lang_get s='trep_prio'}</th>
		<th>{lang_get s='trep_total'}</th>
	  <th>{lang_get s='trep_passed'}</th>
	  <th>{lang_get s='trep_failed'}</th>
	  <th>{lang_get s='trep_blocked'}</th>
	  <th>{lang_get s='trep_not_run'}</th>
		<th>{lang_get s='trep_comp_perc'}</th>

	</tr>
{section name=Row loop=$arrDataCategory}
	<tr>
	{section name=Item loop=$arrDataCategory[Row]}
		<td>{$arrDataCategory[Row][Item]|escape}</td>
	{/section}
	</tr>
{/section}
</table>
