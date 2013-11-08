{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildEdit.tpl,v 1.22 2010/11/06 11:42:47 amkhullar Exp $

Purpose: smarty template - Add new build and show existing

@internal revisions

*}
{assign var="managerURL" value="lib/plan/buildEdit.php"}
{assign var="cancelAction" value="lib/plan/buildView.php"}

{lang_get var="labels"
          s="warning,warning_empty_build_name,enter_build,enter_build_notes,active,
             open,builds_description,cancel,release_date,closure_date,closed_on_date,
             copy_tester_assignments, assignment_source_build,show_event_history,
             show_calender,clear_date"}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$gui->editorType}
{include file="inc_ext_js.tpl" bResetEXTCss=1}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_build_name = "{$labels.warning_empty_build_name|escape:'javascript'}";
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
                 value="{$gui->build_name|escape}" size="{#BUILD_NAME_SIZE#}" required />
                {include file="error_icon.tpl" field="build_name"}
      </td>
    </tr>
    <tr><th style="background:none;">{$labels.enter_build_notes}</th>
      <td>{$gui->notes}</td>
    </tr>

    {* ====================================================================== 
    {if $gui->cfields2 != ''}
    <tr>
      <td  colspan="2">
        <div id="custom_field_container" class="custom_field_container">
        {$gui->cfields}
        </div>
      </td>
    </tr>
    {/if}
    *}

    {if $gui->cfields != ''}
      {foreach key=accessKey item=cf from=$gui->cfields}
      <tr>
        <th style="background:none;">{$cf.label}</th>
        <td>{$cf.input}</td>
      </tr>
      {/foreach}
    {/if}


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
        {* BUGID 3716, BUGID 3930 *}
                <input type="text" 
                       name="release_date" id="release_date" 
               value="{$gui->release_date}" />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('release_date-cal','release_date','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
               onclick="javascript:var x = document.getElementById('release_date'); x.value = '';" >
        <div id="release_date-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
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
