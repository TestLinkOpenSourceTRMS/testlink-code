{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource bugAdd.tpl
*}
{include file="inc_head.tpl"}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<body onunload="dialog_onUnload(bug_dialog)" onload="dialog_onLoad(bug_dialog)">
<h1 class="title">
	{lang_get s='title_bug_add'} 
	{include file="inc_help.tpl" helptopic="hlp_btsIntegration" show_help_icon=true}
</h1>

{include file="inc_update.tpl" user_feedback=$gui->msg}
<div class="workBack">
	<form action="lib/execute/bugAdd.php" method="post">
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
		{if $gui->user_action == 'link'}
	  	<p>
			<a style="font-weight:normal" target="_blank" href="{$gui->createIssueURL}">
			{lang_get s='link_bts_create_bug'}({$gui->issueTrackerVerboseID|escape})</a>
		</p>	
	  	<p class="label">{$gui->issueTrackerVerboseType|escape} {lang_get s='bug_id'}
  	 		<input type="text" id="bug_id" name="bug_id" size="{#BUGID_SIZE#}" maxlength="{$gui->bugIDMaxLength}"/>
		</p>	
		{/if}
		<div class="groupBtn">
     {if $gui->user_action == 'link'}
			<input type="submit" value="{lang_get s='btn_add_bug'}" onclick="return dialog_onSubmit(bug_dialog)" />
     {/if} 
			<input type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
 	</form>
</div>

</body>
</html>