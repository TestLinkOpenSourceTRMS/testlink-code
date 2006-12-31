{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_update.tpl,v 1.12 2006/12/31 16:27:09 franciscom Exp $
Purpose: show message after an SQL operation
*}
{* 
  INPUT: $result (mandatory) = [ok, sql_error_description] 
	 		   If $result is empty do nothing.
	Optional:
	$item = e.g. 'test case'
	$name = name of updated item
	$refresh = [yes] -> refresh tree
	$action = [update (default), add, delete]
            You need to declare a $TLS_<action> variable in 
            the localized strings file, to give a localized feedback
  $feedback_type:
                 used to managed different types of message types.
                 If empty, the traditional (too hard - IMHO - franciscom)
                 message will be displayed.
                 
                 You can see a different effect selecting "soft".
*}

{if $result eq "ok"}

  {lang_get s=$action var='action'}
	{lang_get s=$item var='item'}
	
  {if $feedback_type eq "soft"}
		<div class="warning_{$feedback_type}">	
	  	<p>{$item|default:"item"} {$name|escape} 
      <p>{lang_get s='was_success'} {$action|default:"updated"}!
  	</div>
	{else}
  	<div class="error">
	  	<p>{$item|default:"item"} {$name|escape} 
         {lang_get s='was_success'} {$action|default:"updated"}!</p>
	  </div>
  {/if}
  
  
	{* reload tree *}
	{if $refresh == "yes"}
		{include file="inc_refreshTree.tpl"}
	{/if}

{elseif $result ne ""}

  {if $feedback_type eq "soft"}
		<div class="warning_{$feedback_type}">	
		  <p>{lang_get s='warning'} 
			<p>{$result|escape}
  	</div>
	{else}
  	<div class="error">
      <p>
  		{if $name == ""}
  			{lang_get s='info_failed_db_upd'}
  		{else}
  			{lang_get s='info_failed_db_upd_details'} {$item|default:"item"} {$name|escape}
  		{/if}
      </p>
  		<p>{lang_get s='invalid_query'} {$result|escape}</p>
  	</div>
	{/if}
{/if}
