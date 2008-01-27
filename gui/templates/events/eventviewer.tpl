{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: eventviewer.tpl,v 1.3 2008/01/27 21:13:20 schlundus Exp $ 

Event Viewer

//SCHLUNDUS: i will cleanup this file, when i'm finished

*}
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="inc_ext_js.tpl"}
</head>

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get var='labels'
          s='event_viewer,th_loglevel,th_timestamp,th_description'}


<body {$body_onload}>
<h1>{$labels.event_viewer}</h1>

{literal}
<script type="text/javascript">
function showEventDetails(id)
{
	Ext.Ajax.request(
				{
					url : 'lib/events/eventinfo.php' , 
					params : { id : id },
					method: 'POST',
					success: function (result, request)
							 { 
								showDetailWindow(result.responseText); 
							 }
				} 
			);
}
var infoWin;
function showDetailWindow(info)
{
	var item = document.getElementById('eventDetails');
	item.innerHTML = info;
	
	if(!infoWin)
	{
			infoWin = new Ext.Window({
						el:'eventDetailWindow',
						layout:'fit',
						width:700,
						height:400,
						closeAction:'hide',
						plain: true,
						buttons: [{
							text: 'Close',
							handler: function(){
								infoWin.hide();
							}
						}]
				});
	}
	infoWin.show(this);
}
</script>
<style type="text/css">
fieldset
{
	height:100%;
	
}
#eventviewer
{
	white-space:nowrap;
	cursor: hand;
	cursor: pointer;
}

#eventviewer tr.AUDIT
{
	color:blue;
}
#eventviewer tr.ERROR
{
	color:red;
	font-weight:bold;
	border: 1px solid red;
	white-space: pre;
}
#eventviewer tr.WARNING
{
	color:yellow;
}
#eventviewer tr.INFO
{
	color:green;
}
</style>
{/literal}
<div class="workBack">
		<form method="POST" action="lib/events/eventviewer.php">	
			<div style="height:125px;">
			<fieldset class="x-fieldset" style="float:left"><legend>{lang_get s='th_errorlevel'}</legend>		
				<select size="5" multiple="multiple" name="errorLevel[]">
					{foreach from=$errorLevels item=desc key=value}
					{if in_array((string)$value,$selectedErrorLevels) neq false}
						<option selected="selected" value="{$value}">{$desc}</option>
					{else}	
						<option value="{$value}">{$desc}</option>
					{/if}
					{/foreach}
				</select>
			</fieldset>
			<fieldset class="x-fieldset"><legend>{lang_get s='th_timestamp'}</legend>
			{lang_get s='label_startdate'}:&nbsp;<input type="text" name="date1" id="date1" value="{$startDate}" />
			<input type="button" style="cursor:pointer" onclick="showCal('date1-cal','date1');" value="^" />
			<div id="date1-cal" style="position:absolute;"></div>
			{lang_get s='label_enddate'}:&nbsp;<input type="text" name="date2" id="date2" value="{$endDate}" />
			<input type="button" style="cursor:pointer" onclick="showCal('date1-cal','date2');" value="^" />
			<br /><br />
			<input type="submit" value="{lang_get s='btn_apply'}"/>
			<span class="italic">{lang_get s='click_on_event_info'}</span>
			</fieldset>
			<br />
			</div>
		</form>	
		<br/>
		<br/>
		<table class="common sortable" width="95%" id="eventviewer">
			<tr>
				<th>{$sortHintIcon}{lang_get s='th_errorlevel'}</th>
				<th>{$sortHintIcon}{lang_get s='th_timestamp'}</th>
				<th>{$sortHintIcon}{lang_get s='th_role_description'}</th>
			</tr>
			{foreach from=$events item=event}
			<tr onClick="showEventDetails({$event->dbID})" class="{$event->getLogLevel()|escape}">
					<td>{$event->getLogLevel()|escape}</td>
					<td style="white-space:nowrap">{localize_timestamp ts=$event->timestamp}</td>
					<td>{$event->description|truncate:190|escape}</td>
			</tr>
			{/foreach}
		</table>
	<div id="eventDetailWindow" class="x-hidden">
		<div class="x-window-header">Eventdetails</div>
		<div id="eventDetails" class="x-tab"></div>
    </div>
</div>

</body>

