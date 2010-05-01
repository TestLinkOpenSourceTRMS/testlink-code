{* TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: staticPage.tpl,v 1.3 2010/05/01 18:49:19 franciscom Exp $
 * Purpose: smarty template - generic frame to add any content
 *
 * @internal revisions
 *  20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility
 *}
{include file="inc_head.tpl"}
<body>

{if $gui->pageTitle != ""}
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