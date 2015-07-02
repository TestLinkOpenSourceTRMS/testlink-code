{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource keywordsEdit.tpl
*}
{lang_get var="labels" s='th_keyword,th_notes'}

{$url_args="lib/keywords/keywordsEdit.php"}
{$keyword_edit_url="$basehref$url_args"}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
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
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$main_descr|escape}</h1>

{if $canManage ne ""}
  <div class="workBack">
  
  <div class="action_descr">{$action_descr|escape}
  	{if $mgt_view_events eq "yes" && $keywordID > 0}
			<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" onclick="showEventHistoryFor('{$keywordID}','keywords')" alt="{lang_get s='show_event_history'}" title="{lang_get s='show_event_history'}"/>
	{/if}
  
  </div><br />
  {include file="inc_update.tpl" user_feedback=$user_feedback }

  	<form name="addKey" method="post" action="{$keyword_edit_url}"
 		      onSubmit="javascript:return validateForm(this);">

  	<table class="common" style="width:50%">
  		<tr>
  			<th>{$labels.th_keyword}</th>
  			<td><input type="text" name="keyword" 
  			           size="{#KEYWORD_SIZE#}" maxlength="{#KEYWORD_MAXLEN#}" 
  				         value="{$keyword|escape}" required />
			  		{include file="error_icon.tpl" field="keyword"}
			  </td>				
  		</tr>
  		<tr>
  			<th>{$labels.th_notes}</th>
  			<td><textarea name="notes" rows="{#NOTES_ROWS#}" cols="{#NOTES_COLS#}">{$notes|escape}</textarea></td>
  		</tr>
  	</table>
  	<div class="groupBtn">	
  	<input type="hidden" name="doAction" value="" />
    <input type="submit" name="create_req" value="{$submit_button_label}"
	         onclick="doAction.value='{$submit_button_action}'" />
  	<input type="button" value="{lang_get s='btn_cancel'}"
	         onclick="javascript:location.href=fRoot+'lib/keywords/keywordsView.php'" />
  	</div>
  	</form>
  </div>
{/if}
</body>
</html>