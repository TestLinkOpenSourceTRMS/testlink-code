{* smarty template - view all keywords of product; ver. 1.0 *}
{* $Id: keywordsView.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - View all keywords *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_keywords'}</h1>

{if $rightsKey ne ""}
	{* user can modify keywords *}
	{* tabs *}
	<div class="tabMenu">
		<span class="selected">{lang_get s='menu_view'}</span> 
		<span class="unselected"><a href="lib/keywords/keywordsNew.php">{lang_get s='menu_create'}</a></span> 
		<span class="unselected"><a href="lib/keywords/keywordsEdit.php">{lang_get s='menu_edit_del'}</a></span>
		<span class="unselected"><a href="lib/general/frmWorkArea.php?feature=keywordsAssign">{lang_get s='menu_assign_kw_to_tc'}</a></span> 
	</div>
{/if}

<div class="workBack">

{if $arrKeywords eq ''}
	{lang_get s='no_keywords'}
{else}
	{* data table *}
	<table class="simple" width="70%">
		<tr>
			<th width="30%">{lang_get s='th_keyword'}</th>
			<th>{lang_get s='th_notes'}</th>
		</tr>
		{section name=myKeyword loop=$arrKeywords}
		<tr>
			<td>{$arrKeywords[myKeyword].keyword|escape}</td>
			<td>{$arrKeywords[myKeyword].notes|escape}</td>
		</tr>
		{/section}
	</table>
{/if}
</div>

</body>
</html>