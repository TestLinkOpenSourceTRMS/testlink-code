{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: bugAdd.tpl,v 1.10 2010/06/24 17:25:53 asimon83 Exp $ *}
{* Purpose: smarty template - the template for the attachment upload dialog 

rev :
     20070304 - franciscom - refactoring 
*}
{include file="inc_head.tpl"}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<body onunload="dialog_onUnload(bug_dialog)" onload="dialog_onLoad(bug_dialog)">
<h1 class="title">
	{lang_get s='title_bug_add'} 
	{include file="inc_help.tpl" helptopic="hlp_btsIntegration" show_help_icon=true}
</h1>

{include file="inc_update.tpl" user_feedback=$msg}

<div class="workBack">
	<form action="lib/execute/bugAdd.php" method="post">
	  	<p>
			<a style="font-weight:normal" target="_blank" href="{$bts_url}">
			{lang_get s='link_bts_create_bug'}({$gui->interface_bugs|lower|capitalize})</a>
		</p>	
	  	<p class="label">{$gui->interface_bugs|lower|capitalize} {lang_get s='bug_id'}
  	 		<input type="text" id="bug_id" name="bug_id" size="{#BUGID_SIZE#}" maxlength="{$bugIDMaxLength}"/>
		</p>	
		<div class="groupBtn">
			<input type="submit" value="{lang_get s='btn_add_bug'}" onclick="return dialog_onSubmit(bug_dialog)" />
			<input type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
	</form>
</div>

</body>
</html>