{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource platformsAssign.tpl
Purpose: smarty template - assign platforms to testplans
*}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
          s="title_platforms,menu_assign_platform_to_testplan,
             platform_unlink_warning_title,platform_unlink_warning_message,
             platform_assignment_no_testplan,btn_save,
             btn_save_and_assign_to_tcv,btn_enable_disable_selected"}

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


    // Select all options in right panel, and move to left
    {$opt_cfg->js_ot_name}.transferAllLeft={literal}function(){

      // https://webkul.com/blog/how-to-select-and-deselect-all-options-in-select-box/
      var target = '#' + this.right.id + ' option'; 

      // After some tests it seems it's better to first DESELECT any selected option
      $(target).removeAttr("selected");

      // Now select ALL
      $(target).attr("selected","selected");
      this.transferLeft();  // See below
    };
    {/literal}
    // ----------------------------------------------------------------------------------

    /* Checks if any of the removed platforms has linked testcases.
    * If that is the case, an alert dialog is displayed
    *
    * 20091201 - Eloff - Added transferLeft function
    */
    {$opt_cfg->js_ot_name}.transferLeft={literal}function(){
      options = this.right.options;

      // We will not allow the removal of platforms with linked TCs.
      // Then we need to check
      num_with_linked_to_move = 0;
      for(idx=0; idx<options.length; idx++) {
        if(options[idx].selected && platform_count_map[options[idx].text] > 0) {
          num_with_linked_to_move++;
        }
      }
      // -------------------------------------------------------------------------


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
  </script>
{/if}
</head>

<body {if $gui->can_do} onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])" {/if}>
{include file="aside.tpl"}  

<div id="main-content">
<div class="workBack">
	<h1 class="{#TITLE_CLASS#}">{$gui->mainTitle}</h1>

{if $gui->warning != ''}
  {* do not escape *}
  {$gui->warning}
{/if}

{if $gui->can_do}
		<div style="margin-top: 25px;">
			<form method="post" action="lib/platforms/platformsAssign.php?tplan_id={$gui->tplan_id}">
			  <input type="hidden" name="doAction" value="">
			  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
			  <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">

				{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
				<br />
				<input class="{#BUTTON_CLASS#}" type="submit" 
               name="doAssignPlatforms" id="doAssignPlatforms" 
               value="{$labels.btn_save}" 
				       onclick="doAction.value='doAssignPlatforms'"	/>

       <input class="{#BUTTON_CLASS#}" type="submit" 
              name="doAssignAndTCV" id="doAssignAndTCV"
              value="{$labels.btn_save_and_assign_to_tcv}" 
              onclick="doAction.value='doAssignAndTCV'" />               

       <input class="{#BUTTON_CLASS#}" type="submit" 
              name="doEnableDisable" id="doEnableDisable"
              value="{$labels.btn_enable_disable_selected}" 
              onclick="doAction.value='doEnableDisable'" />               
			</form>
		</div>
	{else}
	  {$labels.platform_assignment_no_testplan}
	{/if}
</div>
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>
