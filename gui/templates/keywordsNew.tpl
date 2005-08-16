{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: keywordsNew.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - create new keywords *}
{include file="inc_head.tpl" jsValidate="yes"}

<body onload="document.forms[0].elements[0].focus()">

{literal}
<script type="text/javascript">
{/literal}
var warning_enter_less1 = "{lang_get s='warning_enter_less1'}";
var warning_enter_at_least1 = "{lang_get s='warning_enter_at_least1'}";
var warning_enter_at_least2 = "{lang_get s='warning_enter_at_least2'}";
var warning_enter_less2 = "{lang_get s='warning_enter_less2'}";
{literal}
</script>
{/literal}

<h1>{lang_get s='title_keywords'}</h1>

{* Tabs *}
<div class="tabMenu">
	<span class="unselected"><a href="lib/keywords/keywordsView.php">{lang_get s='menu_view'}</a></span> 
	<span class="selected">{lang_get s='menu_create'}</span>
	<span class="unselected"><a href="lib/keywords/keywordsEdit.php">{lang_get s='menu_edit_del'}</a></span> 
	<span class="unselected"><a href="lib/general/frmWorkArea.php?feature=keywordsAssign">{lang_get s='menu_assign_kw_to_tc'}</a></span> 
</div>

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="Keyword" name=$name action="add"}


{* Create Form *}
<div class="workBack">

	<form name="addKey" method="post" action="lib/keywords/keywordsNew.php" 
		onsubmit="return valTextLength(this.keyword, 100, 1);">
	<table class="common">
		<caption>{lang_get s='caption_new_keyword'}</caption>
		<tr>
			<th>{lang_get s='th_keyword'}</th>
			<td><input type="text" name="keyword" size="66" maxlength="100" 
				onblur="this.style.backgroundColor=''" /></td>
		</tr>
		<tr>
			<th>{lang_get s='th_notes'}</th>
			<td><textarea name="notes" rows="3" cols="50"></textarea></td>
		</tr>
	</table>
	<input type="submit" name="newKey" value="{lang_get s='btn_create_keyword'}" />
	</form>

</div>

</body>
</html>