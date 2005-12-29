{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_update.tpl,v 1.8 2005/12/29 20:59:00 schlundus Exp $ *}
{* Purpose: smarty template - show SQL update result *}
{* INPUT: $result (mandatory) = [ok, sql_error_description] 
			If $result is empty do nothing.
	Optional:
	$item = e.g. 'test case'
	$name = name of updated item
	$refresh = [yes] 
	$action = [update (default), add, delete]
	
*}
{*
 20051112 - scs - changed "Test Suite" to "TestSuite"
*}

{if $result eq "ok"}

{* 20050508 - fm - refactored 
   need to declare a $TLS_<action> variable in
   the localized strings file.
    add actions below (for automatic detection)
	lang_get('update');
	lang_get('updated');
	lang_get('add');
	lang_get('added');
	lang_get('assigned');
	lang_get('delete');
	lang_get('deleted');
	
	lang_get('item');
	lang_get('user');
	lang_get('TestPlan');
	lang_get('TestCase');
	lang_get('TestSuite')
	lang_get('Priority');
	lang_get('component');
	lang_get('category');
	lang_get('Product');	
	lang_get('Keyword');
	lang_get('Build');
	lang_get('Requirements');
	lang_get('Requirement');
	lang_get('Milestone');
*}
    {lang_get s=$action var='action'}
	{lang_get s=$item var='item'}
	
	<div class="error">
		<p>{$item|default:"item"} {$name|escape} 
       {lang_get s='was_success'} {$action|default:"updated"}!</p>
	</div>

	{* reload tree *}
	{if $refresh == "yes"}
		{include file="inc_refreshTree.tpl"}
	{/if}

{elseif $result ne ""}
	<div class="error">
    <p>
		{if $name == ""}
			{lang_get s='info_failed_db_upd'}
		{else}
			{lang_get s='info_failed_db_upd_details'} {$item|default:"item"} {$name|escape}
		{/if}
    </p>
		<p>{lang_get s='invalid_query'} {$result|escape}<p>
	</div>
{/if}
