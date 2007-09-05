{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: containerMove.tpl,v 1.11 2007/09/05 06:04:43 franciscom Exp $
Purpose: smarty template - form for move/copy container in test specification 

rev :
     20070904 - franciscom - BUGID 1019
     removed checkbox copy nested data
*}
{include file="inc_head.tpl"}
{if $level == 'category'}
	{assign var='parent' value='component'}
{elseif $level == 'component'}
	{assign var='parent' value='product'}
{else}
	{assign var='parent' value='container'}
{/if}
<body>
{lang_get s=$level var=level_translated}
<h1>{$level_translated}{$smarty.const.TITLE_SEP}{$object_name|escape} </h1>

<div class="workBack">
<h1>{lang_get s='title_move_cp'}</h1>

{if $arraySelect eq ''}
	{lang_get s='sorry_further'} {$parent}s {lang_get s='defined_exclam'} 
{else}
	<form method="post" action="lib/testcases/containerEdit.php?objectID={$objectID|escape}">
		<p>
		{lang_get s='cont_move_first'} {$level_translated} {lang_get s='cont_move_second'} {$parent|escape}.<br>
		{lang_get s='cont_copy_first'} {$level_translated} {lang_get s='cont_copy_second'} {$parent|escape}.
		</p>
		<p>{lang_get s='choose_target'} {$parent|escape}:
			<select name="containerID">
				{html_options options=$arraySelect}
			</select>
		</p>
	
		<p>
			<input type="checkbox" name="copyKeywords" checked="checked" value="1" />
			{lang_get s='copy_keywords'}
		</p>

		<div>
			<input type="submit" name="do_move" value="{lang_get s='btn_move'}" />
			<input type="submit" name="do_copy" value="{lang_get s='btn_cp'}" />
			<input type="hidden" name="old_containerID" value="{$old_containerID}" />
		</div>	

	</form>
{/if}
</div>
</body>
</html>