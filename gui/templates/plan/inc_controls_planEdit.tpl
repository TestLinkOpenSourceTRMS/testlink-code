{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_controls_planEdit.tpl,v 1.3 2009/09/21 09:29:14 franciscom Exp $

Rev:
  20120812 - kinow - TICKET 3987: Added fields for copying test plan attachments
  20090807 - franciscom - added platforms feature
*}
{lang_get var="labels"
          s='testplan_copy_builds,testplan_copy_tcases,testplan_copy_tcases_latest,
		         testplan_copy_tcases_current,testplan_copy_builds,
		         testplan_copy_priorities,testplan_copy_milestones,
		         testplan_copy_assigned_to,testplan_copy_user_roles,
		         testplan_copy_platforms_links,testplan_copy_attachments'}

<table style="float: left; text-align:left">
	<tr>
		<td align='left'>
			<input type="checkbox" name="copy_tcases" checked="checked"/>
			{$labels.testplan_copy_tcases}
		</td>
	</tr>
	<tr>
		<td align='left'>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="tcversion_type" value="latest" />{$labels.testplan_copy_tcases_latest}
		</td>
	<tr>
		<td>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="tcversion_type" value="current" checked="1"/>{$labels.testplan_copy_tcases_current}
		</td>
	</tr>
	<tr>
		<td align='left'>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="copy_priorities" checked="checked"/>{$labels.testplan_copy_priorities}
		</td>
	</tr>
	<tr>
		<td align='left'>
			<input type="checkbox" name="copy_builds" checked="checked"/>{$labels.testplan_copy_builds}
		</td>
	</tr>
	<tr>
		<td align='left'>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="copy_assigned_to" checked="checked"/>{$labels.testplan_copy_assigned_to}
		</td>
	</tr>
	<tr>
		<td align='left'>
			<input type="checkbox" name="copy_milestones" checked="checked"/>{$labels.testplan_copy_milestones}
		</td>
	</tr>
	<tr>
		<td align='left'>
			<input type="checkbox" name="copy_user_roles" checked="checked"/>{$labels.testplan_copy_user_roles}
		</td>
	</tr>
	<tr>
		<td align='left'>
			<input type="checkbox" name="copy_attachments" checked="checked"/>{$labels.testplan_copy_attachments}
		</td>
	</tr>
	
	{* always copy platform links *}
	<input type="hidden" name="copy_platforms_links" value="1"/>

</table>