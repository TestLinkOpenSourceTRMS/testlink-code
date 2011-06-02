{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 

confirm email has been sent successfully 
@filesource	emailSent.tpl
*}
{include file="inc_head.tpl"}
<body>

<h1 class="title">{$title|escape} {lang_get s='send_test_report'}</h1>
{if $message != ""}
	<p class='info'>{$message}</p>
{/if}

</body>
</html>