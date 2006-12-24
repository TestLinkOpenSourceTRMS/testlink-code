{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcEdit.tpl,v 1.10 2006/12/24 11:48:18 franciscom Exp $ *}
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

<div class="workBack" style="font-weight: bold;">
<h1>{lang_get s='title_edit_tc'} {$tc.name|escape} {lang_get s='version'} {$tc.version}</h1> 

<form method="post" action="lib/testcases/tcEdit.php">
  <input type="hidden" name="testcase_id"  value="{$tc.testcase_id}">
  <input type="hidden" name="tcversion_id"  value="{$tc.id}">
	<input type="hidden" name="version" value="{$tc.version}" />
	

	<div style="float: right;">
		<input id="do_update" type="submit" name="do_update" value="update" />
	</div>	

	<p>{lang_get s='tc_title'}<br />
		<input type="text" name="name"
   	       size="{#TESTCASE_NAME_SIZE#}" 
           maxlength="{#TESTCASE_NAME_MAXLEN#}" 
		       value="{$tc.name|escape}"
			     alt="{lang_get s='alt_add_tc_name'}"/>
	</p>

	<div>{lang_get s='summary'}<br />
		{$summary}
	</div><p>
	
	<div>{lang_get s='steps'}<br />
		{$steps}
	</div><p>

	<div>{lang_get s='expected_results'}<br />
		{$expected_results}
	</div><p>


	<div><a href="lib/keywords/keywordsView.php" target="mainframe">{lang_get s='tc_keywords'}</a>
  {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	</div><p>

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