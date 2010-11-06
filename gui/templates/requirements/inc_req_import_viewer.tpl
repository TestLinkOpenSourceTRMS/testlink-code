{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_req_import_viewer.tpl,v 1.9 2010/11/06 11:42:47 amkhullar Exp $


rev :

*}
{lang_get var="labels" s='btn_do,check_uncheck_all_checkboxes,th_id,
                          doc_id_short,scope,warning,check_uncheck_children_checkboxes,
                          title,version,assigned_to,assign_to,note_keyword_filter, priority'}

<script type="text/javascript">
//BUGID 3943: Escape all messages (string)
	var check_msg="";
	var alert_box_title = "{$labels.warning|escape:'javascript'}";
{literal}

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
{/literal}

{* prefix for checkbox name ADD*}   
{assign var="add_cb" value="achecked_req"}
  {if $gui->has_items }
   <div class="workBack">
	  {assign var=top_level value=$gui->items[0].level}
	  {foreach from=$gui->items item=rspec key=idx name="div_drawing"}
	    {assign var="div_id" value=div_$idx}
	    {if $div_id != '' }
	      <div id="{$div_id}" style="margin-left:{$rspec.level}0px; border:1;">
        {* check/uncheck on ALL contained Containers is implemented with this clickable image *}
        {if $rspec.req_spec !=''}
	        <h3 class="testlink">
	        {* 
	        <img src="{$smarty.const.TL_THEME_IMG_DIR}/toggle_all.gif"
			                              onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}_","add_value_{$div_id}");'
                                    title="{$labels.check_uncheck_children_checkboxes}" />
          *}
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
          {assign var=next_level value=0}
      {else}
          {assign var=next_level value=$gui->items[$smarty.foreach.div_drawing.iteration].level}
      {/if}
      {if $rspec.level gte $next_level}
          {assign var="max_loop" value=$next_level}
          {assign var="max_loop" value=$rspec.level-$max_loop+1}
          {section name="div_closure" loop=$gui->support_array max=$max_loop} </div> {/section}
      {/if}
      {if $smarty.foreach.div_drawing.last}</div> {/if}
    
    {/if} {* $div_id != '' *}
	{/foreach}
	</div>
  {/if}