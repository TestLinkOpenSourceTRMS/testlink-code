{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: emailSent.tpl,v 1.2 2007/02/03 22:14:07 schlundus Exp $ *}
{* Purpose: smarty template - confirm email has been sent successfully *}
{include file="inc_head.tpl"}
<body>

<h1>{$tpName|escape} {lang_get s='send_test_report'}</h1>

{if $message != "" }
	<p class='info'>{$message}</p>
{/if}
</div>

</body>
</html>
