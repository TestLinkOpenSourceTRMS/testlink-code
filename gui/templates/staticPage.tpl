{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: staticPage.tpl,v 1.2 2008/05/06 06:25:29 franciscom Exp $ *}
{* Purpose: smarty template - generic frame to add any content *}
{include file="inc_head.tpl"}
<body>

{if $gui->pageTitle != "" }
	<h1 class="title">{$gui->pageTitle|escape}</h1>
{/if}

<div class="workBack">
{$gui->pageContent}
</div>

{if $gui->refreshTree}
   {include file="inc_refreshTree.tpl"}
{/if}
</body>
</html>