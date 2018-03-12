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
<div class="container">
<h1 class="title">{$gui->main_descr|escape}</h1>

{if $gui->canManage != ""}
  <div>
  <div class="action_descr">{$gui->action_descr|escape}
  	{if $gui->mgt_view_events eq "yes" && $gui->keywordID > 0}
			<img style="margin-left:5px;" class="clickable" src="{$tlImages.info}" 
				 onclick="showEventHistoryFor('{$gui->keywordID}','keywords')" 
				 alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
	{/if}
  
  </div><br />
  {include file="inc_update.tpl" user_feedback=$gui->user_feedback}

  	<form name="addKey" method="post" action="{$keyword_edit_url}" class="form-horizontal"
 		      onSubmit="javascript:return validateForm(this);">
  	<div class="form-group row">
  	  <label for="keyword">{$labels.th_keyword}</label>
  			<input type="text" class="form-control" name="keyword" id="keyword"
  			           size="{#KEYWORD_SIZE#}" maxlength="{#KEYWORD_MAXLEN#}" 
  				         value="{$gui->keyword|escape}" />
			  		{include file="error_icon.tpl" field="keyword"}
			</div>
			<div class="form-group row">
			  <label for="notes">{$labels.th_notes}</label>
  			<div>
  			  <textarea class="form-control" name="notes" id="notes" rows="{#NOTES_ROWS#}" cols="{#NOTES_COLS#}">
  			    {$gui->notes|escape}
  			  </textarea>
  			</div>
  		</div>
  	<div class="groupBtn">	
	<input type="hidden" name="id" id="id" value="{$gui->keywordID}">
	<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
  	<input type="hidden" id=="doAction" name="doAction" value="" />
    <input type="submit" class="btn btn-default" name="actionButton" value="{$gui->submit_button_label}"
	       onclick="doAction.value='{$gui->submit_button_action}'" />
  	<input type="button" class="btn btn-default" value="{$labels.btn_cancel}"
	         onclick="javascript:location.href=fRoot+'lib/keywords/keywordsView.php?tproject_id={$gui->tproject_id}'" />
  	</div>
  	</form>
  </div>
{/if}
{* --------------------------------------------------------------------------------------   *}
</div>
</body>
</html>