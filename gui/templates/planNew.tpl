{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planNew.tpl,v 1.13 2007/01/02 22:02:33 franciscom Exp $

Purpose: smarty template - create Test Plan
Revisions:

	20060224 - franciscom - removed the rights check
	20061109 - mht - update for TL1.7; GUI update
	20061223 - franciscom - input_dimensions.conf
	20070102 - franciscom - added javascript validation for testplan_name

*}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{literal}
<script type="text/javascript">
{/literal}
var warning_empty_tp_name = "{lang_get s='warning_empty_tp_name'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.testplan_name.value)) 
  {
      alert(warning_empty_tp_name);
      selectField(f, 'testplan_name');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>

<body>
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>{lang_get s='testplan_title_tp_management'}</h1>

<div class="tabMenu">
	{if $tpID eq 0}
		<span class="selected">{lang_get s='testplan_menu_create'}</span> 
	{else}
		<span class="unselected"><a href="lib/plan/planEdit.php?action=empty">{lang_get s='testplan_menu_create'}</a></span> 
		<span class="selected">{lang_get s='testplan_menu_edit'}</span> 
	{/if}
	<span class="unselected"><a href="lib/plan/planEdit.php">{lang_get s='testplan_menu_list'}</a></span> 
</div>


<div class="workBack">
{include file="inc_update.tpl" result=$sqlResult item="TestPlan" action="add"}

	<h2>
	{if $tpID eq 0}
		{lang_get s='testplan_title_create'}
		{assign var='form_action' value='create'} 
	{else}
		{lang_get s='testplan_title_edit'} 
		{assign var='form_action' value='update'} 
	{/if}
	{lang_get s='testplan_title_for_project'} {$prod_name|escape}</h2>

	<form method="post" name="testplan_mgmt" id="testplan_mgmt"
	      action="lib/plan/planEdit.php?action={$form_action}"
	      onSubmit="javascript:return validateForm(this);">
	
	<input type="hidden" name="tpID" value="{$tpID}">
	<table class="common" width="80%">
		<tr><th>{lang_get s='testplan_th_name'}</th></tr>
		<tr>
			<td><input type="text" name="testplan_name" 
			           size="{#TESTPLAN_NAME_SIZE#}" 
			           maxlength="{#TESTPLAN_NAME_MAXLEN#}" 
			           value="{$tpName|escape}"/>
  				{include file="error_icon.tpl" field="testplan_name"}
			</td>
		</tr>
		<tr><th>{lang_get s='testplan_th_notes'}</th></tr>
		<tr>
			<td >{$notes}</td>
		</tr>
		{if $tpID eq 0}
			<tr><th>{lang_get s='testplan_question_create_tp_from'}</th></tr>
			<tr><td>
				<select name="copy">
				<option value="noCopy">{lang_get s='opt_no'}</option>
				{section name=number loop=$arrPlan}
					<option value="{$arrPlan[number][0]}">{$arrPlan[number][1]|escape}</option>
				{/section}
				</select>
			</td></tr>
		{else}
			<tr><td>
				{lang_get s='testplan_th_active'}
				<input type="checkbox" name="active" 
				{if $tpActive eq 1}
					checked="checked"
				{/if}
				/>
      		</td></tr>
		{/if}
	</table>

	<div class="groupBtn">	
		{if $tpID eq 0}
			<input type="submit" name="newTestPlan" value="{lang_get s='testplan_btn_new'}" />
		{else}
			<input type="submit" name="editTestPlan" value="{lang_get s='testplan_btn_edit'}" />
		{/if}
	</div>

	</form>

<p>{lang_get s='testplan_txt_notes'}</p>
	
</div>


</body>
</html>