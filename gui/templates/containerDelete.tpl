{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerDelete.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - delete containers in test specification *}
{* I18N: 20050528 - fm *}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

{include file="inc_title.tpl" title="Delete $level"} 
{include file="inc_update.tpl" result=$sqlResult item=$level}
{if $sqlResult == ''}
	<p>{lang_get s='question_del'} {$level|escape}?</p>
	<form method="post" action="lib/testcases/containerEdit.php?sure=yes&data={$data|escape}"> 

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