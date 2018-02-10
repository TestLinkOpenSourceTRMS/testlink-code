{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_tcbody.tpl,v 1.2 2010/10/24 07:21:23 mx-julian Exp $
viewer for test case in test specification

*}
<table class="simple">
	  <tr>
	  	<th class="bold" colspan="{$inc_tcbody_tableColspan}" style="text-align:left;">
		{$args_testcase.tc_external_id}{$smarty.const.TITLE_SEP}{$args_testcase.name|escape}
		{$smarty.const.TITLE_SEP_TYPE2}{$inc_tcbody_labels.version}{$inc_tcbody_testcase.version|escape}

		
		<img class="clickable" src="{$tlImages.ghost_item}"
             title="{$inc_tcbody_labels.show_ghost_string}"
             onclick="showHideByClass('tr','ghostTC');">

		<img class="clickable" src="{$tlImages.activity}"
             title="{$inc_tcbody_labels.display_author_updater}"
             onclick="showHideByClass('tr','time_stamp_creation');">

	  	</td>
	  </tr>

	  <tr class="ghostTC" style="display:none;">
	  	<td colspan="{$inc_tcbody_tableColspan}">{$inc_tcbody_testcase.ghost}</td>	
	  </tr>
	  <tr class="ghostTC" style="display:none;">
	  	<td colspan="{$inc_tcbody_tableColspan}">&nbsp;</td>	
	  </tr>

	{if $inc_tcbody_author_userinfo != ''}  
	<tr class="time_stamp_creation" style="display:none;">
  		<td colspan="{$inc_tcbody_tableColspan}">
      		{$inc_tcbody_labels.title_created}&nbsp;{localize_timestamp ts=$inc_tcbody_testcase.creation_ts}&nbsp;
      		{$inc_tcbody_labels.by}&nbsp;{$inc_tcbody_author_userinfo->getDisplayName()|escape}
  		</td>
    </tr>
  {/if}
  
 {if $inc_tcbody_testcase.updater_id != ''}
	<tr class="time_stamp_creation" style="display:none;">
  		<td colspan="{$inc_tcbody_tableColspan}">
    		{$inc_tcbody_labels.title_last_mod}&nbsp;{localize_timestamp ts=$inc_tcbody_testcase.modification_ts}
		  	&nbsp;{$inc_tcbody_labels.by}&nbsp;{$inc_tcbody_updater_userinfo->getDisplayName()|escape}
    	</td>
  </tr>
 {/if}
	  <tr><td>&nbsp;</td></tr>

	<tr>
	  <th class="bold" colspan="{$inc_tcbody_tableColspan}" style="text-align:left;">{$inc_tcbody_labels.summary}</td>
	</tr>
	<tr>
		<td colspan="{$inc_tcbody_tableColspan}">{if $inc_tcbody_editor_type == 'none'}{$inc_tcbody_testcase.summary|nl2br}{else}{$inc_tcbody_testcase.summary}{/if}<p></td>
	</tr>

	<tr>
		<th class="bold" colspan="{$inc_tcbody_tableColspan}" style="text-align:left;">{$inc_tcbody_labels.preconditions}</td>
	</tr>
	<tr>
		<td colspan="{$inc_tcbody_tableColspan}">{if $inc_tcbody_editor_type == 'none'}{$inc_tcbody_testcase.preconditions|nl2br}{else}{$inc_tcbody_testcase.preconditions}{/if}<p></td>
	</tr>

	{if $inc_tcbody_cf.before_steps_results neq ''}
	<tr>
	  <td colspan="{$inc_tcbody_tableColspan}">
        {$inc_tcbody_cf.before_steps_results}
      </td>
	</tr>
	{/if}
{if $inc_tcbody_close_table}
</table>
{/if}