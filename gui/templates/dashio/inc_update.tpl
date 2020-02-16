{* 
Testlink Open Source Project - http://testlink.sourceforge.net/

show message after an SQL operation
@filesource	inc_update.tpl

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
                 

  $user_feedback
  
*}

{if $user_feedback neq ''}
    {if $feedback_type != ""}
    	<div class="{$feedback_type}">
  	{else}
     <div class="user_feedback">
  	 {/if}
		{foreach from=$user_feedback item=msg}
			<p>{$msg|escape}</p>
		{/foreach}
     </div>

{else}
  {if $result eq "ok"}
  
    {lang_get s=$action var='action'}
  	{lang_get s=$item var='item'}
  	
    {if $feedback_type eq "soft"}
    	<div class="warning_{$feedback_type}">	
  		<p>{$item|default:"item"} {$name|escape}</p> 
        	<p>{lang_get s='was_success'} {$action|default:"updated"}!</p>
    	</div>
  	{else}
    	<div class="user_feedback">
  	  	<p>{$item|default:"item"} {$name|escape} 
           {lang_get s='was_success'} {$action|default:"updated"}!</p>
  	</div>
    {/if}
    
  
  {elseif $result ne ""}
  
    {if $feedback_type eq "soft"}
  		<div class="warning_{$feedback_type}">	
  		  <p>{lang_get s='warning'}</p> 
  			<p>{$result|escape}</p>
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
{/if}  {* user_feedback*}

{if $result eq "ok" && isset($refresh) && $refresh}
	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}