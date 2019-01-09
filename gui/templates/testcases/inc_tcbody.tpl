{*
TestLink Open Source Project - http://testlink.sourceforge.net/
*}

{$tco = $inc_tcbody_testcase}
<table class="table table-striped table-bordered">
	<thead class="thead-dark">	
		<tr>
			<th class="bold" colspan="{$inc_tcbody_tableColspan}">
        		{$tco.tc_external_id}{$smarty.const.TITLE_SEP}{$tco.name|escape}
        		{$smarty.const.TITLE_SEP_TYPE2}{$inc_tcbody_labels.version|escape}{$tco.version}
        		<img class="clickable" src="{$tlImages.ghost_item}" title="{$inc_tcbody_labels.show_ghost_string}" onclick="showHideByClass('tr','ghostTC');">
        		<img class="clickable" src="{$tlImages.activity}" title="{$inc_tcbody_labels.display_author_updater}" onclick="showHideByClass('tr','time_stamp_creation');">
	  		</th>
		</tr>
		<tr class="ghostTC" style="display:none;">
			<td colspan="{$inc_tcbody_tableColspan}">{$tco.ghost}</td>	
		</tr>
	</thead>
	<tbody>
		{if $inc_tcbody_author_userinfo != ''}  
		<tr class="time_stamp_creation" style="display:none;">
  			<td colspan="{$inc_tcbody_tableColspan}">
          		{$inc_tcbody_labels.title_created}&nbsp;{localize_timestamp ts=$tco.creation_ts}&nbsp;
          		{$inc_tcbody_labels.by}&nbsp;{$inc_tcbody_author_userinfo->getDisplayName()|escape}
  			</td>
    	</tr>
  		{/if}
		{if $tco.updater_id != ''}
		<tr class="time_stamp_creation" style="display:none;">
  			<td colspan="{$inc_tcbody_tableColspan}">
        		{$inc_tcbody_labels.title_last_mod}&nbsp;{localize_timestamp ts=$tco.modification_ts}
    		  	&nbsp;{$inc_tcbody_labels.by}&nbsp;{$inc_tcbody_updater_userinfo->getDisplayName()|escape}
    		</td>
  		</tr>
		{/if}
		<tr>
			<td colspan="{$inc_tcbody_tableColspan}"><p></p></td>
		</tr>
		<tr>
			<th class="bold" colspan="{$inc_tcbody_tableColspan}" style="text-align:left;">{$inc_tcbody_labels.summary}</td>
		</tr>
		<tr>
			<td colspan="{$inc_tcbody_tableColspan}">{if $inc_tcbody_editor_type == 'none'}{$tco.summary|nl2br}{else}{$tco.summary}{/if}<p></td>
		</tr>
		<tr>
			<th class="bold" colspan="{$inc_tcbody_tableColspan}" style="text-align:left;">{$inc_tcbody_labels.preconditions}</td>
		</tr>
    	<tr>
    		<td colspan="{$inc_tcbody_tableColspan}">{if $inc_tcbody_editor_type == 'none'}{$tco.preconditions|nl2br}{else}{$tco.preconditions}{/if}<p></td>
    	</tr>
		{if $inc_tcbody_cf.before_steps_results neq ''}
		<tr>
			<td colspan="{$inc_tcbody_tableColspan}">{$inc_tcbody_cf.before_steps_results}</td>
		</tr>
		{/if}
{if $inc_tcbody_close_table}
</table>
{/if}