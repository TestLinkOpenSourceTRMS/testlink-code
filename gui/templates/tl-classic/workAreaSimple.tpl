{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: workAreaSimple.tpl,v 1.5 2009/05/18 20:22:10 schlundus Exp $
Purpose: show workarea with simple structure title + content + link
*}
{include file="inc_head.tpl"}

<body>

{if isset($title) && $title ne ''}
	<h1 class="title">{$title|escape}</h1>
{/if}

<div class="workBack">

{if $content ne ''}
	{$content}
{/if}

{* 20060809 - franciscom - if user can solve the problem give him/her the url *}
{if $link_to_op ne ''}
  <p><a href="{$basehref}{$link_to_op}">{$hint_text}</a>
{/if}
	
</div>

</body>
</html>