{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcEdit.tpl,v 1.8 2006/04/24 17:44:59 franciscom Exp $ *}
{* Purpose: smarty template - edit test specification: test case *}
{*
20060303 - franciscom
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
		<input type="text" name="name" size="40" value="{$tc.name|escape}"
			alt="{lang_get s='alt_add_tc_name'}"/>
	</p>

	<div>{lang_get s='summary'}<br />
		{$summary}
	</div>
	
	<div>{lang_get s='steps'}<br />
		{$steps}
	</div>

	<div>{lang_get s='expected_results'}<br />
		{$expected_results}
	</div>

  {* 	
	==========================================================================================================
	<p><a href="lib/keywords/keywordsView.php" target="mainframe">{lang_get s='tc_keywords'}</a><br />
		<select name="keywords[]" style="width: 30%" size="{$keySize}" multiple="multiple">
		{section name=oneKey loop=$keys}
				{if $keys[oneKey].selected == "yes"}
					<option value="{$keys[oneKey].key|escape}" selected="selected">{$keys[oneKey].key|escape}</option>
			{else}
					<option value="{$keys[oneKey].key|escape}">{$keys[oneKey].key|escape}</option>
			{/if}
		{/section}
		</select>
	</p>
  ==========================================================================================================
  *}
  
  {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}



</form>

<script type="text/javascript" defer="1">
   	document.forms[0].title.focus()
</script>

</div>
</body>
</html>