{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: bug_add.tpl,v 1.1 2006/09/18 07:12:06 franciscom Exp $ *}
{* Purpose: smarty template - the template for the attachment upload dialog *}
{include file="inc_head.tpl"}

<body onunload="dialog_onUnload(bug_dialog)" onload="dialog_onLoad(bug_dialog)">
<h1>{lang_get s='title_bug_add'}</h1>

{if $msg neq ""}
	<p class='info'>{$msg}</p>
{/if}

<div class="workBack">
	<form action="lib/execute/bug_add.php" method="post">
  	<p class="label">{lang_get s='bug_id'}
  	  <input type="text" id="bug_id" name="bug_id" size="16" maxlength="16"/>
			<input type='hidden' value='{$exec_id}' name="exec_id" id="exec_id"/>
			<a style="font-weight:normal" target="_blank" href="{$bts_url}">
			{lang_get s='button_enter_bug'}</a>
		</P>	
		<div class="groupBtn">
			<input type="submit" value="{lang_get s='btn_add_bug'}" onclick="return dialog_onSubmit(bug_dialog)" />
			<input type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
	</form>
</div>

</body>
</html>