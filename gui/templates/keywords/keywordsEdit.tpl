{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	keywordsEdit.tpl
*}
{$url_args = "lib/keywords/keywordsEdit.php"}
{$keyword_edit_url = "$basehref$url_args"}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{lang_get var='labels' s='th_keyword,show_event_history,th_notes,btn_cancel'}


<script type="text/javascript">
var warning_empty_keyword = "{lang_get s='warning_empty_keyword'}";
function validateForm(f)
{
  if (isWhitespace(f.keyword.value))
  {
    alert(warning_empty_keyword);
    selectField(f, 'keyword');
    return false;
  }
  return true;
}
</script>
</head>

<body>
{$cfg_section = $smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$gui->main_descr|escape}</h1>

{if $gui->canManage != ""}
  <div class="workBack">
  <div class="action_descr">{$gui->action_descr|escape}
  	{if $gui->mgt_view_events eq "yes" && $gui->keywordID > 0}
			<img style="margin-left:5px;" class="clickable" src="{$tlImages.info}" 
				 onclick="showEventHistoryFor('{$gui->keywordID}','keywords')" 
				 alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
	{/if}
  
  </div><br />
  {include file="inc_update.tpl" user_feedback=$gui->user_feedback}

  	<form name="addKey" method="post" action="{$keyword_edit_url}"
 		      onSubmit="javascript:return validateForm(this);">
  	<table class="common" style="width:50%">
  		<tr>
  			<th>{$labels.th_keyword}</th>
  			<td><input type="text" name="keyword" 
  			           size="{#KEYWORD_SIZE#}" maxlength="{#KEYWORD_MAXLEN#}" 
  				         value="{$gui->keyword|escape}" />
			  		{include file="error_icon.tpl" field="keyword"}
			  </td>				
  		</tr>
  		<tr>
  			<th>{$labels.th_notes}</th>
  			<td><textarea name="notes" rows="{#NOTES_ROWS#}" cols="{#NOTES_COLS#}">{$gui->notes|escape}</textarea></td>
  		</tr>
  	</table>
  	<div class="groupBtn">	
	<input type="hidden" name="id" id="id" value="{$gui->keywordID}">
	<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
  	<input type="hidden" id=="doAction" name="doAction" value="" />
    <input type="submit" name="actionButton" value="{$gui->submit_button_label}"
	       onclick="doAction.value='{$gui->submit_button_action}'" />
  	<input type="button" value="{$labels.btn_cancel}"
	         onclick="javascript:location.href=fRoot+'lib/keywords/keywordsView.php?tproject_id={$gui->tproject_id}'" />
  	</div>
  	</form>
  </div>
{/if}
{* --------------------------------------------------------------------------------------   *}

</body>
</html>