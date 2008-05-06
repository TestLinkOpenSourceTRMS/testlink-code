{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: keywordsView.tpl,v 1.13 2008/05/06 06:26:06 franciscom Exp $
Purpose: smarty template - View all keywords 
*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get s='warning_delete_keyword' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/keywords/keywordsEdit.php?doAction=do_delete&id=';
</script>
 
</head>
<body {$body_onload}>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{lang_get s='menu_manage_keywords'}</h1>

<div class="workBack">
  {if $keywords neq ''}
	<table class="simple sortable" style="width:95%">
		<tr>
			<th width="30%">{$sortHintIcon}{lang_get s='th_keyword'}</th>
			<th>{$sortHintIcon}{lang_get s='th_notes'}</th>
			{if $canManage ne ""}
			<th>{$sortHintIcon}{lang_get s='th_delete'}</th>
			{/if}
		</tr>
		{section name=myKeyword loop=$keywords}
		<tr>
			<td>
				{if $canManage ne ""}
				<a href="lib/keywords/keywordsEdit.php?doAction=edit&amp;id={$keywords[myKeyword]->dbID}">
				{/if}
				{$keywords[myKeyword]->name|escape}
				{if $canManage ne ""}
				</a>
				{/if}
			</td>
			<td>{$keywords[myKeyword]->notes|escape|nl2br}</td>
			{if $canManage ne ""}
			<td class="clickable_icon">
			  <img style="border:none;cursor: pointer;"
			       alt="{lang_get s='alt_delete_keyword'}"
             title="{lang_get s='alt_delete_keyword'}"   
             src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"			     
				     onclick="delete_confirmation({$keywords[myKeyword]->dbID},
				              '{$keywords[myKeyword]->name|escape:'javascript'}',
				              '{$del_msgbox_title}','{$warning_msg}');" />
			</td>
			{/if}
		</tr>
		{/section}
	</table>
  {/if}
	

	<div class="groupBtn">	

  	<form name="keyword_view" id="keyword_view" method="post" action="lib/keywords/keywordsEdit.php"> 
  	  <input type="hidden" name="doAction" value="" />

		  {if $canManage ne ""}
  	    <input type="submit" id="create_keyword" name="create_keyword" 
  	           value="{lang_get s='btn_create_keyword'}" 
  	           onclick="doAction.value='create'"/>

		  {/if}
      {if $keywords neq ''}
        <input type="button" id="keyword_assign" name="keyword_assign" 
  	           value="{lang_get s='menu_assign_kw_to_tc'}" 
  	           onclick="location.href=fRoot+'lib/general/frmWorkArea.php?feature=keywordsAssign';"/>
      {/if}    
	

		  {if $canManage ne ""}
		    <input type="button" name="do_import" value="{lang_get s='btn_import'}" 
	 	           onclick="location='{$basehref}/lib/keywords/keywordsImport.php'" />
		  {/if}

      {if $keywords neq ''}
		    <input type="button" name="do_export" value="{lang_get s='btn_export'}" 
	 	           onclick="location='{$basehref}/lib/keywords/keywordsExport.php?doAction=export'" />
      {/if}
  	</form>
	</div>
</div>

</body>
</html>
