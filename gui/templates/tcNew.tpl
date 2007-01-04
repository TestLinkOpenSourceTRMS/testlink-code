{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcNew.tpl,v 1.18 2007/01/04 15:27:58 franciscom Exp $
Purpose: smarty template - create new testcase

20061231 - franciscom - use of $gsmarty_href_keywordsView
                        use a class for the labels

20070104 - franciscom - added javascript validation for testcase_name
*}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

<script language="JavaScript" type="text/javascript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_testcase_name = "{lang_get s='warning_empty_tc_title'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.testcase_name.value)) 
  {
      alert(warning_empty_testcase_name);
      selectField(f, 'testcase_name');
      return false;
  }
  return true;
}
</script>
{/literal}

</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])">
{config_load file="input_dimensions.conf" section="tcNew"} {* Constant definitions *}

<h1>{$parent_info.description}{$smarty.const.TITLE_SEP}{$parent_info.name|escape}</h1>
<div class="workBack">
<h1>{lang_get s='title_new_tc'}</h1>

{include file="inc_update.tpl" result=$sqlResult item="TestCase" name=$name}

<form method="post" action="lib/testcases/tcEdit.php?containerID={$containerID}"
      name="tc_new" id="tc_new"
      onSubmit="javascript:return validateForm(this);">

	<div style="float: right;">
			<input id="do_create" type="submit" name="do_create" value="{lang_get s='btn_create'}" />
	</div>	

  {include file="tcEdit_New_viewer.tpl"}

</form>
</div>

{if $sqlResult eq 'ok'}
	{include file="inc_refreshTree.tpl"}
{/if}

</body>
</html>