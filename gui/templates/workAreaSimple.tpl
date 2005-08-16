{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: workAreaSimple.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show workarea with simple structure
 * title + content *}
{include file="inc_head.tpl"}

<body>

{if $title ne ''}
	<h1>{$title|escape}</h1>
{/if}

<div class="workBack">

{if $content ne ''}
	{$content}
{/if}
	
</div>

</body>
</html>