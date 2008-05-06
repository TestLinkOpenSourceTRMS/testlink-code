{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: attachmentdelete.tpl,v 1.3 2008/05/06 06:25:28 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{include file="inc_head.tpl"}

<body onunload="attachmentDlg_onUnload()" onload="attachmentDlg_onLoad()">
<h1 class="title">{lang_get s='title_delete_attachment'}</h1>
<p class='info'>
{if $bDeleted == 1}
	{lang_get s='deleting_was_ok'}
{else}
	{lang_get s='error_attachment_delete'}
{/if}
</p>

<div class="workBack">
		<div class="groupBtn" style="text-align:right">
			<input align="right" type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
</div>

</body>
</html>