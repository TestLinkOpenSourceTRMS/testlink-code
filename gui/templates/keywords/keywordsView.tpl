{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: keywordsView.tpl,v 1.2 2007/12/08 15:41:37 franciscom Exp $
Purpose: smarty template - View all keywords 

20070102 - franciscom
1. Tab assign to test case will be displayed only if at least one keyword exists
2. add confirmation before deleting

20061007 - franciscom
1. removed message when no keyword availables (useless IMHO)
2. Show export/import buttons only is there are keywords
*}


{include file="inc_head.tpl" jsValidate="yes"}

<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}


{literal}
<script type="text/javascript">
{/literal}
var warning_enter_less1 = "{lang_get s='warning_enter_less1'}";
var warning_enter_at_least1 = "{lang_get s='warning_enter_at_least1'}";
var warning_enter_at_least2 = "{lang_get s='warning_enter_at_least2'}";
var warning_enter_less2 = "{lang_get s='warning_enter_less2'}";
var warning_delete_keyword="{lang_get s='warning_delete_keyword'}";
{literal}
</script>
{/literal}


<h1>{lang_get s='title_keywords'}</h1>

{if $canManage ne ""}
	{* user can modify keywords *}
	{* tabs *}
	<div class="tabMenu">
		<span class="selected">{lang_get s='menu_manage_keywords'}</span> 
    {if $arrKeywords neq ''}
       <span class="unselected">
         <a href="lib/general/frmWorkArea.php?feature=keywordsAssign">{lang_get s='menu_assign_kw_to_tc'}</a>
       </span>
    {/if}    
	</div>
{/if}



<div class="workBack">
  {if $arrKeywords neq ''}
	<table class="common" width="70%">
		<tr>
			<th width="30%">{lang_get s='th_keyword'}</th>
			<th>{lang_get s='th_notes'}</th>
			{if $canManage ne ""}
			<th>{lang_get s='th_delete'}</th>
			{/if}
		</tr>
		{section name=myKeyword loop=$arrKeywords}
		<tr>
			<td>
				{if $canManage ne ""}
				<a href="lib/keywords/keywordsEdit.php?doAction=edit&id={$arrKeywords[myKeyword].id}">
				{/if}
				{$arrKeywords[myKeyword].keyword|escape}
				{if $canManage ne ""}
				</a>
				{/if}
			</td>
			<td>{$arrKeywords[myKeyword].notes|escape|nl2br}</td>
			{if $canManage ne ""}
			<td class="clickable_icon">
				<a href="lib/keywords/keywordsView.php?deleteKey=1&amp;id={$arrKeywords[myKeyword].id}"
				   onclick="return confirm(warning_delete_keyword);">
				<img style="border:none" title="{lang_get s='alt_delete_keyword'}"
				     alt="{lang_get s='alt_delete_keyword'}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/>
				</a>
			</td>
			{/if}
		</tr>
		{/section}
	</table>
  {/if}
	

	<div class="groupBtn">	

  	<form name="keyword_view" id="keyword_view" method="post" action="lib/keywords/keywordsEdit.php"> 
  	  <input type="hidden" name="doAction" value="">

		  {if $canManage ne ""}
  	    <input type="submit" id="create_keyword" name="create_keyword" 
  	           value="{lang_get s='btn_create_keyword'}" 
  	           onclick="doAction.value='create'"/>

		    <input type="button" name="do_import" value="{lang_get s='btn_import_keywords'}" 
	 	           onclick="location='{$basehref}/lib/keywords/keywordsImport.php'" />
		  {/if}

      {if $arrKeywords neq ''}
		    <input type="button" name="do_export" value="{lang_get s='btn_import_keywords'}" 
	 	           onclick="location='{$basehref}/lib/keywords/keywordsImport.php'" />
      {/if}
  	</form>
	</div>
</div>

</body>
</html>
