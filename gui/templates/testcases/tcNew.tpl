{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcNew.tpl,v 1.8 2009/11/22 15:54:27 franciscom Exp $
Purpose: smarty template - create new testcase

20091122 - franciscom - refactoring to use ext-js alert
20070214 - franciscom -
BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed.
 ----------------------------------------------------------------- *}

{lang_get var='labels' s='btn_create,cancel,warning,title_new_tc,
                          warning_empty_tc_title'}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_del_onclick.tpl"}
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
var alert_box_title = "{$labels.warning}";
var warning_empty_testcase_name = "{$labels.warning_empty_tc_title}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.testcase_name.value)) 
  {
      alert_message(alert_box_title,warning_empty_testcase_name);
      selectField(f, 'testcase_name');
      return false;
  }
  return true;
}
</script>
{/literal}

</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0]);focusInputField('testcase_name')">
{config_load file="input_dimensions.conf" section="tcNew"} {* Constant definitions *}

<h1 class="title">{$parent_info.description}{$tlCfg->gui_title_separator_1}
	{$parent_info.name|escape}{$tlCfg->gui_title_separator_2}{$labels.title_new_tc}</h1>

<div class="workBack">

{include file="inc_update.tpl" result=$sqlResult item="testcase" name=$name user_feedback=$user_feedback}

<form method="post" action="lib/testcases/tcEdit.php?containerID={$containerID}"
      name="tc_new" id="tc_new"
      onSubmit="javascript:return validateForm(this);">

	<div class="groupBtn">
	    {* BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed. *}
			<input type="hidden" id="do_create"  name="do_create" value="do_create" />
			<input type="submit" id="do_create_button"  name="do_create_button" value="{$labels.btn_create}" />
			<input type="button" name="go_back" value="{$labels.cancel}" onclick="javascript: history.back();"/>
	</div>	

  {assign var=this_template_dir value=$smarty.template|dirname}
	{include file="$this_template_dir/tcEdit_New_viewer.tpl"}

	<div class="groupBtn">
	    {* BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed. *}
			<input type="hidden" id="do_create_2"  name="do_create" value="do_create" />
			<input type="submit" id="do_create_button_2"  name="do_create_button" value="{$labels.btn_create}" />
			<input type="button" name="go_back" value="{$labels.cancel}" onclick="javascript: history.back();"/>
	</div>	
  
</form>
</div>

{if $sqlResult eq 'ok'}
	{if ($smarty.session.tcspec_refresh_on_action eq "yes") }
		{include file="inc_refreshTree.tpl"}
	{/if}
{/if}

</body>
</html>
