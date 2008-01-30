{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: eventinfo.tpl,v 1.3 2008/01/30 17:49:41 schlundus Exp $ *}
<div class="workBack">
	<table class="simple">
	<tr>
		<th>{lang_get s='th_errorlevel'}</th>
		<td>{$event->getLogLevel()|escape}</td>
	</tr>
	<tr>
		<th>{lang_get s='th_timestamp'}</th>
		<td>{localize_timestamp ts=$event->timestamp}</td>
	</tr>
	<tr>
		<th>{lang_get s='th_source'}</th>
		<td>{$event->source|escape}</td>
	</tr>
	<tr>
		<th>{lang_get s='th_description'}</th>
		<td>{$event->description|escape}</td>
	</tr>
	{if $event->transaction}
	<tr>
			<th colspan="2">{lang_get s='th_session_info'}</th>
	</tr>
	<tr>
			<th>{lang_get s='User'}</th>
			<td>
				{if $user}
					{$user->getDisplayName()}
				{else}
					{$event->userID}
				{/if}
			</td>
	</tr>
	<tr>
			<th>{lang_get s='th_sessionID'}</th>
			<td>{$event->sessionID}</td>
	</tr>
	{/if}
	{if $event->objectID}
		<tr>
			<th colspan="2">{lang_get s='th_activity'}</th>
		</tr>
		<tr>
			<th>{lang_get s='th_activity_code'}</th>
			<td>{$event->activityCode|escape}</td>
		</tr>
		<tr>
			<th>{lang_get s='th_object_id'}</th>
			<td>{$event->objectID|escape}</td>
		</tr>
		<tr>
			<th>{lang_get s='th_object_type'}</th>
			<td>{$event->objectType|escape}</td>
		</tr>
	{/if}
	</table>	
</div>
