{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerMove.tpl,v 1.4 2005/10/13 19:26:35 schlundus Exp $ *}
{* Purpose: smarty template - form for move/copy container in test specification 

20050825 - fm - moveCopy -> containerID
20051013 - am - fix for 115
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
<div class="workBack">

<h1>{lang_get s='title_move_cp'}  {$level|capitalize|escape}</h1>

{if $arraySelect eq ''}
	{lang_get s='sorry_further'} {$parent}s {lang_get s='defined_exclam'} 
{else}
	<form method="post" action="lib/testcases/containerEdit.php?objectID={$objectID|escape}">
		<div>
			<input type="submit" name="{$level|escape}Move" value="{lang_get s='btn_move'}" />
			<input type="submit" name="{$level|escape}Copy" value="{lang_get s='btn_cp'}" />
			<input type="hidden" name="old_containerID" value="{$old_containerID}" />
		</div>	
		<p>{lang_get s='cont_move_first'} {$level|escape} {lang_get s='cont_move_second'} {$parent|escape}.</p>
		<p>{lang_get s='choose_target'} {$parent|escape}:
			<select name="containerID">
				{section name=oneKey loop=$arraySelect}
					<option name="{$arraySelect[oneKey][1]}" value="{$arraySelect[oneKey][0]}">{$arraySelect[oneKey][1]|escape}</option>
				{/section}
			</select>
		</p>
		<p>
			<input type="checkbox" name="nested" checked="checked" value="yes" />
			{lang_get s='include_nested'}
		</p>
		<p>
			<input type="checkbox" name="copyKeywords" checked="checked" value="1" />
			{lang_get s='copy_keywords'}
		</p>
	</form>
{/if}
</div>
</body>
</html>