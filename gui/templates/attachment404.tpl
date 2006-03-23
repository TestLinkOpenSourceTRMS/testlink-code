{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: attachment404.tpl,v 1.1 2006/03/23 20:46:26 schlundus Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_downloading_attachment'}</h1>
<p class='info'>
	{lang_get s='error_attachment_not_found'}
</p>

<div class="workBack">
		<div class="groupBtn" style="text-align:right">
			<input align="right" type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
</div>

</body>
</html>