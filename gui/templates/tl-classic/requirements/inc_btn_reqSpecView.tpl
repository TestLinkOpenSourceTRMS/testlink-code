{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@fielsource inc_btn_reqSpecView.tpl

@internal revisions
*}
{lang_get var='labels'
          s='btn_req_create,btn_new_req_spec,btn_export_req_spec,btn_create_from_issue_xml,
             req_select_create_tc,btn_import_req_spec,btn_import_reqs,
             btn_export_reqs,btn_edit_spec,btn_delete_spec,btn_print_view,
             btn_show_direct_link,btn_copy_requirements,btn_copy_req_spec,
             req_spec_operations, req_operations, btn_freeze_req_spec,btn_new_revision,btn_view_history,btn_bulk_mon'}
             
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<!--- inc_btn_reqSpecView.tpl -->
<div class="groupBtn" id="control_panel" style="display:{$tlCfg->gui->op_area_display->req_spec_container};">
  <form style="display: inline;" 
    id="req_spec" name="req_spec" action="{$req_module}reqSpecEdit.php" method="post">
    <fieldset class="groupBtn">
    <h2>{$labels.req_spec_operations}</h2>
    <input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
    <input type="hidden" name="req_spec_revision_id" value="{$gui->req_spec_revision_id}" />
    <input type="hidden" name="doAction" value="" />
    <input type="hidden" name="log_message" id="log_message" value="" />
    <input type="hidden" name="reqMgrSystemEnabled" id="reqMgrSystemEnabled" 
           value="{$gui->reqMgrSystemEnabled}" />
    
      
  {if $gui->grants->req_mgmt == "yes"}
    {if $gui->btn_import_req_spec == ''}
      {$btnImportReqSpec=$labels.btn_import_req_spec}
    {else}
      {$btnImportReqSpec=$gui->btn_import_req_spec}
    {/if}

    {if $tlCfg->req_cfg->child_requirements_mgmt == $smarty.const.ENABLED}
      <input type="button" name="btn_new_req_spec" value="{$labels.btn_new_req_spec}"
             onclick="location='{$req_spec_new_url}'" />  
        {/if}
      <input type="submit" name="edit_req_spec"  value="{$labels.btn_edit_spec}" 
         onclick="doAction.value='edit'"/>
      <input type="button" name="deleteSRS" value="{$labels.btn_delete_spec}"
           onclick="delete_confirmation({$gui->req_spec.id},'{$gui->req_spec.title|escape:'javascript'|escape}',
                                        '{$del_msgbox_title}','{$warning_msg}');"  />

      <input type="button" name="importReqSpec" value="{$btnImportReqSpec}"
             onclick="location='{$req_spec_import_url}'" />
      <input type="button" name="exportReq" value="{$labels.btn_export_req_spec}"
             onclick="location='{$req_spec_export_url}'" />
      <input type="button" name="freeze_req_spec" value="{$labels.btn_freeze_req_spec}"
             onclick="delete_confirmation({$gui->req_spec.id},'{$gui->req_spec.title|escape:'javascript'|escape}',
                   '{$freeze_msgbox_title|escape:'javascript'|escape}', '{$freeze_warning_msg|escape:'javascript'|escape}',
                   pF_freeze_req_spec);"  />
      <input type="button" name="new_revision" id="new_revision" value="{$labels.btn_new_revision}" 
             onclick="doAction.value='doCreateRevision';javascript:ask4log('req_spec','log_message');"/>
  {/if}
  </form>

  {if $gui->revCount > 1}
    <form method="post" 
      action="lib/requirements/reqSpecCompareRevisions.php" name="req_spec_version_compare">
      <input type="hidden" name="req_spec_id" value="{$args_reqspec_id}" />
      <input type="submit" name="compare_versions" value="{$labels.btn_view_history}" />
    </form>
  {/if}
  
  <form>
  <input type="button" name="printerFriendly" value="{$labels.btn_print_view}"
       onclick="javascript:openPrintPreview('reqSpec',{$args_reqspec_id},-1,-1,
                                            'lib/requirements/reqSpecPrint.php');"/>

    </fieldset>
  </form>


  
  <form id="req_operations" name="req_operations">
    <fieldset class="groupBtn">
      <h2>{$labels.req_operations}</h2>
      {if $gui->grants->req_mgmt == "yes"}
        <input type="button" name="create_req" value="{$labels.btn_req_create}"
               onclick="location='{$req_edit_url}'" />  
        <input type="button" name="importReq" value="{$labels.btn_import_reqs}"
               onclick="location='{$req_import_url}'" />
        <input type="button" name="exportReq" value="{$labels.btn_export_reqs}"
               onclick="location='{$req_export_url}'" />

        <input type="button" name="createReqFromIssueXML" value="{$labels.btn_create_from_issue_xml}"
               onclick="location='{$req_create_from_issue_xml_url}'" />

        {if $gui->requirements_count > 0}
          <input type="button" name="create_tcases" 
                 value="{$labels.req_select_create_tc}" onclick="location='{$req_create_tc_url}'" />
        
          <input type="button" name="copy_req" value="{$labels.btn_copy_requirements}"
                 onclick="location='{$req_spec_copy_req_url}'" />

          <input type="button" name="bulk_mon" value="{$labels.btn_bulk_mon}"
                 onclick="location='{$req_spec_bulk_req_mon_url}'" />
 
        {/if}    
      {/if}

    </fieldset>
  </form>
</div>
