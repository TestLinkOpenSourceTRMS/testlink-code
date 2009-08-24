{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: platformsAssign.tpl,v 1.3 2009/08/24 07:37:41 franciscom Exp $
Purpose: smarty template - assign platforms to testplans
*}
{lang_get var="labels"
          s="title_platforms,menu_assign_platform_to_testplan,
             platform_assignment_no_testplan,btn_save"}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if $gui->can_do}
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

<body {if $gui->can_do} onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])" {/if}>

<div class="workBack">
	<h1 class="title">{$gui->mainTitle}</h1>

{if $gui->warning != ''}
  {* do not escape *}
  {$gui->warning}
{/if}

{if $gui->can_do}
		<div style="margin-top: 25px;">
			<form method="post" action="lib/platforms/platformsAssign.php?tplan_id={$gui->tplan_id}">
				{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
				<br />
				<input type="submit" name="assignPlatforms" value="{$labels.btn_save}" />
			</form>
		</div>
	{else}
	  {$labels.platform_assignment_no_testplan}
	{/if}
</div>
</body>
</html>
