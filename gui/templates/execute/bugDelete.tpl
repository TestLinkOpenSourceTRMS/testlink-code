{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: bugDelete.tpl,v 1.1 2007/12/19 17:59:33 franciscom Exp $
Purpose: smarty template - show Test Results and Metrics 
*}
{include file="inc_head.tpl"}

<body onunload="dialog_onUnload(bug_dialog)" onload="dialog_onLoad(bug_dialog)">
<h1>{lang_get s='title_delete_bug'}</h1>
<p class='info'>
{$msg}
</p>

<div class="workBack">
		<div class="groupBtn" style="text-align:right">
			<input align="right" type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
</div>

</body>
</html>