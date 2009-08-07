{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: platformsAssign.tpl,v 1.1 2009/08/07 06:58:10 franciscom Exp $
Purpose: smarty template - assign platforms to testplans
*}
{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if $can_do}
<script type="text/javascript" language="JavaScript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>
{/if}
</head>

<body
{if $can_do}
	onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])"
{/if}
>

{* improved feedback *}
<div class="workBack">
	<h1 class="title">{lang_get s='title_platforms'}</h1>
	{* tabs *}
	<div class="tabMenu">
		<span class="unselected"><a href="lib/platforms/platformsView.php"
				target='mainframe'>{lang_get s='menu_manage_platforms'}</a></span>
		<span class="selected">{lang_get s='menu_assign_platform_to_testplan'}</span>
	</div>

	{if $can_do}
		{if $platform_assignment_subtitle neq ''}
			<h2>{$platform_assignment_subtitle|escape}</h2>
		{/if}

		{include file="inc_update.tpl" result=$sqlResult item=$level action='updated'}


		{* data form *}
		<div style="margin-top: 25px;">
			<form method="post" action="lib/platforms/platformsAssign.php?tplan_id={$tplan_id}">
				{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
				<br />
				<input type="submit" name="assignPlatforms" value="{lang_get s='btn_save'}" />
			</form>
		</div>
	{else}
		{if $platform_assignment_subtitle neq ''}
			<h2> {$platform_assignment_subtitle}</h2>
		{/if}
	{lang_get s="platform_assignment_no_testplan"}
	{/if}
</div>
</body>
</html>
