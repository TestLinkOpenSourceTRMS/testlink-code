{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerDelete.tpl,v 1.4 2005/12/09 10:04:33 franciscom Exp $ *}
{* Purpose: smarty template - delete containers in test specification *}
{* 
20050830 - fm - 
changed data -> objectID
added $objectName
added refresh="yes" in inc_update include

20050528 - fm - I18N
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

{include file="inc_title.tpl" title="Delete $level $objectName"}
{include file="inc_update.tpl" result=$sqlResult item=$level refresh="yes"}

{if $sqlResult == ''}
  <h2>{lang_get s='delete_notice'}</h2>
	<p>{lang_get s='question_del'} {$level|escape}?</p>

	<form method="post" 
	      action="lib/testcases/containerEdit.php?sure=yes&objectID={$objectID|escape}">
	
	{if $level == 'category'}
		<input type="submit" name="deleteCat" value="{lang_get s='btn_yes_del_cat'}" />
	{elseif $level == 'component'}
		<input type="submit" name="deleteCOM" value="{lang_get s='btn_yes_del_comp'}" />
	{else}
		<p>{lang_get s='gui_error'}</p>
	{/if}
	
	</form>
{/if}

</div>
</body>
</html>