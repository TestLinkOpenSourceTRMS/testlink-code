{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource relations.inc.tpl
@used_by 
*}

{lang_get var='rel_labels' 
          s='relation_id, relation_type, relation_status, relation_project,
             relation_set_by, relation_delete, relations, new_relation, by, title_created,can_not_delete_a_frozen_relation,
             in, btn_add, img_title_delete_relation,no_records_found,other_versions,version,
             title_test_case,match_count,warning,can_not_edit_frozen_tc,can_not_delete_relation_frozen_tc,can_not_delete_relation_because_this_is_not_the_latest,
             commit_title,current_direct_link,current_testcase,test_case,relation_set_on,this_tcversion,can_not_delete_relation_tcversion_frozen,can_not_delete_relation_related_tcversion_frozen,
             specific_direct_link,req_does_not_exist,actions,tcase_relation_hint,tcase_relation_help'}


{lang_get s='delete_rel_msgbox_msg' var='delete_rel_msgbox_msg'}
{lang_get s='delete_rel_msgbox_title' var='delete_rel_msgbox_title'}

<script type="text/javascript">
var alert_box_title = "{$rel_labels.warning|escape:'javascript'}";
var delete_rel_msgbox_msg = '{$delete_rel_msgbox_msg|escape:'javascript'}';
var delete_rel_msgbox_title = '{$delete_rel_msgbox_title|escape:'javascript'}';

/**
 * 
 *
 */
function relation_delete_confirmation(item_id, relation_id, title, msg, pFunction) 
{
  var my_msg = msg.replace('%i',relation_id);
  var safe_title = escapeHTML(title);
  Ext.Msg.confirm(safe_title, my_msg,
                  function(btn, text) { 
                    pFunction(btn,text,item_id, relation_id);
                  });
}


/**
 * 
 *
 */
function delete_relation(btn, text, item_id, relation_id)  {
  
  var my_url = "{$gui->delTCVRelationURL}";
  var dummy = my_url.replace('%1',item_id);
  var my_action = dummy.replace('%2',relation_id);

  if( btn == 'yes' ) {
    window.location=my_action;
  }
}

var pF_delete_relation = delete_relation;

</script>
<form method="post" action="{$basehref}lib/testcases/tcEdit.php">
  <input type="hidden" name="doAction" value="doAddRelation" />
      
  <input type="hidden" name="relation_source_tcase_id" id="relation_source_tcase_id"
         value="{$gui->tcase_id}" />

  {* need to check @20220109 - $gui->tcversion_id is 0, is this OK? means latest? *}
  <input type="hidden" name="relation_source_tcversion_id" 
         id="relation_source_tcversion_id" value="{$gui->tcversion_id}" />
             
  <input type="hidden" name="tcase_id" value="{$gui->tcase_id}" />
  <input type="hidden" name="tcversion_id" 
    value="{$gui->tcversion_id}" />

  {if property_exists($gui,'tplan_id') } 
    <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
  {/if}
  {if property_exists($gui,'show_mode') } 
    <input type="hidden" name="show_mode" value="{$gui->show_mode}" />
  {/if}

  {* $tcversion_id inherited  from tcView_viewer.tpl *}
  <table class="simple" id="relations_{$tcversion_id}">
    {if $args_edit_enabled}
      {$canWork = $args_is_latest_tcv == 1 || 
                  $tlCfg->testcase_cfg->addTCVRelationsOnlyOnLatestTCVersion == 0}

      <br>
      {if $canWork}
        <tr><th colspan="7">{$rel_labels.relations} 
          {if $args_relations.num_relations > 0} ({$args_relations.num_relations}) {/if}
        </th></tr>

        {if $gui->add_relation_feedback_msg != ''}
          <tr style="height:40px; vertical-align: middle;">
            <td style="height:40px; vertical-align: middle;" colspan="7">
              <div class="info">{$gui->add_relation_feedback_msg}</div>
            </td>
          </tr>
        {/if}
  		
  		  {if $args_frozen_version == "no"}
            <tr style="height:40px; vertical-align: middle;"><td style="height:40px; vertical-align: middle;" colspan="7">
            
              <span class="bold">{$rel_labels.new_relation}:</span> {$rel_labels.this_tcversion}
                
              <select name="relation_type">
              {html_options options=$gui->relation_domain.items selected=$gui->relation_domain.selected}
              </select>
          
              <input type="text" name="relation_destination_tcase" id="relation_destination_tcase"
                     placeholder="{$rel_labels.tcase_relation_hint}"
                     title="{$rel_labels.tcase_relation_help}"
                     size="{#TC_EXTERNAL_ID_SIZE#}" maxlength="{#TC_EXTERNAL_ID_MAXLEN#}" 
                     onclick="javascript:this.value=''" required />
              
              <input type="submit" name="relation_submit_btn" value="{$rel_labels.btn_add}" />          
              </td>
            </tr>
  		  {/if}

      {/if}
    {/if} {* Relation can be Created *}


    {* Display Existent Relations *}
    {if $args_relations.num_relations > 0}
      
      <tr>
        <th><nobr>{$rel_labels.relation_id} / {$rel_labels.relation_type}</nobr></th>
        <th colspan="1">{$rel_labels.test_case}</th>
        <th><nobr>{$rel_labels.relation_set_by}</nobr></th>
        <th><nobr>&nbsp;</nobr></th>
      </tr>
      

      {foreach item=rx from=$args_relations.relations}
        {$canDel = $args_edit_enabled && $args_frozen_version == 'no' &&
                   $rx.related_tcase.is_open && 
                   $rx.link_status == $smarty.const.LINK_TC_RELATION_OPEN}
        
        {if $canDel == 0}
          {$cannotDelMsg = '...'}      
          {* Build User Feedback Message *}
          {if $args_edit_enabled == 0 }
            {$cannotDelMsg = ''}      
          {else if $rx.link_status != $smarty.const.LINK_TC_RELATION_OPEN}
            {$cannotDelMsg = $rel_labels.can_not_delete_a_frozen_relation}

          {else if $rx.link_status == $smarty.const.LINK_TC_RELATION_OPEN}

            {$cannotDelMsg = 'rop'}      
            {if $args_is_latest_tcv == 0}
              {$cannotDelMsg = 
                $rel_labels.can_not_delete_relation_because_this_is_not_the_latest}
            {/if}

          {else if $args_frozen_version == "yes"}
            {$cannotDelMsg = $rel_labels.can_not_delete_relation_tcversion_frozen}
          {else if $rx.related_tcase.is_open == 0}
            {$cannotDelMsg = $rel_labels.can_not_delete_relation_related_tcversion_frozen}
          {else if $args_is_latest_tcv == 0}
            {$cannotDelMsg = $rel_labels.can_not_delete_relation_because_this_is_not_the_latest}            
          {else}
            {$cannotDelMsg = $rel_labels.can_not_delete_a_frozen_relation}
          {/if}
        {/if}  
        <tr>
          <td class="bold"><nobr>{$rx.id} / {$rx.type_localized|escape}</nobr></td>
          <td>
            <a href="javascript:openTCaseWindow({$rx.related_tcase.testcase_id},{$rx.related_tcase.id})">
            {$rx.related_tcase.fullExternalID|escape}:
            {$rx.related_tcase.name|escape} [{$rel_labels.version} {$rx.related_tcase.version}]</a></td>
          <td><nobr><span title="{$rel_labels.title_created} {$rx.creation_ts} {$rel_labels.by} {$rx.author|escape}">
             {$rx.author|escape}</span></nobr></td>

          
		     {if $canDel}
          <td align="center">
            <a href="javascript:relation_delete_confirmation({$args_relations.item.testcase_id}, {$rx.id}, 
                                                              delete_rel_msgbox_title, delete_rel_msgbox_msg, 
                                                              pF_delete_relation);">
           <img src="{$tlImages.delete}" title="{$rel_labels.img_title_delete_relation}"  style="border:none" /></a>
          </td>
		  {else}
  		  <td align="center">
  			<img style="border:none;" 	alt="{$cannotDelMsg}"
  			   title="{$cannotDelMsg}"	src="{$tlImages.vorsicht}" />
  		  </td>
		  {/if}
        </tr>
      {/foreach}
    {/if}
    
    </table>
    </form>
