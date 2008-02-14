{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildEdit.tpl,v 1.4 2008/02/14 18:06:18 havlat Exp $

Purpose: smarty template - Add new build and show existing

Rev :
    20071216 - franciscom
    user feedback using ext_js
    
    20070214 - franciscom 
    BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed. 

*}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var alert_box_title = "{lang_get s='warning'}";
var warning_empty_build_name = "{lang_get s='warning_empty_build_name'}";
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

<h1>{lang_get s='title_build_2'}{$smarty.const.TITLE_SEP_TYPE3}{lang_get s='test_plan'}{$smarty.const.TITLE_SEP}{$tplan_name|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" user_feedback=$user_feedback 
         result=$sqlResult item="build"}

<div> {* new build form *}
	{if $build_name ne ""}
		<h2>{lang_get s='title_build_edit'}{$smarty.const.TITLE_SEP_TYPE3}{$build_name|escape}</h2>
	{else}
		<h2>{lang_get s='title_build_create'}</h2>
	{/if}
	
	<form method="post" id="create_build" name="create_build" 
	      onSubmit="javascript:return validateForm(this);">
	      
	<table class="common" style="width:80%">
		<tr>
			<th style="background:none;">{lang_get s='enter_build'}</th>
			<td><input type="text" name="build_name" maxlength="{#BUILD_NAME_MAXLEN#}" 
			           value="{$build_name|escape}" size="{#BUILD_NAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="build_name"}
			</td>
		</tr>
		<tr><th style="background:none;">{lang_get s='enter_build_notes'}</th>
			<td>{$notes}</td>
		</tr>
		<tr><th style="background:none;">{lang_get s='active'}</th>
		    <td><input type="checkbox"  name="is_active" id="is_active"  
		               {if $is_active eq 1} checked {/if} />
        </td>
		</tr>
    <tr>
		    <th style="background:none;">{lang_get s='open'}</th>
		    <td><input type="checkbox"  name="is_open" id="is_open"  
		               {if $is_open eq 1} checked {/if} />
        </td>
		</tr>

    
	</table>
	<p>{lang_get s='builds_description'}</p>
	<div class="groupBtn">	

    {* BUGID 628: Name edit – Invalid action parameter/other behaviours if “Enter” pressed. *}
		<input type="hidden" name="do_action" value="{$button_name}" />
		<input type="submit" name="{$button_name}" value="{$button_value|escape}"
				   onclick="do_action.value='{$button_name}'"/>
		<input type="button" name="go_back" value="{lang_get s='cancel'}" onclick="javascript:history.back();"/>

	</div>
	</form>
</div>
</div>
</body>
</html>
