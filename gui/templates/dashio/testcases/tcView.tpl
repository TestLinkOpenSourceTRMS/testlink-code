{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource tcView.tpl
Purpose: smarty template - view test case in test specification

*}

{config_load file="input_dimensions.conf"}
{lang_get var='labels' 
          s='no_records_found,other_versions,show_hide_reorder,version,title_test_case,match_count,actions,
             file_upload_ko'}

{* Configure Actions *}
{$showMode=$gui->show_mode}

{* Context is CRITIC *}
{$tplanID=intval($gui->tplan_id)}
{$tprojID=intval($gui->tproject_id)}

{$deleteStepAction = "lib/testcases/tcEdit.php?show_mode=$showMode&doAction=doDeleteStep"}
{$deleteStepAction = "$deleteStepAction&tplan_id=$tplanID&tproject_id=$tprojID\&step_id="}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action = fRoot+'{$deleteStepAction}';

function jsCallDeleteFile(btn, text, o_id) { 
  var my_action='';
  if( btn == 'yes' ) {
    my_action='{$gui->delAttachmentURL}'+o_id;
    window.location=my_action;
  }
}         
</script>

{include file="inc_ext_js.tpl" css_only=1}

{* CRITIC:
   needed by refresh on upload logic used when this template 
   is called to edit a test case while executing it.
*}
{if $gui->bodyOnLoad != ''}
  <script language="JavaScript">
  var urlP ='';
  {if '' != $gui->additionalURLPar}
    urlP = '{$gui->additionalURLPar}';
  {/if}
  var addStr = '&onTemplate=tcView&refreshTree=0' + urlP;
  var {$gui->dialogName} = new std_dialog(addStr);
  </script>  
{/if}


{include file="bootstrap.inc.tpl"}
<script src="{$basehref}third_party/bootbox/bootbox.all.min.js"></script>
</head>

{$my_style=""}
{if $gui->hilite_testcase_name}
  {$my_style="background:#059; color:white; margin:0px 0px 4px 0px;padding:3px;"}
{/if}

<body onLoad="viewElement(document.getElementById('other_versions'),false);{$gui->bodyOnLoad}" onUnload="{$gui->bodyOnUnload}">
<h1 class="{#TITLE_CLASS#}">{$gui->pageTitle}{if $gui->show_match_count} - {$labels.match_count}:{$gui->match_count}{/if}
</h1>

{if $gui->uploadOp != null }
  <script>
  var uplMsg = "{$labels.file_upload_ko}<br>";
  var doAlert = false;
  {if $gui->uploadOp->statusOK == false}
    uplMsg += "{$gui->uploadOp->msg}<br>";
    doAlert = true;
  {/if}
  if (doAlert) {
    bootbox.alert(uplMsg);
  }
  </script>
{/if}
{include file="inc_update.tpl" user_feedback=$gui->user_feedback refresh=$gui->refreshTree}
<div class="workBack">

{if $gui->tc_current_version}
  {section name=idx loop=$gui->tc_current_version}
    {$tcID = $gui->tc_current_version[idx][0].testcase_id}
    {$tcVersionID = $gui->tc_current_version[idx][0].id}
    {$hasBeenExecuted = false}
    {if $gui->status_quo[idx][$tcVersionID].executed != '' }
      {$hasBeenExecuted = true}
    {/if}

    {* Current active version *}
    {$my_delete_version="no"}
    {if $gui->testcase_other_versions[idx] neq null}
      {$my_delete_version="yes"}
    {/if}

    {* is it frozen? *}
    {$frozen_version="yes"}
    {if $gui->tc_current_version[idx][0].is_open == 1}
      {$frozen_version="no"}
    {/if}

    
    {$tlIMGTags.toggle_direct_link} &nbsp;

    {if $gui->display_testcase_path}
      {foreach from=$gui->path_info[$tcID] item=path_part}
          {$path_part|escape} /
      {/foreach}
    {/if}

    <i class="fa fa-cog" aria-hidden="true"
      onclick="javascript:toogleShowHide('tcView_viewer_tcase_control_panel_{$tcVersionID}','inline');"
      title="{$labels.actions}">
    </i>
    

      <div class="direct_link" style='display:none'><a href="{$gui->direct_link}" target="_blank">{$gui->direct_link}</a></div>

      {include file="{$tplConfig['tcViewViewer.inc']}"  
              args_aliens_map = $gui->currentVersionAliens
              args_tcase_operations_enabled="yes"
              args_read_only="no"
              args_can_move_copy="yes"
              args_can_delete_testcase="yes" 
              args_show_version="yes" 
              args_hide_relations="no"
              args_new_sibling="yes"
              args_bulk_action="yes"
              args_tcversion_operation_only_edit_button="no"


              args_testcase=$gui->tc_current_version[idx][0]
              args_status_quo=$gui->status_quo[idx]

              args_keywords_map = $gui->currentVersionKeywords 
              args_platforms_map = $gui->currentVersionPlatforms 
              args_reqs = $gui->req4current_version 
              args_relations = $gui->relations[idx]

              args_can_do=$gui->can_do
              args_frozen_version=$frozen_version
              args_can_delete_version=$my_delete_version

              args_show_title=$gui->show_title
              args_activate_deactivate_name='activate'
              args_activate_deactivate='bnt_activate'
              args_cf=$gui->cf_current_version[idx] 
              args_tcase_cfg=$gui->tcase_cfg
              args_users=$gui->users
              args_tproject_name=$gui->tprojectName
              args_tsuite_name=$gui->parentTestSuiteName
              args_linked_versions=$gui->linked_versions[idx]
              args_has_testplans=$gui->has_testplans}
      


      {* If version is FROZEN, you can only download *}
      {$bDownloadOnly=false}
      {if $gui->can_do->edit != 'yes' || $frozen_version == 'yes'}
        {$bDownloadOnly=true}
      {/if}

      {* Has Been Executed? *}
      {if $hasBeenExecuted  &&
          $tlCfg->testcase_cfg->downloadOnlyAfterExec == TRUE}
        {$bDownloadOnly=true}
      {/if}
  
      
      {if !isset($gui->loadOnCancelURL)}
        {$loadOnCancelURL=""}
      {/if} 

    {include file="attachments.inc.tpl" 
            attach_attachmentInfos=$gui->attachments[$tcVersionID]  
            attach_downloadOnly=$bDownloadOnly
            attach_uploadURL={$gui->fileUploadURL[$tcVersionID]}
            attach_loadOnCancelURL=$gui->loadOnCancelURL}
    
    {* Other Versions *}
    {if 'editOnExec' != $gui->show_mode && $gui->testcase_other_versions[idx] neq null}
          {$vid=$gui->tc_current_version[idx][0].id}
          {$div_id="vers_$vid"}
          {$memstatus_id="mem_$div_id"}
          <br />
          {include file="inc_show_hide_mgmt.tpl" 
                  show_hide_container_title=$labels.other_versions
                  show_hide_container_id=$div_id
                  show_hide_container_draw=false
                  show_hide_container_class='exec_additional_info'
                  show_hide_container_view_status_id=$memstatus_id}
                
          <div id="vers_{$vid}" class="workBack">

          {foreach from=$gui->testcase_other_versions[idx] item=my_testcase key=tdx}
    
            {$tcversion_id=$my_testcase.id}
            
            {$thisVersionIsExecuted = false}
            {if $gui->status_quo[idx][$tcversion_id].executed != '' }
              {$thisVersionIsExecuted = true}
            {/if}

            {$cfOtherVersion = null}
            {if $gui->cf_other_versions != null}
              {$cfOtherVersion = $gui->cf_other_versions[idx][$tdx]}
            {/if}



            {$version_num=$my_testcase.version}
            {$title=$labels.version}
            {$title="$title $version_num"}

            {$tcv_frozen_version="no"}
            {if $my_testcase.is_open == 0}
              {$tcv_frozen_version="yes"}
            {/if}
        
            {$sep="_"}
            {$div_id="v_$vid"}
            {$div_id="$div_id$sep$version_num"}
            {$memstatus_id="mem_$div_id"}
            {include file="inc_show_hide_mgmt.tpl" 
                    show_hide_container_title=$title
                    show_hide_container_id=$div_id
                    show_hide_container_draw=false
                    show_hide_container_class='exec_additional_info'
                    show_hide_container_view_status_id=$memstatus_id}
                      
                <div id="{$div_id}" class="workBack">
                {*
                BE CAREFUL
                args_cf=$gui->cf_other_versions[idx][tdx]  - KO
                args_cf=$gui->cf_other_versions[$idx][$tdx]  - KO
                args_cf=$gui->cf_other_versions[$idx][tdx]  - KO
                args_cf=$gui->cf_other_versions[idx][$tdx] - OK 
                - do not know if there is info on smarty manuals
                *}

                <img class="clickable" src="{$tlImages.cog}" 
                  onclick="javascript:toogleShowHide('tcView_viewer_tcase_control_panel_{$tcversion_id}','inline');"
                  title="{$labels.actions}" />

                {* Setting args_can_do makes other versions READONLY *}
                {* Be carefull IDX is OK ONLY for status_quo *}
                {include file="{$tplConfig['tcViewViewer.inc']}" 
                        
                  args_tcase_cfg=$gui->tcase_cfg
                  args_read_only=$tcv_frozen_version

                  args_can_move_copy="no" 
                  args_can_delete_testcase='no'
                  args_can_delete_version="yes"
                  args_hide_relations="no"
                  args_show_version="no" 
                  args_show_title="no"
                  args_new_sibling="no"
                  args_bulk_action="no"
                  args_tcase_operations_enabled="no"

                  args_testcase = $my_testcase 

                  args_status_quo = $gui->status_quo[idx]

                  args_keywords_map = $gui->otherVersionsKeywords[$tdx] 
                  
                  args_platforms_map = $gui->otherVersionsPlatforms[$tdx] 

                  args_aliens_map = $gui->otherVersionsAliens[$tdx] 

                  args_reqs = $gui->req4OtherVersions[$tdx]
                  args_relations = $gui->otherVersionsRelations[$tdx]

                  args_can_do=$gui->can_do
                  args_frozen_version=$tcv_frozen_version

                  args_users=$gui->users
                  args_cf = $cfOtherVersion
                  args_linked_versions=null
                  args_has_testplans=$gui->has_testplans}


                {$downloadOnly = false} 
                {if $thisVersionIsExecuted && 
                    $tlCfg->testcase_cfg->downloadOnlyAfterExec == TRUE}
                  {$downloadOnly=true}
                {/if}

                {if $tcv_frozen_version=="yes"}
                  {$downloadOnly = true} 
                {/if}
                {include file="attachments.inc.tpl" 
                        attach_attachmentInfos=$gui->attachments[$tcversion_id]  
                        attach_downloadOnly=$downloadOnly
                        attach_uploadURL=$gui->fileUploadURL[$tcversion_id]
                        attach_loadOnCancelURL=$gui->loadOnCancelURL}

              </div>
              <br />
              
          {/foreach}
          </div>
    
          {* ---------------------------------------------------------------- *}
          {* Force the div of every old version to show closed as first state *}
          <script type="text/javascript">
            viewElement(document.getElementById('vers_{$vid}'),false);
            {foreach item=my_testcase from=$gui->testcase_other_versions[idx]}
              viewElement(document.getElementById('v_{$vid}_{$my_testcase.version}'),false);
            {/foreach}
          </script>
          {* ---------------------------------------------------------------- *}
      {/if}
      <br>
  {/section}
{else}
  {if isset($gui->warning_msg) }
    {$gui->warning_msg}
  {else}
    {$labels.no_records_found}
  {/if}
{/if}

</div>
{if $gui->refreshTree}
  {include file="inc_refreshTreeWithFilters.tpl"}
{/if}
</body>
</html>