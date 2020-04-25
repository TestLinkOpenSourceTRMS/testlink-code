{*
TestLink Open Source Project - http://testlink.sourceforge.net/
*}

{$tco = $inc_tcbody_testcase}
<table class="simple">
	<tr>
	  <th class="bold" colspan="{$inc_tcbody_tableColspan}"
      style="text-align:left;">
  		{$tco.tc_external_id}{$smarty.const.TITLE_SEP}{$tco.name|escape}
  		{$smarty.const.TITLE_SEP_TYPE2}{$inc_tcbody_labels.version|escape}{$tco.version}
  		<img class="clickable" src="{$tlImages.ghost_item}"
               title="{$inc_tcbody_labels.show_ghost_string}"
               onclick="showHideByClass('tr','ghostTC');">

  		<img class="clickable" src="{$tlImages.activity}"
           title="{$inc_tcbody_labels.display_author_updater}"
           onclick="showHideByClass('tr','time_stamp_creation');">
  	</th>
	</tr>

	<tr class="ghostTC" style="display:none;">
	  <td colspan="{$inc_tcbody_tableColspan}">{$tco.ghost}</td>	
	</tr>
	<tr class="ghostTC" style="display:none;">
	  <td colspan="{$inc_tcbody_tableColspan}">&nbsp;</td>	
	</tr>

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
</table>
<!-- ------------------------------------------- -->

{if $inc_tcbody_cf.after_title neq ''}
  <div style="padding: 5px 3px 4px 5px;"> 
    <div id="cf_after_title" style="padding: 5px 3px 4px 5px;"
         class="custom_field_container">
      {$inc_tcbody_cf.after_title}
    </div>
  </div  
{/if}

<div class="mainAttrContainer"> 
  <div class="summaryCONTAINER">
    {if $inc_tcbody_cf.before_summary neq ''}
      <div id="cf_before_summary"
            class="custom_field_container">
        {$inc_tcbody_cf.before_summary}
      </div>
      <br>
    {/if}
    <div class="labelHolder">{$inc_tcbody_labels.summary}</div>
    <div>{if $inc_tcbody_editor_type == 'none'}{$tco.summary|nl2br}{else}{$tco.summary}{/if}</div>

    {if $inc_tcbody_cf.after_summary neq ''}
      <br>
      <div id="cf_after_summary"
            class="custom_field_container">
        {$inc_tcbody_cf.after_summary}
      </div>
    {/if}
  </div>

  <div class="spaceOne" style="margin-top:35px;"></div>

  <div class="preconditionsCONTAINER">
    {if $inc_tcbody_cf.before_preconditions neq ''}
      <div id="cf_before_preconditions"
            class="custom_field_container">
        {$inc_tcbody_cf.before_preconditions}
      </div>
      <br>
    {/if}
    <div class="labelHolder">{$inc_tcbody_labels.preconditions}</div>
    <div>{if $inc_tcbody_editor_type == 'none'}{$tco.preconditions|nl2br}{else}{$tco.preconditions}{/if}</div>
    {if $inc_tcbody_cf.after_summary neq ''}
      <br>
      <div id="cf_after_preconditions"
            class="custom_field_container">
        {$inc_tcbody_cf.after_preconditions}
      </div>
    {/if}
  </div>
  
  {if $inc_tcbody_cf.before_steps_results neq ''}
    <div class="CFBeforeStepsCONTAINER">
      <div class="custom_field_container">
        {$inc_tcbody_cf.before_steps_results}
      </div>
    </div>
  {/if}

</div>
<hr>
