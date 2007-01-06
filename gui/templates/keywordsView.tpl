{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: keywordsView.tpl,v 1.9 2007/01/06 15:14:35 franciscom Exp $
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
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
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

{if $rightsKey ne ""}
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



{* -------------------------- Create Form -----------------------------------------------   *}
{if $rightsKey ne ""}
  <div class="workBack">
     {* show SQL result *}
    {include file="inc_update.tpl" result=$sqlResult item="Keyword" name=$name action="$action"}

  	<form name="addKey" method="post" action="lib/keywords/keywordsView.php" 
  		    onsubmit="return valTextLength(this.keyword, 100, 1);">
  	<input type="hidden" name="id" value="{$keywordID}" />
  	<table class="common">
  		<tr>
  			<th>{lang_get s='th_keyword'}</th>
  			<td><input type="text" name="keyword" 
  			           size="{#KEYWORD_SIZE#}" maxlength="{#KEYWORD_MAXLEN#}" 
  				         onblur="this.style.backgroundColor=''" value="{$keyword|escape}"/></td>
  		</tr>
  		<tr>
  			<th>{lang_get s='th_notes'}</th>
  			<td><textarea name="notes" rows="{#NOTES_ROWS#}" cols="{#NOTES_COLS#}">{$notes|escape}</textarea></td>
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
{* --------------------------------------------------------------------------------------   *}


<div class="workBack">

  {if $arrKeywords neq ''}
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
				<a href="lib/keywords/keywordsView.php?deleteKey=1&amp;id={$arrKeywords[myKeyword].id}"
				   onclick="return confirm(warning_delete_keyword);">
				<img style="border:none" title="{lang_get s='alt_delete_keyword'}"
				     alt="{lang_get s='alt_delete_keyword'}" src="icons/thrash.png"/>
				</a>
			</td>
			{/if}
		</tr>
		{/section}
	</table>
  {/if}
	

	<div class="groupBtn">	

  	<form name="export" method="post" action="lib/keywords/keywordsView.php" 
		  {if $rightsKey ne ""}
		    <input type="button" name="importAll" value="{lang_get s='btn_import_keywords'}" 
	 	           onclick="location='lib/keywords/keywordsimport.php'" />
		  {/if}

      {if $arrKeywords neq ''}
    	  <input type="submit" name="exportAll" value="{lang_get s='btn_export'}"> 
	      <select name="exportType">
		    {html_options options=$exportTypes}
	      </select>
      {/if}


  	</form>
	</div>
</div>

</body>
</html>