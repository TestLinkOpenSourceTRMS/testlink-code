{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: attachment404.tpl,v 1.2 2008/05/06 06:25:28 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{include file="inc_head.tpl"}

<body>
<h1 class="title">{lang_get s='title_downloading_attachment'}</h1>
<p class='info'>
	{lang_get s='error_attachment_not_found'} {$gui->id}
</p>

<div class="workBack">
		<div class="groupBtn" style="text-align:right">
			<input align="right" type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
</div>

</body>
</html>