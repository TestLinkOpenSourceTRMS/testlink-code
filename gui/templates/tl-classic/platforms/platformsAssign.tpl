{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: platformsAssign.tpl,v 1.7 2010/09/06 15:44:45 erikeloff Exp $
Purpose: smarty template - assign platforms to testplans

@internal Revisions:
20100906 - eloff - BUGID 3738 - don't allow removing platform with linked TCs
*}
{lang_get var="labels"
          s="title_platforms,menu_assign_platform_to_testplan,
             platform_unlink_warning_title,platform_unlink_warning_message,
             platform_assignment_no_testplan,btn_save"}

{include file="inc_head.tpl" openHead='yes'}
{include file="inc_ext_js.tpl"}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if $gui->can_do}
  <script type="text/javascript" language="JavaScript">
{* Used to show warnings when trying to remove platform with testcases *}
{$gui->platform_count_js}

  var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
  {$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
  {$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
  {$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
  {$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
  {$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
  {$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");


/* Checks if any of the removed platforms has linked testcases.
 * If that is the case, an alert dialog is displayed
 *
 * 20091201 - Eloff - Added transferLeft function
 */
{$opt_cfg->js_ot_name}.transferLeft={literal}function(){
	options = this.right.options;
	num_with_linked_to_move = 0;
	for(idx=0; idx<options.length; idx++) {
		if(options[idx].selected && platform_count_map[options[idx].text] > 0) {
			num_with_linked_to_move++;
		}
	}
	// Don't allow removal of platforms with linked TCs.
	if (num_with_linked_to_move > 0) {
		Ext.Msg.alert("{/literal}{$labels.platform_unlink_warning_title}{literal}",
		                "{/literal}{$labels.platform_unlink_warning_message}{literal}");
	}
	else {
		// this is the default call from option transfer
		moveSelectedOptions(this.right,this.left,this.autoSort,this.staticOptionRegex); this.update();
	}
};
{/literal}
// Select all options in right panel, and move to left
{$opt_cfg->js_ot_name}.transferAllLeft={literal}function(){
	options = this.right.options;
	Ext.query("option", this.right).each(function(el, i) {
			el.selected = true;
		});
	this.transferLeft();
};
{/literal}
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
			  <input type="hidden" name="doAction" value="">
				{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
				<br />
				<input type="submit" name="doAssignPlatforms" value="{$labels.btn_save}" 
				       onclick="doAction.value='doAssignPlatforms'"	/>
			</form>
		</div>
	{else}
	  {$labels.platform_assignment_no_testplan}
	{/if}
</div>
</body>
</html>
