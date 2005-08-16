{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: keywordsAssign.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - assign keywords to one or more test cases *}
{* Andreas Morsing : changed action to updated *}
{include file="inc_head.tpl"}

<body>

<div class="workBack">

<h1>{lang_get s='title_keywords'}</h1>
{* tabs *}
<div class="tabMenu">
	<span class="unselected"><a href="lib/keywords/keywordsView.php"
			target='mainframe'>{lang_get s='menu_view'}</a></span> 
	<span class="unselected"><a href="lib/keywords/keywordsNew.php"
			target='mainframe'>{lang_get s='menu_create'}</a></span> 
	<span class="unselected"><a href="lib/keywords/keywordsEdit.php"
			target='mainframe'>{lang_get s='menu_edit_del'}</a></span>
	<span class="selected">{lang_get s='menu_assign_kw_to_tc'}</span> 
</div>

{include file="inc_update.tpl" result=$sqlResult item=$level action='updated'}


{* data form *}
<div style="margin-top: 25px;">
	<form method="post" action="lib/keywords/keywordsAssign.php?data={$data}&edit={$level}">
	<table class="common" width="85%" cellpadding="10">
		<caption>
{if $level == "component"}
	{lang_get s='title_assign_kw_to_tc_in_com'}
{elseif $level == "category"}
	{lang_get s='title_assign_kw_to_tc_in_cat'}
{elseif $level == "testcase"}
	{lang_get s='title_assign_kw_to_tc'}
{/if}
	&nbsp;{$title|escape}
		</caption>
		{if $level == "testcase"}
		<tr>
			<td>{lang_get s='atc_keywords'}</td>
			{if $tcKeys ne ""}
				<td>{$tcKeys|escape}</td>
			{else}
				<td>{lang_get s='none'}</td>
			{/if}
		</tr>
		{/if}
		<tr>
			{if $level == "testcase"}
			<td valign="top">{lang_get s='sel_all_keywords'}<br/ >
				<i>{lang_get s='info_multi_sel'} </i></td>
			<td valign="top">
				<select name="keywords[]" multiple="multiple">
				{section name=Row loop=$arrKeys}
					{if $arrKeys[Row].selected == "yes"}
						<option value="{$arrKeys[Row].keyword|escape}"
							selected="selected">{$arrKeys[Row].keyword|escape}</option>
					{else}
						<option value="{$arrKeys[Row].keyword|escape}">{$arrKeys[Row].keyword|escape}</option>
					{/if}
				{/section}
			{else}
			<td>{lang_get s='select_keyword_label'}</td>
			<td>
				{if $arrKeys eq ''}
					{lang_get s='no_keywords'} 
				{else}
					<select name="keywords">
					{section name=Row loop=$arrKeys}
					<option value="{$arrKeys[Row].keyword|escape}">{$arrKeys[Row].keyword|escape}</option>
					{/section}
				{/if}
			{/if}
			</select></td>
		</tr>
	</table>
	<input type="submit" name="assign{$level}" value="{lang_get s='btn_assign'}" />
	</form>

</div>

</div>

</body>
</html>