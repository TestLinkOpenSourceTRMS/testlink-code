{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planMilestoneEdit.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - edit milestones *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_milestones'} {$projectName|escape}</h1>

{* tabs *}
<div class="tabMenu">
	<span class="unselected"><a href="lib/plan/planMilestones.php">{lang_get s='menu_add'}</a></span> 
	<span class="selected">{lang_get s='menu_edit_del'}</span>
</div>

<div class="workBack">

{* product was added *}
{if $sqlResult ne ""}
	{include file="inc_update.tpl" result=$sqlResult item="Milestone" name=$name action="update"}
{/if}

	<div>
		<h2>{lang_get s='title_existing_milestones'}</h2>
		{if $arrMilestone ne ""}
			<p><i>{lang_get s='info_milestones_date'}</i></p>
			<form method="post">
				<table class="common" width="100%">
					<tr>
						<th>{lang_get s='th_name'}</th>
						<th>{lang_get s='th_date_format'}</th>
						<th>{lang_get s='th_perc_a_prio'}</th>
						<th>{lang_get s='th_perc_b_prio'}</th>
						<th>{lang_get s='th_perc_c_prio'}</th>
						<th>{lang_get s='th_delete_q'}</th>
					</tr>
					{section name=Row loop=$arrMilestone}
						<tr>
							<td>
								<input type="hidden" name="{$arrMilestone[Row].id}" value="{$arrMilestone[Row].id}" />
								<input name="title{$arrMilestone[Row].id}" value="{$arrMilestone[Row].title|escape}" maxlength="50" />
							</td>
							<td>
								<input name="date{$arrMilestone[Row].id}" value="{$arrMilestone[Row].date|escape}" maxlength="12" />
							</td>
							<td>
								<input name="aPriority{$arrMilestone[Row].id}" value="{$arrMilestone[Row].A|escape}" maxlength="3" />
							</td>
							<td>
								<input name="bPriority{$arrMilestone[Row].id}" value="{$arrMilestone[Row].B|escape}" maxlength="3" />
							</td>
							<td>
								<input name="cPriority{$arrMilestone[Row].id}" value="{$arrMilestone[Row].C|escape}" maxlength="3" />
							</td>
							<td>
								<input type="checkbox" name="check{$arrMilestone[Row].id}" />
							</td>
						</tr>
					{/section}
				</table>
				<p><input type="submit" name="editMilestone" value="{lang_get s='btn_upd_milestone'}" /></p>
				</form>
		{else}
			{lang_get s='no_milestones'}
		{/if}
		
	</div>
	
</div>

</body>
</html>