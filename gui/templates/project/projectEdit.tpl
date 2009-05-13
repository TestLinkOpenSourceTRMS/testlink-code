{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: projectEdit.tpl,v 1.19 2009/05/13 05:55:49 franciscom Exp $
Purpose: smarty template - Edit existing product

rev:
    20090512 - franciscom - is_public attribute
    20080117 - franciscom - removed displayy of ID -> use projectview feature
    20080112 - franciscom - added test case prefix management
    20070725 - franciscom
    refactoring: if test project qty == 0 -> do not display the edit/delete tab
                 remove query string from url, to avoid redirect to home page.

    20070515 - franciscom
    BUGID 0000854: Test project cannot be deleted if name contains a ' (single quote)
    added escape type to escape modifier on onclick javascript event

    20070214 - franciscom
    BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed.

*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{assign var="managerURL" value="lib/project/projectEdit.php"}
{assign var="editAction" value="$managerURL?doAction=edit&tprojectID="}

{lang_get var="labels" s='show_event_history,th_active,cancel,info_failed_loc_prod,
                          invalid_query,
                          caption_edit_tproject,caption_new_tproject,name,tcase_id_prefix,
                          title_testproject_management,notes,color,enable_priority, enable_automation,
                          public,enable_requirements,btn_upd,btn_inactivate,btn_activate,btn_del,th_id'}


{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$editorType}
{include file="inc_del_onclick.tpl"}

{if $gui_cfg->testproject_coloring neq 'none'}
  {include file="inc_jsPicker.tpl"}
{/if}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{lang_get s='warning'}";
var warning_empty_tcase_prefix = "{lang_get s='warning_empty_tcase_prefix'}";
var warning_empty_tproject_name = "{lang_get s='warning_empty_tproject_name'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.tprojectName.value))
  {
      alert_message(alert_box_title,warning_empty_tproject_name);
      selectField(f, 'tprojectName');
      return false;
  }
  if (isWhitespace(f.tcasePrefix.value))
  {
      alert_message(alert_box_title,warning_empty_tcase_prefix);
      selectField(f, 'tcasePrefix');
      return false;
  }

  return true;
}
</script>
{/literal}
</head>

<body>
<h1 class="title">{$main_descr|escape}</h1>
<div class="workBack">

{if $user_feedback != ''}
  {include file="inc_update.tpl" user_feedback=$user_feedback feedback_type=$feedback_type}
{/if}

	{* edit product form *}
	{if $found == "yes"}
		<h2>{$caption|escape}
		{if $mgt_view_events eq "yes" and $id}
			<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
			     onclick="showEventHistoryFor('{$id}','testprojects')" 
			     alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
		{/if}
		</h2>
		<div>
		<form name="edit_testproject" id="edit_testproject"
		      method="post" action="{$managerURL}"
		      onSubmit="javascript:return validateForm(this);">

		<input type="hidden" name="tprojectID" value="{$id}" />
		<table id="item_view" class="common" style="width:80%">
			<tr>
				<th style="background:none;">{$labels.name}</th>
				<td><input type="text" name="tprojectName"
  			           size="{#TESTPROJECT_NAME_SIZE#}"
	  		           maxlength="{#TESTPROJECT_NAME_MAXLEN#}"
				           value="{$name|escape}"/>
				  				{include file="error_icon.tpl" field="tprojectName"}
				</td>
			</tr>
	   <tr>
		  <th style="background:none;">{$labels.notes}</th>
		  <td width="80%">{$notes}</td>
	   </tr>

	   {if $gui_cfg->testproject_coloring neq 'none'}
			<tr>
				<th style="background:none;">{$labels.color}</th>
				<td>
					<input type="text" name="color" value="{$color|escape}" maxlength="12" />
					{* this function below calls the color picker javascript function.
					It can be found in the color directory *}
					<a href="javascript: TCP.popup(document.forms['edit_testproject'].elements['color'], '{$basehref}third_party/color_picker/picker.html');">
						<img width="15" height="13" border="0" alt="Click Here to Pick up the color"
						src="third_party/color_picker/img/sel.gif" />
					</a>
				</td>
			</tr>
		 {/if}
			<tr>
				<th style="background:none;">{$labels.tcase_id_prefix}</th>
				<td><input type="text" name="tcasePrefix"
  			           size="{#TESTCASE_PREFIX_SIZE#}"
	  		           maxlength="{#TESTCASE_PREFIX_MAXLEN#}"
				           value="{$tcasePrefix|escape}"/>
				  				{include file="error_icon.tpl" field="tcasePrefix"}
				</td>
			</tr>

			<tr>
				<th style="background:none;">{$labels.enable_requirements}</th>
				<td>
				  <input type="checkbox" name="optReq" {if $optReq eq 1} checked="checked"	{/if} />
				</td>
			</tr>
			<tr>
				<th style="background:none;">{$labels.enable_priority}</th>
				<td>
				  <input type="checkbox" name="optPriority" {if $optPriority eq 1} checked="checked"	{/if} />
				</td>
			</tr>
			<tr>
				<th style="background:none;">{$labels.enable_automation}</th>
				<td>
				  <input type="checkbox" name="optAutomation" {if $optAutomation eq 1} checked="checked"	{/if} />
				</td>
			</tr>

			<tr><th style="background:none;">{$labels.th_active}</th>
			    <td>
			    <input type="checkbox" name="active" {if $active eq 1} checked="checked"	{/if} />
			    </td>
      </tr>
			<tr><th style="background:none;">{$labels.public}</th>
			    <td>
			    <input type="checkbox" name="is_public" {if $gui->is_public eq 1} checked="checked"	{/if} />
			    </td>
      </tr>


		</table>
    {if $canManage == "yes"}
		<div class="groupBtn">
    {* BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed.
                  added hidden   *}
    		<input type="hidden" name="doAction" value="{$doActionValue}" />
		    <input type="submit" name="doActionButton" value="{$buttonValue}" />
			<input type="button" name="go_back" value="{$labels.cancel}" onclick="javascript:history.back();"/>

		</div>
		{/if}

		</form>
	</div>
	{else}
		<p class="info">
		{if $name neq ''}
			{$labels.info_failed_loc_prod} - {$name|escape}!<br />
		{/if}
		{$labels.invalid_query}: {$sqlResult|escape}<p>
	{/if}
</div>
</body>
</html>
