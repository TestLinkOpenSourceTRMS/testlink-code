{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource workAreaSimple.tpl
Purpose: show workarea with simple structure title + content + link
*}
{include file="inc_head.tpl"}

<body>
{if isset($gui->title) && $gui->title != ''}
	<h1 class="title">{$gui->title|escape}</h1>
{/if}

<div class="workBack">
{if $gui->content != ''}
	{$gui->content}
{/if}

{if $gui->link_to_op != ''}
  <p><a href="{$basehref}{$gui->link_to_op}">{$gui->hint_text}</a>
{/if}
</div>
</body>
</html>