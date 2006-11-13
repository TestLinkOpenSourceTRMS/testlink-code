{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: buildNew.tpl,v 1.10 2006/11/13 22:24:58 havlat Exp $ *}
{* Purpose: smarty template - Add new build and show existing 

*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_build_1'} ' {$TPname|escape} ' - {lang_get s='title_build_2'}</h1>

{include file="inc_update.tpl" result=$sqlResult item="Build" name=$name}

<div class="workBack">

<div> {* new build form *}
	{if $build_name ne ""}
		<h2>{lang_get s='title_build_update'} ' {$build_name} '</h2>
	{else}
		<h2>{lang_get s='title_build_create'}</h2>
	{/if}
	
	<form method="post">
	<table class="common" style="width:80%">
		<tr><th>{lang_get s='enter_build'}</th></tr>
		<tr>
			<td><input type="text" name="build_name" maxlength="100" value="{$build_name}" size="50"/></td>
		</tr>
		<tr><th>{lang_get s='enter_build_notes'}</th></tr>
		<tr>
			<td>{$notes}</td>
		</tr>
	</table>
	<p>{lang_get s='msg_build'}</p>
	<div class="groupBtn">	
		<input type="submit" name="{$button_name}" value="{$button_value}" />
	</div>
	</form>
</div>
<hr>
<div> {* existing builds *}
	<h2>{lang_get s='title_build_list'}</h2>
  {if $arrBuilds ne ""}
  {lang_get s='warning_delete_build' var="warning_msg" }

	<table class="simple" style="width:80%">
		<tr>
			<th>{lang_get s='th_title'} {$TPname|escape}</th>
			<th>{lang_get s='th_description'}</th>
			<th style="width: 60px;">{lang_get s='th_delete'}</th>
		</tr>
		{foreach item=build from=$arrBuilds}
			<tr>
				<td><a href="lib/plan/buildNew.php?edit_build=load_info&buildID={$build.id}">{$build.name|escape}
					<img alt="{lang_get s='alt_edit_build'}" src="gui/images/icon_edit.png"/></td>
				<td>{$build.notes|truncate:120}</td>
				<td><a href="javascript:deleteBuild_onClick({$build.id},'{$warning_msg}')"><img style="border:none" alt="{lang_get s='alt_delete_build'}" src="icons/thrash.png"/></a></td>
			</tr>
		{/foreach}
	</table>
{else}
	<p>{lang_get s='no_builds'}</p>
{/if}
</div>
<form method="POST" action="lib/plan/buildNew.php" id="deleteBuildForm" onsubmit="return false">
	<input type="hidden" name="buildID" id="buildID">
	<input type="hidden" name="del_build" id="del_build">
</form>
</div>

</body>
</html>