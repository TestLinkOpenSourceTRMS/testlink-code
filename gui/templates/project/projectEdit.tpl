{**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * Smarty template - Edit existing Test project
 *
 * CVS: $Id: projectEdit.tpl,v 1.31.2.1 2011/01/07 19:50:29 franciscom Exp $
 *
 * Revisions:
 *	20110107 - franciscom - BUGID 4145 - removed wrong hidden input
 *  20100930 - franciscom - BUGID 2344: Private test project
 *  20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility
 *	20100212 - havlatm - inventory support
 *	20100204 - franciscom - test project copy
 *  20090512 - franciscom - is_public attribute
 *  20080117 - franciscom - removed displayy of ID -> use projectview feature
 *  20080112 - franciscom - added test case prefix management
 *  20070725 - franciscom - refactoring: if test project qty == 0 -> do not display 
 *   		the edit/delete tab, remove query string from url, to avoid redirect to home page.
 *  20070515 - franciscom - BUGID 0000854: Test project cannot be deleted 
 *   		if name contains a ' (single quote)
 *   		added escape type to escape modifier on onclick javascript event
 *	20070214 - franciscom - BUGID 628: Name edit � Invalid action parameter/other 
 *			behaviours if �Enter� pressed.
 *}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{assign var="managerURL" value="lib/project/projectEdit.php"}
{assign var="editAction" value="$managerURL?doAction=edit&tprojectID="}

{lang_get var="labels" 
	s='show_event_history,th_active,cancel,info_failed_loc_prod,invalid_query,
	create_from_existent_tproject,opt_no,caption_edit_tproject,caption_new_tproject,name,
	title_testproject_management,testproject_enable_priority, testproject_enable_automation,
    public,testproject_color,testproject_alt_color,testproject_enable_requirements,
    testproject_enable_inventory,testproject_features,testproject_description,
    testproject_prefix,availability,mandatory,warning,warning_empty_tcase_prefix,
    warning_empty_tproject_name'}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$editorType}
{include file="inc_del_onclick.tpl"}

{if $gui_cfg->testproject_coloring neq 'none'}
  {include file="inc_jsPicker.tpl"}
{/if}

<script type="text/javascript">
//   BUGID 3943: Escape all messages (string)
	var alert_box_title = "{$labels.warning|escape:'javascript'}";
	var warning_empty_tcase_prefix = "{$labels.warning_empty_tcase_prefix|escape:'javascript'}";
	var warning_empty_tproject_name = "{$labels.warning_empty_tproject_name|escape:'javascript'}";
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
	{/literal}
	</script>
</head>

<body>
<h1 class="title">
	{$main_descr|escape}  {$tlCfg->gui_title_separator_1}
	{$caption|escape}
	{if $mgt_view_events eq "yes" and $gui->tprojectID}
		<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
			     onclick="showEventHistoryFor('{$gui->tprojectID}','testprojects')" 
			     alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
	{/if}
</h1>

{if $user_feedback != ''}
  {include file="inc_update.tpl" user_feedback=$user_feedback feedback_type=$feedback_type}
{/if}

<div class="workBack">
	{if $gui->found == "yes"}
		<div style="width:80%; margin: auto;">
		<form name="edit_testproject" id="edit_testproject"
		      method="post" action="{$managerURL}"
		      onSubmit="javascript:return validateForm(this);">

		<table id="item_view" class="common" style="width:100%; padding:3px;">

			{if $gui->tprojectID eq 0}
		    {if $gui->testprojects != ''}
	 		<tr>
	 			<td>{$labels.create_from_existent_tproject}</td>
		 		<td>
			 		<select name="copy_from_tproject_id">
			 		<option value="0">{$labels.opt_no}</option>
			 		{foreach item=testproject from=$gui->testprojects}
			 			<option value="{$testproject.id}">{$testproject.name|escape}</option>
			 		{/foreach}
			 		</select>
		 		</td>
	 		</tr>
		 	{/if}
			{/if}
			<tr>
				<td>{$labels.name} *</td>
				<td><input type="text" name="tprojectName" size="{#TESTPROJECT_NAME_SIZE#}"
						value="{$gui->tprojectName|escape}" maxlength="{#TESTPROJECT_NAME_MAXLEN#}" />
				  	{include file="error_icon.tpl" field="tprojectName"}
				</td>
			</tr>
			<tr>
				<td>{$labels.testproject_prefix} *</td>
				<td><input type="text" name="tcasePrefix" size="{#TESTCASE_PREFIX_SIZE#}"
	  		           value="{$gui->tcasePrefix|escape}" maxlength="{#TESTCASE_PREFIX_MAXLEN#}" />
				  	{include file="error_icon.tpl" field="tcasePrefix"}
				</td>
			</tr>
			<tr>
				<td>{$labels.testproject_description}</td>
				<td style="width:80%">{$notes}</td>
			</tr>
			{if $gui_cfg->testproject_coloring neq 'none'}
			<tr>
				<th style="background:none;">{$labels.testproject_color}</th>
				<td>
					<input type="text" name="color" value="{$color|escape}" maxlength="12" />
					{* this function below calls the color picker javascript function.
					It can be found in the color directory *}
					<a href="javascript: TCP.popup(document.forms['edit_testproject'].elements['color'], '{$basehref}third_party/color_picker/picker.html');">
						<img width="15" height="13" border="0" alt="{$labels.testproject_alt_color}"
						src="third_party/color_picker/img/sel.gif" />
					</a>
				</td>
			</tr>
			{/if}
			<tr>
				<td>{$labels.testproject_features}</td><td></td>
			</tr>
			<tr>
				<td></td><td>
				  	<input type="checkbox" name="optReq" 
				  			{if $gui->projectOptions->requirementsEnabled} checked="checked"	{/if} />
					{$labels.testproject_enable_requirements}
				</td>
			</tr>
			<tr>
				<td></td><td>
					<input type="checkbox" name="optPriority" 
							{if $gui->projectOptions->testPriorityEnabled} checked="checked"	{/if} />
					{$labels.testproject_enable_priority}
				</td>
			</tr>
			<tr>
				<td></td><td>
					<input type="checkbox" name="optAutomation" 
				  			{if $gui->projectOptions->automationEnabled} checked="checked" {/if} />
					{$labels.testproject_enable_automation}
				</td>
			</tr>
			<tr>
				<td></td><td>
					<input type="checkbox" name="optInventory" 
				  			{if $gui->projectOptions->inventoryEnabled} checked="checked" {/if} />
					{$labels.testproject_enable_inventory}
				</td>
			</tr>

			<tr>
				<td>{$labels.availability}</td><td></td>
			</tr>
			<tr>
				<td></td><td>
			    	<input type="checkbox" name="active" {if $gui->active eq 1} checked="checked" {/if} />
			    	{$labels.th_active}
			    </td>
      		</tr>

			<tr>
				<td></td><td>
			    	<input type="checkbox" name="is_public" {if $gui->is_public eq 1} checked="checked"	{/if} />
			    	{$labels.public}
			    </td>
      		</tr>
			<tr><td cols="2">
		    {if $gui->canManage == "yes"}
				<div class="groupBtn">
		    	{* BUGID 628: Name edit Invalid action parameter/other behaviours if Enter pressed.
                  added hidden   *}
    			<input type="hidden" name="doAction" value="{$doActionValue}" />
				<input type="hidden" name="tprojectID" value="{$gui->tprojectID}" />
			    <input type="submit" name="doActionButton" value="{$buttonValue}" />
				<input type="button" name="go_back" value="{$labels.cancel}" onclick="javascript:history.back();"/>
				</div>
			{/if}
			</td></tr>

		</table>
		</form>
		<p>* {$labels.mandatory}</p>
	</div>
	{else}
		<p class="info">
		{if $gui->tprojectName != ''}
			{$labels.info_failed_loc_prod} - {$gui->tprojectName|escape}!<br />
		{/if}
		{$labels.invalid_query}: {$sqlResult|escape}</p>
	{/if}
</div>
</body>
</html>
