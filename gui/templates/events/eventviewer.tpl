{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: eventviewer.tpl,v 1.35 2010/10/26 13:11:34 mx-julian Exp $

Event Viewer
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var='labels'
          s='event_viewer,th_loglevel,th_timestamp,th_description,title_eventdetails,
             title_eventinfo,label_startdate,label_enddate,btn_apply,click_on_event_info,
             btn_clear_events,btn_clear_all_events,th_event_description,select_user,clear_tip,
             message_please_wait,btn_close,th_role_description,th_user,generated_by_TestLink_on,
             show_calender,clear_date'}


{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {assign var="tableID" value=$matrix->tableID}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}

<script type="text/javascript">
var strPleaseWait = "{$labels.message_please_wait|escape:javascript}";
var strCloseButton = "{$labels.btn_close|escape:javascript}";
{literal}
var progressBar = null;

function showEventDetails(id)
{
	progressBar = Ext.Msg.wait(strPleaseWait);
	Ext.Ajax.request(
				{
					url : 'lib/events/eventinfo.php' ,
					params : { id : id },
					method: 'POST',
					success: function(result, request)
							 {
								showDetailWindow(result.responseText);
							 },
					failure: function (result, request)
						{
							if (progressBar)
							{
								progressBar.hide();
							}	
						}
				}
			);
}
var infoWin;
function showDetailWindow(info)
{
	var item = document.getElementById('eventDetails');
	item.innerHTML = info;
	if (progressBar)
	{
		progressBar.hide();
	}
	if(!infoWin)
	{
		infoWin = new Ext.Window({
					el:'eventDetailWindow',
					modal:true,
					autoTabs: true,
					layout:'fit',
					width:700,
					height:500,
					items: new Ext.TabPanel({
						el: 'detailTabs',
						autoTabs:true,
						activeTab:0,
						deferredRender:false,
						border:false
					}),
					closeAction:'hide',
					plain: true,
					buttons: [{
						text: strCloseButton,
						handler: function(){
							infoWin.hide();
						}
					}]
			});
	}
	infoWin.show();
}
</script>
<style type="text/css">
fieldset
{
	height:100%;

}
</style>
{/literal}

</head>
<body {$body_onload}>
<h1 class="title">{$labels.event_viewer}</h1>

<div class="workBack">
		<form method="post" action="lib/events/eventviewer.php">
			<input type="hidden" name="object_id" value="{$gui->object_id}" />
			<input type="hidden" name="object_type" value="{$gui->object_type|escape}" />
			<input type="hidden" name="doAction" id="doAction" value="filter" />
			
			<div style="height:125px;">
			<fieldset class="x-fieldset" style="float:left"><legend>{$labels.th_loglevel}</legend>
				<select name="logLevel[]" size="5" multiple="multiple" >
					{foreach from=$gui->logLevels item=desc key=value}
					{if in_array((string)$value,$gui->selectedLogLevels) neq false}
						<option selected="selected" value="{$value}">{$desc}</option>
					{else}
						<option value="{$value}">{$desc}</option>
					{/if}
					{/foreach}
				</select>
			</fieldset>

			<fieldset class="x-fieldset" style="float:left"><legend>{$labels.select_user}</legend>
        <select name="testers[]" size="5" multiple="multiple">
        	{foreach from=$gui->testers item=userid key=row}
			      {if in_array((string)$row,$gui->selectedTesters) neq false}
        	    <option value="{$row}" selected="selected">{$gui->testers[$row]|escape}</option>
        	  {else}
        	    <option value="{$row}" >{$gui->testers[$row]|escape}</option>
        	  {/if}  
        	{/foreach}
        </select>
			</fieldset>

			<fieldset class="x-fieldset"><legend>{$labels.th_timestamp}</legend>
				<div id="release_date-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
				
			{$labels.label_startdate}:&nbsp;
			<input type="text" name="startDate" id="startDate" value="{$gui->startDate}" 
			       onclick="showCal('startDate-cal','startDate','{$gsmarty_datepicker_format}');" readonly/>
			<img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
			     onclick="showCal('startDate-cal','startDate','{$gsmarty_datepicker_format}');" >
			<img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
			     onclick="javascript:var x = document.getElementById('startDate'); x.value = '';" >
			<div id="startDate-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
			
			{$labels.label_enddate}:&nbsp;
			<input type="text" name="endDate" id="endDate" value="{$gui->endDate}" 
			       onclick="showCal('endDate-cal','endDate','{$gsmarty_datepicker_format}');" readonly/>
			<img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
			     onclick="showCal('endDate-cal','endDate','{$gsmarty_datepicker_format}');" >
			<img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
			     onclick="javascript:var x = document.getElementById('endDate'); x.value = '';" >
			<div id="endDate-cal" style="position:absolute;width:240px;left:540px;z-index:1;"></div>
			
			<input type="submit" value="{$labels.btn_apply}" onclick="doAction.value='filter'" />
			<br />
			{if $gui->canDelete}
			  <br />
			  <input type="submit"  value="{$labels.btn_clear_events}" onclick="doAction.value='clear'" />
			  <img src="{$smarty.const.TL_THEME_IMG_DIR}/sym_question.gif" title="{$labels.clear_tip}">
			{/if}
			</fieldset>
			<br />
			</div>
		</form>
		<br/>
		<br/>
		
				
		{if $gui->warning_msg == ''}
		    {foreach from=$gui->tableSet key=idx item=matrix}
		       {assign var="tableID" value="table_$idx"}
		       {$matrix->renderBodySection($tableID)}
		    {/foreach}
		    <br />
		    <p class="italic">{$labels.click_on_event_info}</p>
		    <br />
		    {$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
		{else}
		    <br />
		    <div class="user_feedback">
		    {$gui->warning_msg}
		    </div>
		{/if} 

</div>
<div id="eventDetailWindow" class="x-hidden">
	<div class="x-window-header">{$labels.title_eventinfo}</div>
	<div id="detailTabs">
		<div class="x-tab" title="{$labels.title_eventdetails}">
			<div id="eventDetails" class="inner-tab"></div>
		</div>
	</div>
</div>
</body>