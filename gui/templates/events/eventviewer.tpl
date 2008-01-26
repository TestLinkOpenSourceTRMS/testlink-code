{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: eventviewer.tpl,v 1.2 2008/01/26 09:31:18 franciscom Exp $ 

Event Viewer

*}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}
</head>

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get var='labels'
          s='event_viewer,th_loglevel,th_timestamp,th_description,th_activity'}


<body {$body_onload}>
<h1>{$labels.event_viewer}</h1>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="event" name=$role.role action="deleted"}


<div class="workBack">
		<table class="common sortable" width="90%">
			<tr>
				<th>{$sortHintIcon}{$labels.th_loglevel}</th>
				<th>{$sortHintIcon}{$labels.th_timestamp}</th>
				<th>{$sortHintIcon}{$labels.th_description}</th>
				<th>{$sortHintIcon}{$labels.th_activity}</th>
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