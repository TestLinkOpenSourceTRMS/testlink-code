{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: emailSent.tpl,v 1.4 2009/02/23 21:42:40 havlat Exp $ *}
{* Purpose: smarty template - confirm email has been sent successfully *}
{include file="inc_head.tpl"}
<body>

<h1 class="title">{$title|escape} {lang_get s='send_test_report'}</h1>
{if $message != "" }
	<p class='info'>{$message}</p>
{/if}

</body>
</html>
