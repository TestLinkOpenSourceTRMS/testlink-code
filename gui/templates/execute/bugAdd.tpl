{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: bugAdd.tpl,v 1.2 2008/05/02 07:09:23 franciscom Exp $ *}
{* Purpose: smarty template - the template for the attachment upload dialog 

rev :
     20070304 - franciscom - refactoring 
*}
{include file="inc_head.tpl"}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='help' var='common_prefix'}
{assign var="text_hint" value="$common_prefix"}

<body onunload="dialog_onUnload(bug_dialog)" onload="dialog_onLoad(bug_dialog)">
<h1>
 {lang_get s='title_bug_add'} 
 {include file="inc_help.tpl" help="bug_add" locale=$locale 
          inc_help_alt="$text_hint" inc_help_title="$text_hint"}
</h1>

{include file="inc_update.tpl" user_feedback=$msg}

<div class="workBack">
	<form action="lib/execute/bugAdd.php" method="post">
  	<p>
			<a style="font-weight:normal" target="_blank" href="{$bts_url}">
			{lang_get s='link_bts_create_bug'}({$gsmarty_interface_bugs|lower|capitalize})</a>
		</p>	
  	<p class="label">{$gsmarty_interface_bugs|lower|capitalize} {lang_get s='bug_id'}
  	  <input type="text" id="bug_id" name="bug_id" size="{#BUGID_SIZE#}" maxlength="{#BUGID_MAXLEN#}"/>
			<input type='hidden' value='{$exec_id}' name="exec_id" id="exec_id"/>
		</P>	
		<div class="groupBtn">
			<input type="submit" value="{lang_get s='btn_add_bug'}" onclick="return dialog_onSubmit(bug_dialog)" />
			<input type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
	</form>
</div>

</body>
</html>