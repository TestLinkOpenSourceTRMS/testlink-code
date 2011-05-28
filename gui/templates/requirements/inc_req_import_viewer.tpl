{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource inc_req_import_viewer.tpl

@internal revisions
*}
{lang_get var="labels" s='btn_do,check_uncheck_all_checkboxes,th_id,
                          doc_id_short,scope,warning,check_uncheck_children_checkboxes,
                          title,version,assigned_to,assign_to,note_keyword_filter, priority'}

<script type="text/javascript">
	var check_msg="";
	var alert_box_title = "{$labels.warning|escape:'javascript'}";

function check_action_precondition(container_id,action)
{
	if(checkbox_count_checked(container_id) <= 0)
	{
		alert_message(alert_box_title,check_msg);
		return false;
	}
	return true;
}
</script>

{* prefix for checkbox name ADD*}   
{$add_cb="achecked_req"}
  {if $gui->has_items }
   <div class="workBack">
	  {$top_level=$gui->items[0].level}
	  {foreach from=$gui->items item=rspec key=idx name="div_drawing"}
	    {$div_id=div_$idx}
	    {if $div_id != '' }
	      <div id="{$div_id}" style="margin-left:{$rspec.level}0px; border:1;">
        {* check/uncheck on ALL contained Containers is implemented with this clickable image *}
        {if $rspec.req_spec !=''}
	        <h3 class="testlink">
          {$rspec.req_spec.doc_id|escape}::{$rspec.req_spec.title|escape}
	        </h3>
        {/if}
        {* used as memory for the check/uncheck all checkbox javascript logic *}
        <input type="hidden" name="add_value_{$div_id}"  id="add_value_{$div_id}"  value="0" />

          {if $rspec.requirements != '' }
            <table cellspacing="0" style="font-size:small;" width="100%">
            {* ---------------------------------------------------------------------------------------------------- *}
			      {* Heading *}
			      <tr style="background-color:#059; font-weight:bold; color:white">
              <td>{$labels.doc_id_short}</td> 
              <td>{$labels.title}</td>
              <td align="center">&nbsp;&nbsp;{$labels.scope}</td>
            </tr>
            {* ---------------------------------------------------------------------------------------------------- *}
            {foreach from=$rspec.requirements item=req key=reqIndex}
              <tr>
            	  <td>
            	  {$req.docid|escape}
            	    	</td>
                    <td>
            	    	{$req.title|escape}
            	    	</td>
                    <td align="center">
            	    		{$req.description|escape}
            	    	</td>
                  </tr>
            {/foreach} 
          </table>
          {/if}

      {if $gui->items_qty eq $smarty.foreach.div_drawing.iteration }
          {$next_level=0}
      {else}
          {$next_level=$gui->items[$smarty.foreach.div_drawing.iteration].level}
      {/if}
      {if $rspec.level gte $next_level}
          {$max_loop=$next_level}
          {$max_loop=$rspec.level-$max_loop+1}
          {section name="div_closure" loop=$gui->support_array max=$max_loop} </div> {/section}
      {/if}
      {if $smarty.foreach.div_drawing.last}</div> {/if}
    
    {/if} {* $div_id != '' *}
	{/foreach}
	</div>
  {/if}