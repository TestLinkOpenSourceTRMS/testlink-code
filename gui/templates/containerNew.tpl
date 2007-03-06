{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: containerNew.tpl,v 1.17 2007/03/06 20:19:35 schlundus Exp $
Purpose: smarty template - create containers

20070214 - franciscom -
BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed.

20061231 - franciscom - using parent_info
20060804 - franciscom - changes to add option transfer
*}
{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}

<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
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
var warning_empty_container_name = "{lang_get s='warning_empty_testsuite_name'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.container_name.value)) 
  {
      alert(warning_empty_container_name);
      selectField(f, 'container_name');
      return false;
  }
  return true;
}
</script>
{/literal}

</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0]);focusInputField('name')">
{config_load file="input_dimensions.conf" section="containerEdit"} {* Constant definitions *}

<h1>{$parent_info.description}{$smarty.const.TITLE_SEP}{$parent_info.name|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_create'} {lang_get s=$level}</h1>
	
{include file="inc_update.tpl" result=$sqlResult 
                               user_feedback=$user_feedback
                               item=$level action="add" name=$name
                               refresh=$smarty.session.tcspec_refresh_on_action }


<form method="post" action="lib/testcases/containerEdit.php?containerID={$containerID}"
	      name="container_new" id="container_new"
        onSubmit="javascript:return validateForm(this);">


	<div style="font-weight: bold;">
		<div style="float: right;">
		  {* BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed. *}
      		<input type="hidden" name="add_testsuite" id="add_testsuite" />
			<input type="submit" name="add_testsuite_button" value="{lang_get s='btn_create_testsuite'}" />
		</div>	
		{include file="inc_testsuite_viewer_rw.tpl"}

   {* Custom fields *}
   {if $cf neq ""}
     <br />
     <div class="custom_field_container">
     {$cf}
     </div>
   {/if}
   
  	 <br />
   <div>
   <a href={$gsmarty_href_keywordsView}>{lang_get s='tc_keywords'}</a>
	 {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	 </div>

</div>
</form>
</body>
</html>
