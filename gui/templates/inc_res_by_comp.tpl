{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_res_by_comp.tpl,v 1.5 2006/11/26 20:30:40 kevinlevy Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* I18N: 20050528 - fm *}
{*
	20051126 - scs - added escaping of all items
*}

<h2>{lang_get s='title_res_by_top_level_suites'}</h2>
<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
	<tr>
		<th>{lang_get s='trep_comp'}</th>
		<th>{lang_get s='trep_total'}</th>
	  <th>{lang_get s='trep_passed'}</th>
	  <th>{lang_get s='trep_failed'}</th>
	  <th>{lang_get s='trep_blocked'}</th>
	  <th>{lang_get s='trep_not_run'}</th>
		<th>{lang_get s='trep_comp_perc'}</th>

	</tr>
{section name=Row loop=$arrDataSuite}
	<tr>
	{section name=Item loop=$arrDataSuite[Row]}
		<td>{$arrDataSuite[Row][Item]|escape}</td>
	{/section}
	</tr>
{/section}
</table>
