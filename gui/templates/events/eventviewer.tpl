{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: eventviewer.tpl,v 1.1 2008/01/22 21:52:19 schlundus Exp $ 
Purpose: smarty template - View defined roles 
*}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

</head>

<body {$body_onload}>
<h1>{lang_get s='title_user_mgmt'} - {lang_get s='title_roles'}</h1>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="event" name=$role.role action="deleted"}


<div class="workBack">
		<table class="common sortable" width="90%">
			<tr>
				<th>{$sortHintIcon}{lang_get s='th_errorlevel'}</th>
				<th>{$sortHintIcon}{lang_get s='th_timestamp'}</th>
				<th>{$sortHintIcon}{lang_get s='th_role_description'}</th>
				<th>{$sortHintIcon}{lang_get s='th_activity'}</th>
			</tr>
			{foreach from=$events item=event}
			<tr>
					<td>{$event->getLogLevel()|escape}</td>
					<td>{localize_timestamp ts=$event->timestamp}</td>
					<td>{$event->description|escape}</td>
					<td>{$event->activityCode|escape}</td>
			</tr>
			{/foreach}
		</table>
</div>

</body>