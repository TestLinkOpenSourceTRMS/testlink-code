{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: emailSent.tpl,v 1.3 2008/05/06 06:25:29 franciscom Exp $ *}
{* Purpose: smarty template - confirm email has been sent successfully *}
{include file="inc_head.tpl"}
<body>

<h1 class="title">{$tpName|escape} {lang_get s='send_test_report'}</h1>

{if $message != "" }
	<p class='info'>{$message}</p>
{/if}
</div>

</body>
</html>
