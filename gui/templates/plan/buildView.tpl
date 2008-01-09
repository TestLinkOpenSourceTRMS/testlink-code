{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: buildView.tpl,v 1.2 2008/01/09 07:05:19 franciscom Exp $

Purpose: smarty template - Show existing builds

Rev :
     20071007 - franciscom - delete on click logic refactored 
     20070921 - franciscom - BUGID  - added strip_tags|strip to notes
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='warning_delete_build' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}
<script type="text/javascript" src="{$basehref}gui/javascript/sorttable.js" language="javascript"></script>

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/plan/buildNew.php?do_action=do_delete&build_id=';
</script>
</head>

<body {$body_onload}>

<h1>{lang_get s='title_build_2'}{$smarty.const.TITLE_SEP_TYPE3}{lang_get s='test_plan'} {$tplan_id|escape}{$smarty.const.TITLE_SEP}{$tplan_name|escape}</h1>

<div class="workBack">
{include file="inc_update.tpl" result=$sqlResult item="build"}

{* ------------------------------------------------------------------------------------------- *}
<div id="existing_builds">
  {* <h2>{lang_get s='title_build_list'}</h2> *}
  {if $the_builds ne ""}
  	<table class="simple  sortable" style="width:80%">
  		<tr>
  			<th title="click to sort">{lang_get s='th_title'}</th>
  			<th class="sorttable_nosort">{lang_get s='th_description'}</th>
  			<th class="sorttable_nosort">{lang_get s='th_active'}</th>
  			<th class="sorttable_nosort">{lang_get s='th_open'}</th>
  			<th class="sorttable_nosort">{lang_get s='th_delete'}</th>
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
  				   {if $build.is_open eq 1} 
  				     <img style="border:none" 
  				            title="{lang_get s='alt_open_build'}" 
  				            alt="{lang_get s='alt_open_build'}" 
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/apply_f2_16.png"/>
  				    {else}
  				    &nbsp;        
  				    {/if}
  				</td>
  				<td class="clickable_icon">
				       <img style="border:none;cursor: pointer;" 
  				            title="{lang_get s='alt_delete_build'}" 
  				            alt="{lang_get s='alt_delete_build'}" 
 					            onclick="delete_confirmation({$build.id},'{$build.name|escape:'javascript'}',
 					                                         '{$del_msgbox_title}','{$warning_msg}');"
  				            src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"/>
  				</td>
  			</tr>
  		{/foreach}
  	</table>
  {else}
  	<p>{lang_get s='no_builds'}</p>
  {/if}
</div>
{* ------------------------------------------------------------------------------------------- *}

 <div class="groupBtn">
    <form method="post" action="lib/plan/buildNew.php?do_action=create" id="create_build">
      <input type="submit" name="create_build" value="{lang_get s='btn_build_create'}" />
    </form>
  </div>

	<p>{lang_get s='builds_description'}</p>

</div>

</body>
</html>
