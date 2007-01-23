{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildNew.tpl,v 1.17 2007/01/23 09:03:22 franciscom Exp $

Purpose: smarty template - Add new build and show existing

Rev :
     1. added config_load 
     2. added javascript validation for build_name
     3. added title attribute
*}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_build_name = "{lang_get s='warning_empty_build_name'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.build_name.value)) 
  {
      alert(warning_empty_build_name);
      selectField(f, 'build_name');
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

<h1>{lang_get s='title_build_2'}{$smarty.const.TITLE_SEP_TYPE3}{lang_get s='test_plan'}{$smarty.const.TITLE_SEP}{$TPname|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" result=$sqlResult item="build" name=$name}

<div> {* new build form *}
	{if $build_name ne ""}
		<h2>{lang_get s='title_build_update'}{$build_name|escape}</h2>
	{else}
		<h2>{lang_get s='title_build_create'}</h2>
	{/if}
	
	<form method="post" id="create_build" name="create_build" 
	      onSubmit="javascript:return validateForm(this);">
	      
	<table class="common" style="width:80%">
		<tr>
			<th>{lang_get s='enter_build'}</th>
			<td><input type="text" name="build_name" maxlength="{#BUILD_NAME_MAXLEN#}" 
			           value="{$build_name|escape}" size="{#BUILD_NAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="build_name"}
			</td>
		</tr>
		<tr><th>{lang_get s='enter_build_notes'}</th>
			<td>{$notes}</td>
		</tr>
		<tr><th>{lang_get s='active'}</th>
		    <td><input type="checkbox"  name="is_active" id="is_active"  
		               {if $is_active eq 1} checked {/if} />
        </td>
		</tr>
    <tr>
		    <th>{lang_get s='open'}</th>
		    <td><input type="checkbox"  name="is_open" id="is_open"  
		               {if $is_open eq 1} checked {/if} />
        </td>
		</tr>

    
	</table>
	<p>{lang_get s='msg_build'}</p>
	<div class="groupBtn">	
		<input type="submit" name="{$button_name|escape}" value="{$button_value|escape}" />
	</div>
	</form>
</div>
<hr />

{* ------------------------------------------------------------------------------------------- *}
<div id="existing_builds">
  <h2>{lang_get s='title_build_list'}</h2>
  {if $arrBuilds ne ""}
    {lang_get s='warning_delete_build' var="warning_msg" }
  
  	<table class="simple" style="width:80%">
  		<tr>
  			<th>{lang_get s='th_title'} {$TPname|escape}</th>
  			<th>{lang_get s='th_description'}</th>
  			<th style="width: 30px;">{lang_get s='th_active'}</th>
  			<th style="width: 30px;">{lang_get s='th_open'}</th>
  			<th style="width: 60px;">{lang_get s='th_delete'}</th>
  		</tr>
  		{foreach item=build from=$arrBuilds}
  			<tr>
  				<td><a href="lib/plan/buildNew.php?edit_build=load_info&amp;buildID={$build.id}"
  				       title="{lang_get s='alt_edit_build'}">{$build.name|escape}
  					     {if $gsmarty_gui->show_icon_edit}
  					         <img style="border:none"
  					              alt="{lang_get s='alt_edit_build'}" 
  					              title="{lang_get s='alt_edit_build'}"
  					              src="gui/images/icon_edit.png"/>
  					     {/if}    
  					  </a>   
  				</td>
  				<td>{$build.notes|truncate:#BUILD_NOTES_TRUNCATE_LEN#}</td>
  				<td align="center">{if $build.active eq 1} 
  				     <img style="border:none" 
  				            title="{lang_get s='alt_active_build'}" 
  				            alt="{lang_get s='alt_active_build'}" 
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td align="center">{if $build.open eq 1} 
  				     <img style="border:none" 
  				            title="{lang_get s='alt_open_build'}" 
  				            alt="{lang_get s='alt_open_build'}" 
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td align="center"><a href="javascript:deleteBuild_onClick({$build.id},'{$warning_msg}')">
  				       <img style="border:none" 
  				            title="{lang_get s='alt_delete_build'}" 
  				            alt="{lang_get s='alt_delete_build'}" 
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/>
  				   </a>
  				</td>
  			</tr>
  		{/foreach}
  	</table>
  {else}
  	<p>{lang_get s='no_builds'}</p>
  {/if}
</div>
{* ------------------------------------------------------------------------------------------- *}


<form method="POST" action="lib/plan/buildNew.php" id="deleteBuildForm" onsubmit="return false">
	<input type="hidden" name="buildID" id="buildID" />
	<input type="hidden" name="del_build" id="del_build" />
</form>
</div>

</body>
</html>
