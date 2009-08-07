{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: platformsEdit.tpl,v 1.3 2009/08/07 16:26:55 franciscom Exp $
Purpose: smarty template - View all platforms

rev:
  20090806 - franciscom - refactoring
*}
{assign var="url_args" value="lib/platforms/platformsEdit.php"}
{assign var="platform_edit_url" value="$basehref$url_args"}

{lang_get var="labels"
          s="warning,warning_empty_platform,show_event_history,
             th_platform,th_notes,btn_cancel"}


{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{$labels.warning}";
var warning_empty_platform = "{$labels.warning_empty_platform}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.name.value))
  {
      alert_message(alert_box_title,warning_empty_platform);
      selectField(f, 'name');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>

<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$gui->main_descr|escape}</h1>

{if $gui->canManage ne ""}
  <div class="workBack">
  
  <div class="action_descr">{$gui->action_descr|escape}
	{if $gui->mgt_view_events eq "yes" && $gui->platformID > 0}
			<img style="margin-left:5px;" class="clickable" 
			     src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
			     onclick="showEventHistoryFor('{$gui->platformID}','platforms')" 
			     alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
	{/if}
  
  </div><br />
  {include file="inc_update.tpl" user_feedback=$user_feedback }

  	<form id="addPlatform" name="addPlatform" method="post" action="{$platform_edit_url}"
 		      onSubmit="javascript:return validateForm(this);">

  	<table class="common" style="width:50%">
  		<tr>
  			<th>{$labels.th_platform}</th>
  			{assign var="input_name" value="name"}
  			<td><input type="text" name="{$input_name}"
  			           size="{#PLATFORM_SIZE#}" maxlength="{#PLATFORM_MAXLEN#}"
  				         value="{$gui->name|escape}" />
			  		{include file="error_icon.tpl" field="{$input_name}"}
			  </td>
  		</tr>
  		<tr>
  			<th>{$labels.th_notes}</th>
  			<td><textarea name="notes" rows="{#NOTES_ROWS#}" cols="{#NOTES_COLS#}">{$gui->notes|escape}</textarea></td>
  		</tr>
  	</table>
  	<div class="groupBtn">	
  	<input type="hidden" name="doAction" value="" />
    <input type="submit" id="submitButton" name="submitButton" value="{$gui->submit_button_label}"
	         onclick="doAction.value='{$gui->submit_button_action}'" />
  	<input type="button" value="{$labels.btn_cancel}"
	         onclick="javascript:location.href=fRoot+'lib/platforms/platformsView.php'" />
  	</div>
  	</form>
  </div>
{/if}
{* --------------------------------------------------------------------------------------   *}

</body>
</html>
