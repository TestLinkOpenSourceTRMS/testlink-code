{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: emailSent.tpl,v 1.1 2007/01/10 07:28:35 kevinlevy Exp $ *}
{* Purpose: smarty template - confirm email has been sent successfully *}
{include file="inc_head.tpl"}
{*
	20051126 - scs - added escaping of tpname
*}
<body>

<h1>{$tpName|escape} {lang_get s='send_test_report'}</h1>

{if $message != "" }
	<p class='info'>{$message}</p>
{/if}
</div>

</body>
</html>