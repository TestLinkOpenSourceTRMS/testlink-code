{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildEdit.tpl,v 1.10 2008/09/09 10:22:49 franciscom Exp $

Purpose: smarty template - Add new build and show existing

Rev :
     20080217 - francisco.mancardi@gruppotesi.com
     Problems with history.goback, using call to view builds on goback
     
     20071216 - franciscom
     user feedback using ext_js
    
     20070214 - franciscom 
     BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed. 

*}
{assign var="managerURL" value="lib/plan/buildEdit.php"}
{assign var="cancelAction" value="lib/plan/buildView.php"}

{lang_get var="labels"
          s="warning,warning_empty_build_name,enter_build,enter_build_notes,active,
             open,builds_description,cancel"}          

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" editorType=$editorType}
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


<body>
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$main_descr|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" user_feedback=$user_feedback 
         result=$sqlResult item="build"}

<div> 
	<h2>{$operation_descr|escape}
		{if $mgt_view_events eq "yes" && $build_id > 0}
				<img style="margin-left:5px;" class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" onclick="showEventHistoryFor('{$build_id}','builds')" alt="{lang_get s='show_event_history'}" title="{lang_get s='show_event_history'}"/>
		{/if}
	</h2>
	<form method="post" id="create_build" name="create_build" 
	      action="{$managerURL}" onSubmit="javascript:return validateForm(this);">
	      
	<table class="common" style="width:80%">
		<tr>
			<th style="background:none;">{$labels.enter_build}</th>
			<td><input type="text" name="build_name" id="build_name" 
			           maxlength="{#BUILD_NAME_MAXLEN#}" 
			           value="{$build_name|escape}" size="{#BUILD_NAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="build_name"}
			</td>
		</tr>
		<tr><th style="background:none;">{$labels.enter_build_notes}</th>
			<td>{$notes}</td>
		</tr>
		<tr><th style="background:none;">{$labels.active}</th>
		    <td><input type="checkbox"  name="is_active" id="is_active"  
		               {if $is_active eq 1} checked {/if} />
        </td>
		</tr>
    <tr>
		    <th style="background:none;">{$labels.open}</th>
		    <td><input type="checkbox"  name="is_open" id="is_open"  
		               {if $is_open eq 1} checked {/if} />
        </td>
		</tr>

    
	</table>
	<p>{$labels.builds_description}</p>
	<div class="groupBtn">	

    {* BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed. *}
		<input type="hidden" name="do_action" value="{$buttonCfg->name}" />
		<input type="hidden" name="build_id" value="{$build_id}" />
		
		<input type="submit" name="{$buttonCfg->name}" value="{$buttonCfg->value|escape}"
				   onclick="do_action.value='{$buttonCfg->name}'"/>
		<input type="button" name="go_back" value="{$labels.cancel}" 
		       onclick="javascript: location.href=fRoot+'{$cancelAction}';"/>

	</div>
	</form>
</div>
</div>
</body>
</html>
