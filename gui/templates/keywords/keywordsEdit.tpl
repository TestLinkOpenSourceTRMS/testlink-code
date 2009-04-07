{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: keywordsEdit.tpl,v 1.8 2009/04/07 18:55:29 schlundus Exp $
Purpose: smarty template - View all keywords 
*}
{assign var="url_args" value="lib/keywords/keywordsEdit.php"}
{assign var="keyword_edit_url" value="$basehref$url_args"}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{literal}
<script type="text/javascript">
{/literal}
var warning_empty_keyword = "{lang_get s='warning_empty_keyword'}";
{literal}
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
{/literal}
</head>

<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
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
  			<th>{lang_get s='th_keyword'}</th>
  			<td><input type="text" name="keyword" 
  			           size="{#KEYWORD_SIZE#}" maxlength="{#KEYWORD_MAXLEN#}" 
  				         value="{$keyword|escape}" />
			  		{include file="error_icon.tpl" field="keyword"}
			  </td>				
  		</tr>
  		<tr>
  			<th>{lang_get s='th_notes'}</th>
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
{* --------------------------------------------------------------------------------------   *}

</body>
</html>
