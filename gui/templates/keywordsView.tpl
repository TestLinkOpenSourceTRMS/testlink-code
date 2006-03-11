{* smarty template - view all keywords of product; ver. 1.0 *}
{* $Id: keywordsView.tpl,v 1.6 2006/03/11 22:58:02 schlundus Exp $ *}
{* Purpose: smarty template - View all keywords *}
{include file="inc_head.tpl" jsValidate="yes"}

<body>
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

{if $rightsKey ne ""}
	{* user can modify keywords *}
	{* tabs *}
	<div class="tabMenu">
		<span class="selected">{lang_get s='menu_manage_keywords'}</span> 
		<span class="unselected"><a href="lib/general/frmWorkArea.php?feature=keywordsAssign">{lang_get s='menu_assign_kw_to_tc'}</a></span> 
	</div>
{/if}

{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="Keyword" name=$name action="$action"}

{* Create Form *}
{if $rightsKey ne ""}
<div class="workBack">

	<form name="addKey" method="post" action="lib/keywords/keywordsView.php" 
		onsubmit="return valTextLength(this.keyword, 100, 1);">
	<input type="hidden" name="id" value="{$keywordID}" />
	<table class="common">
		<caption>{lang_get s='caption_new_keyword'}</caption>
		<tr>
			<th>{lang_get s='th_keyword'}</th>
			<td><input type="text" name="keyword" size="66" maxlength="100" 
				onblur="this.style.backgroundColor=''" value="{$keyword|escape}"/></td>
		</tr>
		<tr>
			<th>{lang_get s='th_notes'}</th>
			<td><textarea name="notes" rows="3" cols="50">{$notes|escape}</textarea></td>
		</tr>
	</table>
	<div class="groupBtn">	
	{if $keywordID == 0}
		<input type="submit" name="newKey" value="{lang_get s='btn_create_keyword'}" />
	{else}
		<input type="submit" name="editKey" value="{lang_get s='btn_edit_keyword'}" />
	{/if}
	</div>
	</form>

</div>
{/if}

<div class="workBack">

{if $arrKeywords eq ''}
	{lang_get s='no_keywords'}
{else}
	{* data table *}
	<table class="common" width="70%">
		<tr>
			<th width="30%">{lang_get s='th_keyword'}</th>
			<th>{lang_get s='th_notes'}</th>
			{if $rightsKey ne ""}
			<th>{lang_get s='th_delete'}</th>
			{/if}
		</tr>
		{section name=myKeyword loop=$arrKeywords}
		<tr>
			<td>
				{if $rightsKey ne ""}
				<a href="lib/keywords/keywordsView.php?id={$arrKeywords[myKeyword].id}">
				{/if}
				{$arrKeywords[myKeyword].keyword|escape}
				{if $rightsKey ne ""}
				</a>
				{/if}
			</td>
			<td>{$arrKeywords[myKeyword].notes|escape|nl2br}</td>
			{if $rightsKey ne ""}
			<td>
				<a href="lib/keywords/keywordsView.php?deleteKey=1&amp;id={$arrKeywords[myKeyword].id}">
				<img style="border:none" alt="{lang_get s='alt_delete_keyword'}" src="icons/thrash.png"/>
				</a>
			</td>
			{/if}
		</tr>
		{/section}
	</table>
	
{/if}
	<div class="groupBtn">	
		<input type="submit" name="exportAll" value="{lang_get s='btn_export_keywords'}" onclick="location='lib/keywords/keywordsexport.php'" />
		{if $rightsKey ne ""}
		<input type="submit" name="importAll" value="{lang_get s='btn_import_keywords'}" onclick="location='lib/keywords/keywordsimport.php'" />
		{/if}
	</div>
</div>

</body>
</html>