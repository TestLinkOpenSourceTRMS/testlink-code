{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_controls_planEdit.tpl,v 1.1 2007/12/06 17:50:33 franciscom Exp $

Rev:
    20071205 - contribution by -
*}

<table style="float: left; text-align:left">
	<tr><td align='right'>
		{lang_get s='testplan_copy_tcases'}
		</td>
		<td align='left'>
		<input type="checkbox" name="copy_tcases" checked="checked"/>
		{lang_get s='testplan_copy_tcases_latest'}<input type="radio" value="latest" name="tcversion_type"/>
		{lang_get s='testplan_copy_tcases_current'}<input type="radio" name="tcversion_type" value="current" checked="1"/>
			</td></tr>
	<tr><td align='left'>
		{lang_get s='testplan_copy_builds'}
		</td>
		<td align='left'>
		<input type="checkbox" name="copy_builds" checked="checked"/>
			</td></tr>
	<tr><td align='left'>
		{lang_get s='testplan_copy_priorities'}
		</td>
		<td align='left'>
		<input type="checkbox" name="copy_priorities" checked="checked"/>
			</td></tr>
	<tr><td align='left'>
		{lang_get s='testplan_copy_milestones'}
		</td>
		<td align='left'>
		<input type="checkbox" name="copy_milestones" checked="checked"/>
			</td></tr>
	<tr><td align='left'>
		{lang_get s='testplan_copy_user_roles'}
		</td>
		<td align='left'>
		<input type="checkbox" name="copy_user_roles" checked="checked"/>
			</td></tr>
</table>