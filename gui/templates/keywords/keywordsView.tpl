{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: keywordsView.tpl,v 1.4 2007/12/08 18:11:12 franciscom Exp $
Purpose: smarty template - View all keywords 

20070102 - franciscom
1. Tab assign to test case will be displayed only if at least one keyword exists
2. add confirmation before deleting

20061007 - franciscom
1. removed message when no keyword availables (useless IMHO)
2. Show export/import buttons only is there are keywords
*}


{include file="inc_head.tpl" jsValidate="yes"}


{lang_get s='warning_delete_keyword' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/keywords/keywordsEdit.php?doAction=do_delete&id=';
</script>
 



<body {$body_onload}>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>{lang_get s='menu_manage_keywords'}</h1>

<div class="workBack">
  {if $arrKeywords neq ''}
	<table class="simple" style="width:80%">
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
			  <img style="border:none;cursor: pointer;"
			       alt="{lang_get s='alt_delete_keyword'}"
             title="{lang_get s='alt_delete_keyword'}"   
             src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"			     
				     onclick="delete_confirmation({$arrKeywords[myKeyword].id},
				              '{$arrKeywords[myKeyword].keyword|escape:'javascript'}',     
				              '{$del_msgbox_title}','{$warning_msg}');" >
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

		  {/if}
      {if $arrKeywords neq ''}
        <input type="button" id="keyword_assign" name="keyword_assign" 
  	           value="{lang_get s='menu_assign_kw_to_tc'}" 
  	           onclick="location.href=fRoot+'lib/general/frmWorkArea.php?feature=keywordsAssign';"/>
      {/if}    
	

		  {if $canManage ne ""}
		    <input type="button" name="do_import" value="{lang_get s='btn_import_keywords'}" 
	 	           onclick="location='{$basehref}/lib/keywords/keywordsImport.php'" />
		  {/if}

      {if $arrKeywords neq ''}
		    <input type="button" name="do_export" value="{lang_get s='btn_export_keywords'}" 
	 	           onclick="location='{$basehref}/lib/keywords/keywordsExport.php?doAction=export'" />
      {/if}
  	</form>
	</div>
</div>

</body>
</html>
