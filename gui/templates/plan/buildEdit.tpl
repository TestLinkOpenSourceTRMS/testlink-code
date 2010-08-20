{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildEdit.tpl,v 1.17 2010/08/20 13:39:41 franciscom Exp $

Purpose: smarty template - Add new build and show existing

Rev:
    20100820 - franciscom - refactored to use only $gui as interface from php code
    20100707 - asimon - BUGID 3406: addition of items for copying user
                        assignments from other builds
    
    20080217 - franciscom
    Problems with history.goback, using call to view builds on goback
    
    20071216 - franciscom
    user feedback using ext_js
    
    20070214 - franciscom 
    BUGID 628: Name edit Invalid action parameter/other behaviours if Enter pressed. 

*}
{assign var="managerURL" value="lib/plan/buildEdit.php"}
{assign var="cancelAction" value="lib/plan/buildView.php"}

{lang_get var="labels"
          s="warning,warning_empty_build_name,enter_build,enter_build_notes,active,
             open,builds_description,cancel,release_date,closure_date,closed_on_date,
             copy_tester_assignments, assignment_source_build,show_event_history"}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{$labels.warning}";
var warning_empty_build_name = "{$labels.warning_empty_build_name}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.build_name.value)) 
  {
      alert_message(alert_box_title,warning_empty_build_name);
      selectField(f, 'build_name');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>


<body onload="showOrHideElement('closure_date',{$gui->is_open})">
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" user_feedback=$gui->user_feedback 
         result=$sqlResult item="build"}

<div> 
	<h2>{$gui->operation_descr|escape}
		{if $gui->mgt_view_events eq "yes" && $gui->build_id > 0}
				<img style="margin-left:5px;" class="clickable" 
				     src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" onclick="showEventHistoryFor('{$gui->build_id}','builds')" 
				     alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
		{/if}
	</h2>
	<form method="post" id="create_build" name="create_build" 
	      action="{$managerURL}" onSubmit="javascript:return validateForm(this);">
	      
	<table class="common" style="width:80%">
		<tr>
			<th style="background:none;">{$labels.enter_build}</th>
			<td><input type="text" name="build_name" id="build_name" 
			           maxlength="{#BUILD_NAME_MAXLEN#}" 
			           value="{$gui->build_name|escape}" size="{#BUILD_NAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="build_name"}
			</td>
		</tr>
		<tr><th style="background:none;">{$labels.enter_build_notes}</th>
			<td>{$gui->notes}</td>
		</tr>
		<tr><th style="background:none;">{$labels.active}</th>
		    <td><input type="checkbox"  name="is_active" id="is_active"  
		               {if $gui->is_active eq 1} checked {/if} />
        </td>
		</tr>
    <tr>
		    <th style="background:none;">{$labels.open}</th>
		    <td><input type="checkbox"  name="is_open" id="is_open"  
		               {if $gui->is_open eq 1} checked {/if} 
		               onclick="showOrHideElement('closure_date',this.checked)"/>
            <span id="closure_date" style="display:none;">{$labels.closed_on_date}: {localize_date d=$gui->closed_on_date}</span>
            <input type="hidden" name="closed_on_date" value={$gui->closed_on_date}>
        </td>
		</tr>

    <tr>
		    <th style="background:none;">{$labels.release_date}</th>
		    <td>
		    {html_select_date prefix="release_date_"  time=$gui->release_date
                  month_format='%m' end_year="+1"
                  day_value_format="%02d"
                  all_empty=' ' month_empty= ' '
                  field_order=$gsmarty_html_select_date_field_order}
        </td>
		</tr>

	{* BUGID 3406 *}
	{* show this only if we create a new build and there are other builds to copy from *}
	{if !$gui->build_id && $gui->source_build.build_count}
		<tr>
			<th style="background:none;">{$labels.copy_tester_assignments}</th>
			<td>
				<input type="checkbox"  name="copy_tester_assignments" id="copy_tester_assignments"
				       {if $gui->copy_tester_assignments} checked {/if} 
				       onclick="showOrHideElement('source_build_selection',!this.checked)"
				/>
				<span id="source_build_selection"
				{if !$gui->copy_tester_assignments} style="display:none;" {/if} >
					{$labels.assignment_source_build}
					<select name="source_build_id">
					{html_options options=$gui->source_build.items selected=$gui->source_build.selected}
					</select>
				</span>
			</td>
		</tr>
    {/if}
    
	</table>
	<p>{$labels.builds_description}</p>
	<div class="groupBtn">	

    {* BUGID 628: Name edit Invalid action parameter/other behaviours if Enter pressed. *}
		<input type="hidden" name="do_action" value="{$gui->buttonCfg->name}" />
		<input type="hidden" name="build_id" value="{$gui->build_id}" />
		
		<input type="submit" name="{$gui->buttonCfg->name}" value="{$gui->buttonCfg->value|escape}"
				   onclick="do_action.value='{$gui->buttonCfg->name}'"/>
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: location.href=fRoot+'{$cancelAction}';"/>

	</div>
	</form>
</div>
</div>
</body>
</html>
