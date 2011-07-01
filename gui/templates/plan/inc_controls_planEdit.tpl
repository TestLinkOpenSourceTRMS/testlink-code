{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_controls_planEdit.tpl,v 1.3 2009/09/21 09:29:14 franciscom Exp $

Rev:
  20090807 - franciscom - added platforms feature
*}
{lang_get var="labels"
          s='testplan_copy_builds,testplan_copy_tcases,testplan_copy_tcases_latest,
		         testplan_copy_tcases_current,testplan_copy_builds,
		         testplan_copy_priorities,testplan_copy_milestones,
		         testplan_copy_assigned_to,testplan_copy_user_roles,testplan_copy_platforms_links'}

<table style="float: left; text-align:left">
	<tr>
		<td align='left'>
			{$labels.testplan_copy_tcases}
		</td>
		<td align='left'>
			<input type="checkbox" name="copy_tcases" checked="checked"/>
			{$labels.testplan_copy_tcases_latest}<input type="radio" name="tcversion_type" value="latest" />
			{$labels.testplan_copy_tcases_current}<input type="radio" name="tcversion_type" value="current" checked="1"/>
		</td>
	</tr>
	<tr>
		<td align='left'>
			{$labels.testplan_copy_builds}
		</td>
		<td align='left'>
			<input type="checkbox" name="copy_builds" checked="checked"/>
		</td>
	</tr>
	<tr>
		<td align='left'>
			{$labels.testplan_copy_priorities}
		</td>
		<td align='left'>
			<input type="checkbox" name="copy_priorities" checked="checked"/>
		</td>
	</tr>
	<tr>
		<td align='left'>
			{$labels.testplan_copy_milestones}
		</td>
		<td align='left'>
			<input type="checkbox" name="copy_milestones" checked="checked"/>
		</td>
	</tr>
	<tr>
		<td align='left'>
			{$labels.testplan_copy_user_roles}
		</td>
		<td align='left'>
			<input type="checkbox" name="copy_user_roles" checked="checked"/>
		</td>
	</tr>
	<tr>
		<td align='left'>
			{$labels.testplan_copy_platforms_links}
		</td>
		<td align='left'>
			<input type="checkbox" name="copy_platforms_links" checked="checked"/>
		</td>
	</tr>
	<tr>
		<td align='left'>
			{$labels.testplan_copy_assigned_to}
		</td>
		<td align='left'>
			<input type="checkbox" name="copy_assigned_to" checked="checked"/>
		</td>
	</tr>
</table>