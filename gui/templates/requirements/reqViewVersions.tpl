{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource reqViewVersions.tpl
Purpose: view requirement with version management
         Based on work tcViewer.tpl

@internal revisions
@since 1.9.11
*}

{lang_get s='warning_delete_requirement' var="warning_msg"}
{lang_get s='warning_freeze_requirement' var="freeze_warning_msg"}
{lang_get s='warning_unfreeze_requirement' var="unfreeze_warning_msg"}

{lang_get s='delete' var="del_msgbox_title"}
{lang_get s='freeze' var="freeze_msgbox_title"}
{lang_get s='unfreeze' var="unfreeze_msgbox_title"}

{lang_get s='delete_rel_msgbox_msg' var='delete_rel_msgbox_msg'}
{lang_get s='delete_rel_msgbox_title' var='delete_rel_msgbox_title'}
{lang_get s='warning_empty_reqdoc_id' var='warning_empty_reqdoc_id'}

{lang_get var='labels' 
          s='relation_id, relation_type, relation_document, relation_status, relation_project,
             relation_set_by, relation_delete, relations, new_relation, by, title_created,
             relation_destination_doc_id, in, btn_add, img_title_delete_relation, current_req,
             no_records_found,other_versions,version,title_test_case,match_count,warning,
             revision_log_title,please_add_revision_log,commit_title,current_direct_link,
             specific_direct_link,req_does_not_exist,actions'}


{include file="inc_head.tpl" openHead='yes' jsValidate="yes"} 
{include file="inc_del_onclick.tpl"}

{config_load file="input_dimensions.conf"}

<script type="text/javascript">
// Requirement can not be deleted due to JS error -> label has to be escaped
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var delete_rel_msgbox_msg = '{$delete_rel_msgbox_msg|escape:'javascript'}';
var delete_rel_msgbox_title = '{$delete_rel_msgbox_title|escape:'javascript'}';
var warning_empty_reqdoc_id = '{$warning_empty_reqdoc_id|escape:'javascript'}';
var log_box_title = "{$labels.commit_title|escape:'javascript'}";
var log_box_text = "{$labels.please_add_revision_log|escape:'javascript'}";

Ext.onReady(function(){ 
{foreach from=$gui->log_target key=idx item=item_id}
  tip4log({$item_id});
{/foreach}
});


/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
function delete_req(btn, text, o_id)
{ 
  var my_action=fRoot+'lib/requirements/reqEdit.php?doAction=doDelete&requirement_id=';
  if( btn == 'yes' )
  {
    my_action = my_action+o_id;
    window.location=my_action;
  }
}

/**
 * 
 *
 */
function delete_req_version(btn, text, o_id)
{ 
  var my_action=fRoot+'lib/requirements/reqEdit.php?doAction=doDeleteVersion&req_version_id=';
  if( btn == 'yes' )
  {
    my_action = my_action+o_id;
    window.location=my_action;
  }
}         

/**
 * 
 *
 */
function freeze_req_version(btn, text, o_id)
{
  var my_action=fRoot+'lib/requirements/reqEdit.php?doAction=doFreezeVersion&req_version_id=';
  if( btn == 'yes' )
  {
    my_action = my_action+o_id;
    window.location=my_action;
  }
}

/**
 * 
 *
 */
function unfreeze_req_version(btn, text, o_id)
{
  var my_action=fRoot+'lib/requirements/reqEdit.php?doAction=doUnfreezeVersion&req_version_id=';
  if( btn == 'yes' )
  {
    my_action = my_action+o_id;
    window.location=my_action;
  }
}

/**
 * 
 *
 */
function validate_req_docid_input(input_id, original_value) {

  var input = document.getElementById(input_id);

  if (isWhitespace(input.value) || input.value == original_value) {
      alert_message(alert_box_title,warning_empty_reqdoc_id);
    return false;
  }

  return true;
}

/**
 * 
 *
 */
function delete_req_relation(btn, text, req_id, relation_id) 
{
  var my_action=fRoot + 'lib/requirements/reqEdit.php?doAction=doDeleteRelation&requirement_id='
                     + req_id + '&relation_id=' + relation_id;
  if( btn == 'yes' ) 
  {
    window.location=my_action;
  }
}

/**
 * 
 *
 */
function relation_delete_confirmation(requirement_id, relation_id, title, msg, pFunction) 
{
  var my_msg = msg.replace('%i',relation_id);
  var safe_title = title.escapeHTML();
  Ext.Msg.confirm(safe_title, my_msg,
                  function(btn, text) { 
                    pFunction(btn,text,requirement_id, relation_id);
                  });
}


/**
 * 
 *
 */
function ask4log(fid_prefix,tid_prefix,idx)
{
  var target = document.getElementById(tid_prefix+'_'+idx);
  var my_form = document.getElementById(fid_prefix+'_'+idx);
  Ext.Msg.prompt(log_box_title, log_box_text, function(btn, text){
      if (btn == 'ok')
      {
          target.value=text;
          my_form.submit();
      }
  },this,true);    
  return false;    
} 

/**
 * 
 */
function tip4log(itemID)
{
  var fUrl = fRoot+'lib/ajax/getreqlog.php?item_id=';
  new Ext.ToolTip({
        target: 'tooltip-'+itemID,
        width: 500,
        autoLoad:{ url: fUrl+itemID },
        dismissDelay: 0,
        trackMouse: true
    });
}

/**
 * Be Carefull this TRUST on existence of $gui->delAttachmentURL
 */
function jsCallDeleteFile(btn, text, o_id)
{ 
  var my_action='';
  if( btn == 'yes' )
  {
    my_action='{$gui->delAttachmentURL}'+o_id;
    window.location=my_action;
  }
}        


// **************************************************************************
// VERY IMPORTANT:
// needed to make delete_confirmation() understand we are using a function.
// if I pass delete_req as argument javascript complains.
// **************************************************************************
var pF_delete_req = delete_req;
var pF_delete_req_version = delete_req_version; 
var pF_freeze_req_version = freeze_req_version;
var pF_delete_req_relation = delete_req_relation;
var pF_unfreeze_req_version = unfreeze_req_version;
</script>

<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
{* need by refresh on upload logic used when this template is called while executing *}
{if $gui->bodyOnLoad != ''}
<script language="JavaScript">
var {$gui->dialogName} = new std_dialog('&refreshTree');
</script>  
{/if}


<link rel="stylesheet" type="text/css" href="{$basehref}/third_party/DataTables-1.10.4/media/css/jquery.dataTables.TestLink.css">
<script type="text/javascript" language="javascript" src="{$basehref}/third_party/DataTables-1.10.4/media/js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="{$basehref}/third_party/DataTables-1.10.4/media/js/jquery.dataTables.js"></script>



</head>

{$my_style=""}
{if $gui->hilite_item_name}
  {$my_style="background:#059; color:white; margin:0px 0px 4px 0px;padding:3px;"}
{/if}

{$this_template_dir=$smarty.template|dirname}

<body onLoad="viewElement(document.getElementById('other_versions'),false);{$gui->bodyOnLoad}" onUnload="{$gui->bodyOnUnload}">
<h1 class="title">{$gui->main_descr|escape}{if isset($gui->show_match_count) && $gui->show_match_count} - {$labels.match_count}: {$gui->match_count}{/if}
  {include file="inc_help.tpl" helptopic="hlp_req_view" show_help_icon=true}
</h1>
{if !isset($refresh_tree) }
  {$refresh_tree=false}
{/if}
{include file="inc_update.tpl" user_feedback=$user_feedback refresh=$refresh_tree}

<div class="workBack">

{if isset($gui->current_version)}
{section name=idx loop=$gui->current_version}

  {$reqID=$gui->current_version[idx][0].id}
  {* Current active version *}
  {if $gui->other_versions[idx] neq null}
    {$my_delete_version=true}
  {else}
    {$my_delete_version=false}
  {/if}
  
  {* is it frozen? *}
  {if $gui->current_version[idx][0].is_open}
    {$frozen_version=false}
  {else}
    {$frozen_version=true}
  {/if}
  
  <h2 style="{$my_style}">
  {$tlImages.toggle_direct_link} &nbsp;
  {if $gui->display_path}
      {foreach from=$gui->path_info[$reqID] item=path_part}
        {$path_part|escape} /
      {/foreach}
  {/if}
  <img class="clickable" src="{$tlImages.cog}" onclick="javascript:toogleShowHide('control_panel','inline');"
       title="{$labels.actions}" />

    {if !$gui->show_title }
      {$gui->current_version[idx][0].req_doc_id|escape}:{$gui->current_version[idx][0].title|escape}</h2>
    {/if}
    <div class="direct_link" style='display:none'>
    <a href="{$gui->direct_link}" target="_blank">{$labels.current_direct_link}</a><br/>
    <a href="{$gui->direct_link}&version={$gui->current_version[idx][0].version}" target="_blank">{$labels.specific_direct_link}</a><br/>
    </div>

  {include file="$this_template_dir/reqViewVersionsViewer.tpl" 
           args_req_coverage=$gui->req_coverage
           args_req=$gui->current_version[idx][0] 
           args_gui=$gui
           args_grants=$gui->grants 
           args_can_copy=true
           args_can_delete_req=true
           args_can_delete_version=$my_delete_version
           args_frozen_version=$frozen_version
           args_show_version=true
           args_show_title=$gui->show_title
           args_cf=$gui->cfields_current_version[idx] 
           args_tproject_name=$gui->tproject_name
           args_reqspec_name=$gui->current_version[idx][0]['req_spec_title']}
  
  {$downloadOnly=false}
  {if $gui->grants->req_mgmt != 'yes' || $frozen_version}
    {$downloadOnly=true}
  {/if}
  
  {if !isset($loadOnCancelURL)}
    {$loadOnCancelURL=""}
  {/if} 

  {if $gui->req_cfg->relations->enable && !$frozen_version} {* show this part only if relation feature is enabled *}
  
    {* form to enter a new relation *}
    <form method="post" action="{$basehref}lib/requirements/reqEdit.php" 
        onSubmit="javascript:return validate_req_docid_input('relation_destination_req_doc_id', 
                                                             '{$labels.relation_destination_doc_id}');">
    
    <table class="simple" id="relations">
    
      <tr><th colspan="7">{$labels.relations}</th></tr>
    
      {if $gui->req_add_result_msg}
        <tr style="height:40px; vertical-align: middle;"><td style="height:40px; vertical-align: middle;" colspan="7">
          {$gui->req_add_result_msg}
        </td></tr>
      {/if}
    
      {if $gui->req_relations.rw}
      <tr style="height:40px; vertical-align: middle;"><td style="height:40px; vertical-align: middle;" colspan="7">
      
        <span class="bold">{$labels.new_relation}:</span> {$labels.current_req}
          
        <select name="relation_type">
        {html_options options=$gui->req_relation_select.items selected=$gui->req_relation_select.selected}
        </select>
    
        <input type="text" name="relation_destination_req_doc_id" id="relation_destination_req_doc_id"
               value="{$labels.relation_destination_doc_id}" 
               size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}" 
               onclick="javascript:this.value=''" />
      
        {* show input for testproject only if cross-project linking is enabled *}
        {if $gui->req_cfg->relations->interproject_linking}
            {$labels.relation_project} <select name="relation_destination_testproject_id">
            {html_options options=$gui->testproject_select.items selected=$gui->testproject_select.selected}
            </select>
        {/if} 
        
        <input type="hidden" name="doAction" value="doAddRelation" />
        <input type="hidden" name="relation_source_req_id" value="{$gui->req_id}" />
        <input type="submit" name="relation_submit_btn" value="{$labels.btn_add}" />
        
        </td>
      </tr>
      {/if}     
    {if $gui->req_relations.num_relations}
      
      <tr>
        <th><nobr>{$labels.relation_id}</nobr></th>
        <th><nobr>{$labels.relation_type}</nobr></th>
        
        {if $gui->req_cfg->relations->interproject_linking}
          {assign var=colspan value=1}
        {else}
          {assign var=colspan value=2}
        {/if}
        
        <th colspan="{$colspan}">{$labels.relation_document}</th>
        <th><nobr>{$labels.relation_status}</nobr></th>
        
        {if $gui->req_cfg->relations->interproject_linking}
          <th><nobr>{$labels.relation_project}</nobr></th>
        {/if}
        
        <th><nobr>{$labels.relation_set_by}</nobr></th>
        {if $gui->req_relations.rw}
        <th><nobr>{$labels.relation_delete}</nobr></th>
        {/if}
      </tr>
      
      {foreach item=relation from=$gui->req_relations.relations}
      {assign var=status value=$relation.related_req.status}
        <tr>
          <td>{$relation.id}</td>
          <td class="bold"><nobr>{$relation.type_localized|escape}</nobr></td>
          <td colspan="{$colspan}"><a href="javascript:openLinkedReqWindow({$relation.related_req.id})">
            {$relation.related_req.req_doc_id|escape}:
            {$relation.related_req.title|escape}</a></td>
          <td><nobr>{$gui->reqStatus.$status|escape}</nobr></td>
          
          {* show related testproject name only if cross-project linking is enabled *}
          {if $gui->req_cfg->relations->interproject_linking}
            <td><nobr>{$relation.related_req.testproject_name|escape}</nobr></td>
          {/if}
          
          <td><nobr><span title="{$labels.title_created} {$relation.creation_ts} {$labels.by} {$relation.author|escape}">
            {$relation.author|escape}</span></nobr></td>

          <td align="center">
          {if $gui->req_relations.rw}
                <a href="javascript:relation_delete_confirmation({$gui->req_relations.req.id}, {$relation.id}, 
                                                                 delete_rel_msgbox_title, delete_rel_msgbox_msg, 
                                                                 pF_delete_req_relation);">
                  <img src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png" 
                       title="{$labels.img_title_delete_relation}"  style="border:none" /></a>
                  {/if}
                </td>
        </tr>
      {/foreach}
            
    {/if}
    
    </table>
    </form>
  
  {/if}
  
  {* end req relations *}

  {include file="$this_template_dir/reqMonitors.tpl"} 
         
  {include file="attachments.inc.tpl" 
             attach_id=$reqID  
             attach_tableName=$gui->attachmentTableName
             attach_attachmentInfos=$gui->attachments[$reqID]  
             attach_downloadOnly=$downloadOnly
             attach_loadOnCancelURL=$loadOnCancelURL}
             
  {* Other Versions *}
    {if $gui->other_versions[idx] neq null}
        {$vid=$gui->current_version[idx][0].id}
        {$div_id="vers_$vid"}
        {$memstatus_id="mem_$div_id"}
  
        {include file="inc_show_hide_mgmt.tpl" 
                 show_hide_container_title=$labels.other_versions
                 show_hide_container_id=$div_id
                 show_hide_container_draw=false
                 show_hide_container_class='exec_additional_info'
                 show_hide_container_view_status_id=$memstatus_id}
               
        <div id="vers_{$vid}" class="workBack">
        
        {foreach from=$gui->other_versions[idx] item=my_req key=rdx}
            {$version_num=$my_req.version}
            {$title=$labels.version}
            {$title="$title $version_num"}
            {$div_id="v_$vid"}
            {$sep="_"}
            {$div_id="$div_id$sep$version_num"}
            {$memstatus_id="mem_$div_id"}

            {if $my_req.is_open}
              {$frozen_version=false}
            {else}
              {$frozen_version=true}
            {/if}
           
            {include file="inc_show_hide_mgmt.tpl" 
                     show_hide_container_title=$title
                     show_hide_container_id=$div_id
                     show_hide_container_draw=false
                     show_hide_container_class='exec_additional_info'
                     show_hide_container_view_status_id=$memstatus_id}
              <div id="{$div_id}" class="workBack">
              {include file="$this_template_dir/reqViewVersionsViewer.tpl" 
                       args_req_coverage=$gui->req_coverage
                       args_req=$my_req 
                       args_gui=$gui
                       args_grants=$gui->grants 
                       args_can_copy=false
                       args_can_delete_req=false
                       args_can_delete_version=true
                       args_frozen_version=$frozen_version
                       args_show_version=true 
                       args_show_title=true
                       args_cf=$gui->cfields_other_versions[idx][$rdx]}
             </div>
             <br />
             
        {/foreach}
        </div>
  
        {* ---------------------------------------------------------------- *}
        {* Force the div of every old version to show closed as first state *}
        <script type="text/javascript">
        viewElement(document.getElementById('vers_{$vid}'),false);
        {foreach from=$gui->other_versions[idx] item=my_req}
          viewElement(document.getElementById('v_{$vid}_{$my_req.version}'),false);
        {/foreach}
        </script>
        {* ---------------------------------------------------------------- *}
    {/if}
    <br>
{/section}
{else}
  {if $gui->reqHasBeenDeleted}
    {$labels.req_does_not_exist}
  {else}
    {$labels.no_records_found}
  {/if}
{/if}

{if isset($gui->refreshTree) && $gui->refreshTree}
  {include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</div>
</body>
</html>