{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	issueTrackerEdit.tpl

@internal revisions
@since 1.9.4
*}
{$url_args="lib/issuetrackers/issueTrackerEdit.php"}
{$edit_url="$basehref$url_args"}

{lang_get var='labels'
          s='warning,warning_empty_issuetracker_name,warning_empty_issuetracker_type,
             show_event_history,th_issuetracker,th_issuetracker_type,config,btn_cancel,
             issuetracker_show_cfg_example,issuetracker_cfg_example,used_on_testproject,btn_check_connection,issueTracker_connection_ok,issueTracker_connection_ko'}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var warning_empty_issuetracker_name = "{$labels.warning_empty_issuetracker_name|escape:'javascript'}";
var alert_box_title = "{$labels.warning|escape:'javascript'}";
function validateForm(f)
{
  if (isWhitespace(f.name.value))
  {
      alert_message(alert_box_title,warning_empty_issuetracker_name);
      selectField(f, 'name');
      return false;
  }
  return true;
}

function displayITSCfgExample(oid,displayOID)
{
	var type;
	type = Ext.get(oid).getValue();
	Ext.Ajax.request({
		url: fRoot+'lib/ajax/getissuetrackercfgtemplate.php',
		method: 'GET',
		params: {
			type: type
		},
		success: function(result, request) {
			var obj = Ext.util.JSON.decode(result.responseText);
      // after 
      // include of jquery and upgrade of prototype
      // I've started with issues $
      document.getElementById(displayOID).innerHTML = obj['cfg'];
		},
		failure: function (result, request) {
		}
	});
	
}
</script>
</head>

<body>
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$gui->main_descr|escape}</h1>

{if $gui->canManage != ""}
  <div class="workBack">
  
  <div class="action_descr">{$gui->action_descr|escape}
  	{if $gui->mgt_view_events eq "yes" && $gui->item.id > 0}
			<img style="margin-left:5px;" class="clickable" src="{$tlImages.info}"
				 onclick="showEventHistoryFor('{$gui->item.id}','issuetrackers')" 
				 alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
	{/if}
  
  </div><br />
  {include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}


  {if $gui->connectionStatus == 'ok'}
    {$labels.issueTracker_connection_ok}
  {else if $gui->connectionStatus == 'ko'}    
    {$labels.issueTracker_connection_ko}
  {/if}

  	<form name="edit" method="post" action="{$edit_url}" onSubmit="javascript:return validateForm(this);">
  	<table class="common" style="width:50%">
  		<tr>
  			<th>{$labels.th_issuetracker}</th>
  			<td><input type="text" name="name" id="name"  
  			           size="{#ISSUETRACKER_NAME_SIZE#}" maxlength="{#ISSUETRACKER_NAME_MAXLEN#}" 
  				         value="{$gui->item.name|escape}" />
			  		{include file="error_icon.tpl" field="name"}
			  </td>				
  		</tr>
  		<tr>
  			<th>{$labels.th_issuetracker_type}</th>
			<td>
  			<select id="type" name="type">
  				{html_options options=$gui->typeDomain selected=$gui->item.type}
  			</select>
  			<a href="javascript:displayITSCfgExample('type','cfg_example')">{$labels.issuetracker_show_cfg_example}</a>
			</td>
  		</tr>
		
  		<tr>
  			<th>{$labels.config}</th>
  			<td><textarea name="cfg" rows="{#ISSUETRACKER_CFG_ROWS#}" 
  									 cols="{#ISSUETRACKER_CFG_COLS#}">{$gui->item.cfg}</textarea></td>
  		</tr>
  		<tr>
  			<th>{$labels.issuetracker_cfg_example}</th>
  			<td name="cfg_example" id="cfg_example">&nbsp;</td>
  		</tr>
  	</table>

	{if $gui->testProjectSet != ''}
  	<table class="common" style="width:50%">
		<tr>
			<th>
			{$labels.used_on_testproject}
			</th>
		</tr>
		{foreach key=item_id item=item_def from=$gui->testProjectSet}
		<tr>
			<td>
			{$item_def.testproject_name|escape}
			</td>
		</tr>
		{/foreach}
  	</table>
	{/if}

  	<div class="groupBtn">	
	  <input type="hidden" name="id" id="id" value="{$gui->item.id}">
  	<input type="hidden" name="doAction" value="{$gui->operation}" />
    <input type="submit" name="create" id="create" 
           value="{$gui->submit_button_label}"
	         onclick="doAction.value='{$gui->operation}'" />
  
    <input type="submit" name="checkConnection" id="checkConnection" 
           value="{$labels.btn_check_connection}"
           onclick="doAction.value='checkConnection'" />
   
   	<input type="button" value="{$labels.btn_cancel}"
	         onclick="javascript:location.href=fRoot+'lib/issuetrackers/issueTrackerView.php'" />
  	</div>
  	</form>
  </div>
{/if}
</body>
</html>