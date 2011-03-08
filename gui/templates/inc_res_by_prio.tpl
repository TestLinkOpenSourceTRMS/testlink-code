{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_res_by_prio.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* I18N: 20050528 - fm *}
{* MHT 200507 - modified for associated array *}

<h2>{lang_get s='title_res_by_prio'}</h2>
<p>{lang_get s='tit_milestone'} {$arrDataPriority.milestone} ({lang_get s='tit_end_date'} {$arrDataPriority.deadline})</p>
<table class="simple" style="text-align: center; margin-left: 0px;">
	<tr>
	<th>{lang_get s='trep_prio'}</th>
	<th>{lang_get s='trep_total'}</th>
	<th>{lang_get s='trep_status'}</th>
	<th>{lang_get s='trep_passed'}</th>
	<th>{lang_get s='trep_failed'}</th>
	<th>{lang_get s='trep_blocked'}</th>
	<th>{lang_get s='trep_not_run'}</th>
	<th>{lang_get s='trep_comp_perc'}</th>
	<th>{lang_get s='trep_milestone_goal'}</th>
	</tr>
{section name=Row loop=$arrDataPriority}
	<tr>
		<td>{$arrDataPriority[Row].priority}</td>
		<td>{$arrDataPriority[Row].total}</td>
		<td>{$arrDataPriority[Row].status}</td>
		<td>{$arrDataPriority[Row].pass}</td>
		<td>{$arrDataPriority[Row].fail}</td>
		<td>{$arrDataPriority[Row].blocked}</td>
		<td>{$arrDataPriority[Row].notRun}</td>
		<td>{$arrDataPriority[Row].percentComplete}</td>
		<td>{$arrDataPriority[Row].milestone}</td>
	</tr>
{/section}
</table>
