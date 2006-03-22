{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: buildNew.tpl,v 1.7 2006/03/22 11:56:21 franciscom Exp $ *}
{* Purpose: smarty template - Add new build and show existing 

*}
{include file="inc_head.tpl"}

<body>

{literal}
<script type="text/javascript">
{/literal}
var warning_delete_build = "{lang_get s='warning_delete_build'}";
{literal}
</script>
{/literal}

<h1>{lang_get s='title_create_build_for_tp'} '{$TPname|escape}'</h1>

{include file="inc_update.tpl" result=$sqlResult item="Build" name=$name}

<div class="workBack">

<div> {* new build form *}
	<h2>{lang_get s='title_notes'}</h2>
	<p>{lang_get s='msg_build'}</p>
	<form method="post">

	<table class="common" width="80%">
		<tr>
			<td>{lang_get s='enter_build'}</td>
			<td><input type="text" name="build_name" maxlength="100" value="{$build_name}" size="30"/></td>
		</tr>
		<tr>
			<td>{lang_get s='enter_build_notes'}</td>
			<td width="80%">{$notes}</td>
		</tr>
	</table>
	<div class="groupBtn">	
		<input type="submit" name="{$button_name}" value="{$button_value}" />
	</div>
	</form>
</div>

<div> {* existing builds *}
{if $arrBuilds ne ""}
	<table class="simple" width="80%" style="table-layout:fixed">
		<tr>
			<th>{lang_get s='th_existing_builds'} {$TPname|escape}</th>
			<th>{lang_get s='th_build_notes'}</th>
			<th>{lang_get s='th_delete'}</th>
		</tr>
		{foreach item=build from=$arrBuilds}
			<tr>
				<td><a href="lib/plan/buildNew.php?edit_build=load_info&buildID={$build.id}">{$build.name|escape}</td>
				<td><pre style="display:inline">{$build.notes}</pre></td>
				<td><a href="javascript:deleteBuild_onClick({$build.id})"><img style="border:none" alt="{lang_get s='alt_delete_build'}" src="icons/thrash.png"/></a></td>
			</tr>
		{/foreach}
	</table>
{else}
	{lang_get s='no_builds'}
{/if}
</div>
<form method="POST" action="lib/plan/buildNew.php" id="deleteBuildForm" onsubmit="return false">
	<input type="hidden" name="buildID" id="buildID">
	<input type="hidden" name="del_build" id="del_build">
</form>
</div>

</body>
</html>