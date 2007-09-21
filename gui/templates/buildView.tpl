{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildView.tpl,v 1.4 2007/09/21 06:16:13 franciscom Exp $

Purpose: smarty template - Show existing builds

Rev :
     20070921 - franciscom - BUGID  - added strip_tags|strip to notes
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
{lang_get s='warning_delete_build' var="warning_msg" }
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>{lang_get s='title_build_2'}{$smarty.const.TITLE_SEP_TYPE3}{lang_get s='test_plan'}{$smarty.const.TITLE_SEP}{$tplan_name|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" result=$sqlResult item="build" name=$name}

{* ------------------------------------------------------------------------------------------- *}
<div id="existing_builds">
  {* <h2>{lang_get s='title_build_list'}</h2> *}
  {if $the_builds ne ""}
  	<table class="simple" style="width:80%">
  		<tr>
  			<th>{lang_get s='th_title'}</th>
  			<th>{lang_get s='th_description'}</th>
  			<th>{lang_get s='th_active'}</th>
  			<th>{lang_get s='th_open'}</th>
  			<th>{lang_get s='th_delete'}</th>
  		</tr>
  		{foreach item=build from=$the_builds}
  			<tr>
  				<td><a href="lib/plan/buildNew.php?do_action=edit&amp;build_id={$build.id}"
  				       title="{lang_get s='alt_edit_build'}">{$build.name|escape}
  					     {if $gsmarty_gui->show_icon_edit}
  					         <img style="border:none"
  					              alt="{lang_get s='alt_edit_build'}" 
  					              title="{lang_get s='alt_edit_build'}"
  					              src="{$smarty.const.TL_THEME_IMG_DIR}/icon_edit.png"/>
  					     {/if}    
  					  </a>   
  				</td>
  				<td>{$build.notes|strip_tags|strip|truncate:#BUILD_NOTES_TRUNCATE_LEN#}</td>
  				<td class="clickable_icon">
  				   {if $build.active eq 1} 
  				     <img style="border:none" 
  				            title="{lang_get s='alt_active_build'}" 
  				            alt="{lang_get s='alt_active_build'}" 
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td class="clickable_icon">
  				   {if $build.open eq 1} 
  				     <img style="border:none" 
  				            title="{lang_get s='alt_open_build'}" 
  				            alt="{lang_get s='alt_open_build'}" 
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td class="clickable_icon">
  				  <a href="javascript:deleteBuild_onClick({$build.id},'{$warning_msg}')">
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

<form method="post" action="lib/plan/buildNew.php" 
      id="deleteBuildForm" onsubmit="return false">
	<input type="hidden" name="build_id" id="build_id" />
	<input type="hidden" name="do_action" id="do_action" />
</form>


 <div class="groupBtn">
    <form method="post" action="lib/plan/buildNew.php?do_action=create" id="create_build">
      <input type="submit" name="create_build" value="{lang_get s='btn_build_create'}" />
    </form>
  </div>

</div>

</body>
</html>
