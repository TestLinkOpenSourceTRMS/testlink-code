{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcEdit.tpl,v 1.11 2006/12/31 18:20:49 franciscom Exp $ *}
{* Purpose: smarty template - edit test specification: test case *}
{*
20060425 - franciscom - added update button at page bottom
20051008 - am - correct wrong link to keywords view page
*}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

<script language="JavaScript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>
</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])">
{config_load file="input_dimensions.conf" section="tcNew"} {* Constant definitions *}
<h1>{lang_get s='test_case'}{$gsmarty_title_sep}{$tc.name|escape}</h1> 

<div class="workBack" style="font-weight: bold;">
<h1>{lang_get s='title_edit_tc'}{$gsmarty_title_sep_type3}{lang_get s='version'} {$tc.version}</h1> 

<form method="post" action="lib/testcases/tcEdit.php">
  <input type="hidden" name="testcase_id"  value="{$tc.testcase_id}">
  <input type="hidden" name="tcversion_id"  value="{$tc.id}">
	<input type="hidden" name="version" value="{$tc.version}" />
	

	<div style="float: right;">
		<input id="do_update" type="submit" name="do_update" value="update" />
	</div>	

  {include file="tcEdit_New_viewer.tpl"}
    
	{* 20060425 - franciscom - same Name DIFFERENT ID *}
	<div style="float: right;">
		<input id="do_update_bottom" type="submit" name="do_update" value="update" />
	</div>	


</form>

<script type="text/javascript" defer="1">
   	document.forms[0].name.focus()
</script>

</div>
</body>
</html>