{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_tcbody.tpl,v 1.2 2010/10/24 07:21:23 mx-julian Exp $
viewer for test case in test specification

rev:
    20101024 - Julian - BUGID 3928 - Custom fields before steps not using complete width of table
    20100901 - franciscom - display test case body 
*}
<table class="simple">
  {if $inc_tcbody_show_title == "yes"}
	<tr>
		<th colspan="{$inc_tcbody_tableColspan}">
		{$inc_tcbody_testcase.tc_external_id}{$smarty.const.TITLE_SEP}{$inc_tcbody_testcase.name|escape}</th>
	</tr>
  {/if}

	  <tr>
	  	<td class="bold" colspan="{$inc_tcbody_tableColspan}">{$inc_tcbody_labels.version}
	  	{$inc_tcbody_testcase.version|escape}
	  	</td>
	  </tr>
	  
	{if $inc_tcbody_author_userinfo != ''}  
	<tr class="time_stamp_creation">
  		<td colspan="{$inc_tcbody_tableColspan}">
      		{$inc_tcbody_labels.title_created}&nbsp;{localize_timestamp ts=$inc_tcbody_testcase.creation_ts}&nbsp;
      		{$inc_tcbody_labels.by}&nbsp;{$inc_tcbody_author_userinfo->getDisplayName()|escape}
  		</td>
  </tr>
  {/if}
  
 {if $inc_tcbody_testcase.updater_id != ''}
	<tr class="time_stamp_creation">
  		<td colspan="{$inc_tcbody_tableColspan}">
    		{$inc_tcbody_labels.title_last_mod}&nbsp;{localize_timestamp ts=$inc_tcbody_testcase.modification_ts}
		  	&nbsp;{$inc_tcbody_labels.by}&nbsp;{$inc_tcbody_updater_userinfo->getDisplayName()|escape}
    	</td>
  </tr>
 {/if}

	<tr>
		<td class="bold" colspan="{$inc_tcbody_tableColspan}">{$inc_tcbody_labels.summary}</td>
	</tr>
	<tr>
		<td colspan="{$inc_tcbody_tableColspan}">{$inc_tcbody_testcase.summary}</td>
	</tr>

	<tr>
		<td class="bold" colspan="{$inc_tcbody_tableColspan}">{$inc_tcbody_labels.preconditions}</td>
	</tr>
	<tr>
		<td colspan="{$inc_tcbody_tableColspan}">{$inc_tcbody_testcase.preconditions}</td>
	</tr>

	{* 20090718 - franciscom *}
	{if $inc_tcbody_cf.before_steps_results neq ''}
	<tr>
	  {* 20101024 - BUGID 3928 *}
	  <td colspan="{$inc_tcbody_tableColspan}">
        {$inc_tcbody_cf.before_steps_results}
      </td>
	</tr>
	{/if}
{if $inc_tcbody_close_table}	
</table>
{/if}