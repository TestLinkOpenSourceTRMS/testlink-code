{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_feedback.tpl,v 1.2 2010/06/14 17:05:36 erikeloff Exp $

Purpose: show feedback after an operation (for example SQL request)
Note: this is replacement for inc_update.tpl (simplified)

INPUT: 
  $user_feedback['type']:
       INFO - succesfull action (default)
       ERROR - (highlighted) some error/problem happens 
                 

  $user_feedback['message']: a localized message
  		empty string disable it
  
 --------------------------------------------------------------------------- *}

{if $user_feedback.message neq ''}
    {if $user_feedback.type === ERROR}
		{assign var="divClass" value="error"}
  	{else}
		{assign var="divClass" value="user_feedback"}
	{/if}
    <div class="{$divClass}">
		<p>{$user_feedback.message|escape}</p>
	</div>
{/if}  {* user_feedback*}
