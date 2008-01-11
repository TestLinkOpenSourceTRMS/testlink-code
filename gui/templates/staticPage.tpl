{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: staticPage.tpl,v 1.1 2008/01/11 00:57:07 havlat Exp $ *}
{* Purpose: smarty template - generic frame to add any content *}
{include file="inc_head.tpl"}
<body>

{if $title != "" }
	<h1>{$title|escape}</h1>
{/if}

<div class="workBack">
{$pageContent}
</div>

</body>
</html>