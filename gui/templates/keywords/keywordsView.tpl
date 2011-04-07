{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: keywordsView.tpl,v 1.17 2010/10/17 09:46:37 franciscom Exp $
Purpose: smarty template - View all keywords 
*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var='labels'
          s='th_notes,th_keyword,th_delete,btn_import,btn_export,
             menu_assign_kw_to_tc,btn_create_keyword'}

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
	<table class="simple_tableruler sortable">
		<tr>
			<th width="30%">{$tlImages.sort_hint}{$labels.th_keyword}</th>
			<th>{$tlImages.sort_hint}{$labels.th_notes}</th>
			{if $canManage != ""}
				<th style="min-width:70px">{$tlImages.sort_hint}{$labels.th_delete}</th>
			{/if}
		</tr>
		{section name=myKeyword loop=$keywords}
		<tr>
			<td>
				{if $canManage != ""}
					<a href="lib/keywords/keywordsEdit.php?doAction=edit&amp;id={$keywords[myKeyword]->dbID}">
				{/if}
				{$keywords[myKeyword]->name|escape}
				{if $canManage != ""}
					</a>
				{/if}
			</td>
			<td>{$keywords[myKeyword]->notes|escape|nl2br}</td>
			{if $canManage ne ""}
				<td class="clickable_icon">
			  		<img style="border:none;cursor: pointer;"
			       		alt="{lang_get s='alt_delete_keyword'}" title="{lang_get s='alt_delete_keyword'}"   
             		src="{$tlImages.delete}"			     
				     	 onclick="delete_confirmation({$keywords[myKeyword]->dbID},
				              '{$keywords[myKeyword]->name|escape:'javascript'|escape}',
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
	  	           value="{$labels.btn_create_keyword}" 
	  	           onclick="doAction.value='create'"/>
		{/if}
	    {if $keywords neq ''}
	    	<input type="button" id="keyword_assign" name="keyword_assign" 
	  	    	value="{$labels.menu_assign_kw_to_tc}" 
	  	        onclick="location.href=fRoot+'lib/general/frmWorkArea.php?feature=keywordsAssign';"/>
	    {/if}    
		
		{if $canManage ne ""}
			<input type="button" name="do_import" value="{$labels.btn_import}" 
		 		onclick="location='{$basehref}/lib/keywords/keywordsImport.php'" />
		{/if}
	
	    {if $keywords neq ''}
			<input type="button" name="do_export" value="{$labels.btn_export}" 
		 		onclick="location='{$basehref}/lib/keywords/keywordsExport.php?doAction=export'" />
	    {/if}
	  	</form>
	</div>
</div>

</body>
</html>
