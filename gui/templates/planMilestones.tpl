{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planMilestones.tpl,v 1.3 2007/01/13 23:45:36 schlundus Exp $ *}
{* Purpose: smarty template - edit milestones *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_milestones'} {$tpName|escape}</h1>

{* product was added *}
{if $sqlResult ne ""}
	{include file="inc_update.tpl" result=$sqlResult item="Milestone" action="$action"}
{/if}

<div class="workBack">

	<div>
	<h2>{lang_get s='title_new_milestone'}</h2>
	<p class="italic">{lang_get s='info_milestones_date'}</p>
	<form method="post" action="lib/plan/planMilestones.php">
	<input type="hidden" name="id" value="{$mileStone.id|escape}"/>
	<table class="common" width="45%">
		<tr>
			<td>{lang_get s='th_name'}:</td>
			<td>
				<input type="text" name="name" maxlength="100" value="{$mileStone.title|escape}"/>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_date_format'}:</td>
			<td>
				<input type="text" name="date" maxlength="10" value="{$mileStone.date|escape}"/>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_perc_a_prio'}:</td>
			<td>
				<input type="text" name="A" maxlength="3" value="{$mileStone.apriority|escape}"/>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_perc_b_prio'}:</td>
			<td>
				<input type="text" name="B" maxlength="3" value="{$mileStone.bpriority|escape}"/>
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_perc_c_prio'}:</td>
			<td>
				<input type="text" name="C" maxlength="3" value="{$mileStone.cpriority|escape}"/>
			</td>
		</tr>
	</table>
	<p>
	{if $mileStone eq null}
		<input type="submit" name="newMilestone" value="{lang_get s='btn_new_milestone'}" />
	{else}
		<input type="submit" name="update" value="{lang_get s='btn_edit_milestone'}" />
	{/if}
	</p>
	</form>
	</div>


</div>

<div class="workBack">

	<div>
		<h2>{lang_get s='title_existing_milestones'}</h2>
		{if $arrMilestone ne ""}
				<table class="common" width="100%">
					<tr>
						<th>{lang_get s='th_name'}</th>
						<th>{lang_get s='th_date_format'}</th>
						<th>{lang_get s='th_perc_a_prio'}</th>
						<th>{lang_get s='th_perc_b_prio'}</th>
						<th>{lang_get s='th_perc_c_prio'}</th>
						<th>{lang_get s='th_delete'}</th>
					</tr>
					{section name=Row loop=$arrMilestone}
						<tr>
							<td>
								<a href="lib/plan/planMilestones.php?id={$arrMilestone[Row].id}">{$arrMilestone[Row].title|escape}</a>
							</td>
							<td>
								{$arrMilestone[Row].date|escape}
							</td>
							<td>
								{$arrMilestone[Row].apriority|escape}
							</td>
							<td>
								{$arrMilestone[Row].bpriority|escape}
							</td>
							<td>
								{$arrMilestone[Row].cpriority|escape}
							</td>
							<td>
								<a href="lib/plan/planMilestones.php?delete=1&amp;id={$arrMilestone[Row].id}">
								<img style="border:none" alt="{lang_get s='alt_delete_milestone'}" src="icons/thrash.png"/>
								</a>
							</td>
						</tr>
					{/section}
				</table>
		{else}
			{lang_get s='no_milestones'}
		{/if}
	</div>
	
</div>

</body>
</html>
