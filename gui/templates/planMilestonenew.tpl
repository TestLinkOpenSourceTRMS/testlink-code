{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planMilestonenew.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - edit milestones *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_milestones'} {$projectName}</h1>

{* tabs *}
<div class="tabMenu">
	<span class="selected">{lang_get s='menu_add'}</span> 
	<span class="unselected"><a href="lib/plan/planmilestoneedit.php">{lang_get s='menu_edit_del'}</a></span>
</div>

<div class="workBack">

{* product was added *}
{if $sqlResult ne ""}
	{include file="inc_update.tpl" result=$sqlResult item="Milestone" name=$name action="add"}
{/if}

	<div>
	<h2>{lang_get s='title_new_milestone'}</h2>
	<p><i>{lang_get s='info_milestones_date'}</i></p>
	<form method="post">
	<table class="common" width="45%">
		<tr>
			<td>{lang_get s='th_name'}:</td>
			<td>
				<input type="text" name="name" maxlenght="50" />
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_date_format'}:</td>
			<td>
				<input type="text" name="date" maxlength="12" />
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_perc_a_prio'}:</td>
			<td>
				<input type="text" name="A" maxlength="3" />
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_perc_b_prio'}:</td>
			<td>
				<input type="text" name="B" maxlength="3" />
			</td>
		</tr>
		<tr>
			<td>{lang_get s='th_perc_c_prio'}:</td>
			<td>
				<input type="text" name="C" maxlength="3" />
			</td>
		</tr>
	</table>
	<p><input type="submit" NAME="newMilestone" value="{lang_get s='btn_new_milestone'}" /></p>
	</form>
	</div>


</div>

</body>
</html>