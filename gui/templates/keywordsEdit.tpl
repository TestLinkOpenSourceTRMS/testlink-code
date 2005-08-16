{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: keywordsEdit.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - edit / delete keywords *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_keywords'}</h1>

{* tabs *}
<div class="tabMenu">
	<span class="unselected"><a href="lib/keywords/keywordsView.php">{lang_get s='menu_view'}</a></span> 
	<span class="unselected"><a href="lib/keywords/keywordsNew.php">{lang_get s='menu_create'}</a></span> 
	<span class="selected">{lang_get s='menu_edit_del'}</span>
	<span class="unselected"><a href="lib/general/frmWorkArea.php?feature=keywordsAssign">{lang_get s='menu_assign_kw_to_tc'}</a></span> 
</div>

{if $updated eq "yes"}
	<div>
	<p class="bold">{lang_get s='keywords_updated'}</p>
	<table class="simple" width="50%">
		<tr>
			<th>{lang_get s='th_keyword'}</th>
			<th>{lang_get s='th_result'}</th>
		</tr>
		{section name=myKey loop=$arrUpdate}
		<tr>
			<td>{$arrUpdate[myKey].keyword|escape}</td>
			<td>{$arrUpdate[myKey].result|escape}</td>
		</tr>
		{/section}
	</table>
	</div>
{/if}

<div class="workBack">

{if $arrKeywords eq ''}
	 {lang_get s='no_keywords'} 
{else}
	{* data form *}
	<form method="post" action="lib/keywords/keywordsEdit.php">
	<table class="common">
		<tr>
			<th>{lang_get s='th_keyword'}</th>
			<th>{lang_get s='th_notes'}</th>
			<th>{lang_get s='th_delete_q'}</th>
		</tr>

		{section name=myKeyword loop=$arrKeywords}
		<tr>
			<td>
				<input type="hidden" name="id{$arrKeywords[myKeyword].id}" 
				value="{$arrKeywords[myKeyword].id}" />
				<input type="edit" name="keyword{$arrKeywords[myKeyword].id}" 
				value="{$arrKeywords[myKeyword].keyword|escape}" maxlength="100" />
			</td>
			<td>
				<textarea rows="2" cols="50" name="notes{$arrKeywords[myKeyword].id}">{$arrKeywords[myKeyword].notes|escape}</textarea>
			</td>
			<td><input type="checkbox" name="check{$arrKeywords[myKeyword].id}" /></td>
		</tr>
		{/section}

	</table>
	<input type="submit" name="editKey" value="{lang_get s='btn_multi_upd'}" />
	</form>
{/if}
</div>

</body>
</html>