{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: eventinfo.tpl,v 1.7 2009/08/19 19:56:25 schlundus Exp $ 
*}
{lang_get var="labels"
          s='th_loglevel,th_timestamp,th_source,th_description,
             th_session_info,User,th_sessionID,th_activity_code,
             th_object_id,th_object_type,th_activity'}
             
<div class="workBack">
	<table class="simple">
	<tr>
		<th>{$labels.th_loglevel}</th>
		<td>{$event->getLogLevel()|escape}</td>
	</tr>
	<tr>
		<th>{$labels.th_timestamp}</th>
		<td>{localize_timestamp ts=$event->timestamp}</td>
	</tr>
	<tr>
		<th>{$labels.th_source}</th>
		<td>{$event->source|escape}</td>
	</tr>
	<tr>
		<th>{$labels.th_description}</th>
		<td>{$event->description|escape}</td>
	</tr>
	{if $event->transaction}
	<tr>
			<th colspan="2">{$labels.th_session_info}</th>
	</tr>
	<tr>
			<th>{$labels.User}</th>
			<td>
				{if $user}
					{$user->getDisplayName()|escape}
				{else}
					{$event->userID}
				{/if}
			</td>
	</tr>
	<tr>
			<th>{$labels.th_sessionID}</th>
			<td>{$event->sessionID}</td>
	</tr>
	{/if}
	{if $event->objectID}
		<tr>
			<th colspan="2">{$labels.th_activity}</th>
		</tr>
		<tr>
			<th>{$labels.th_activity_code}</th>
			<td>{$event->activityCode|escape}</td>
		</tr>
		<tr>
			<th>{$labels.th_object_id}</th>
			<td>{$event->objectID|escape}</td>
		</tr>
		<tr>
			<th>{$labels.th_object_type}</th>
			<td>{$event->objectType|escape}</td>
		</tr>
	{/if}
	</table>	
</div>
