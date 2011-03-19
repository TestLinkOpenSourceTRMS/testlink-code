{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@fielsource inc_btn_reqSpecView.tpl

@internal revisions

20110319 - franciscom - BUGID 4321: Requirement Spec - add option to print single Req Spec			
20101028 - asimon - BUGID 3954: added contribution by Vincent to freeze requirements
20101022 - eloff - BUGID 3918 - work on buttons
20101007 - asimon - BUGID 3866 - removed analyse button
20100326 - asimon - removed print button
*}
{lang_get var='labels'
          s='btn_req_create,btn_new_req_spec,btn_export_req_spec,
             req_select_create_tc,btn_import_req_spec,btn_import_reqs,
             btn_export_reqs,btn_edit_spec,btn_delete_spec,btn_print_view,
             btn_show_direct_link,btn_copy_requirements,btn_copy_req_spec,
             req_spec_operations, req_operations, btn_freeze_req_spec'}
             
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<!--- inc_btn_reqSpecView.tpl -->
<div class="groupBtn">
	<form id="req_spec" name="req_spec" action="{$req_module}reqSpecEdit.php" method="post">
		<fieldset class="groupBtn">
			<h2>{$labels.req_spec_operations}</h2>
			<input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
			<input type="hidden" name="doAction" value="" />

	{if $gui->grants->req_mgmt == "yes"}
		{if $tlCfg->req_cfg->child_requirements_mgmt == $smarty.const.ENABLED}
  	        <input type="button" name="btn_new_req_spec" value="{$labels.btn_new_req_spec}"
		           onclick="location='{$req_spec_new_url}'" />  
        {/if}
  		<input type="submit" name="edit_req_spec"  value="{$labels.btn_edit_spec}" 
			   onclick="doAction.value='edit'"/>
  		<input type="button" name="deleteSRS" value="{$labels.btn_delete_spec}"
  	       onclick="delete_confirmation({$gui->req_spec.id},'{$gui->req_spec.title|escape:'javascript'|escape}',
                                        '{$del_msgbox_title}','{$warning_msg}');"	/>

		    <input type="button" name="importReqSpec" value="{$labels.btn_import_req_spec}"
		           onclick="location='{$req_spec_import_url}'" />
 		    <input type="button" name="exportReq" value="{$labels.btn_export_req_spec}"
		           onclick="location='{$req_spec_export_url}'" />
            {* BUGID 3954 *}
            <input type="button" name="freeze_req_spec" value="{$labels.btn_freeze_req_spec}"
                   onclick="delete_confirmation({$gui->req_spec.id},'{$gui->req_spec.title|escape:'javascript'|escape}',
                   '{$freeze_msgbox_title|escape:'javascript'|escape}', '{$freeze_warning_msg|escape:'javascript'|escape}',
                   pF_freeze_req_spec);"	/>
	{/if}
	<input type="button" name="printerFriendly" value="{$labels.btn_print_view}"
		   onclick="javascript:openPrintPreview('reqSpec',{$args_reqspec_id},-1,-1,
		                                        'lib/requirements/reqSpecPrint.php');"/>

		</fieldset>

		<fieldset class="groupBtn">
			<h2>{$labels.req_operations}</h2>
  		{if $gui->grants->req_mgmt == "yes"}
	  	  <input type="button" name="create_req" value="{$labels.btn_req_create}"
		           onclick="location='{$req_edit_url}'" />  
		    <input type="button" name="importReq" value="{$labels.btn_import_reqs}"
		           onclick="location='{$req_import_url}'" />
 		    <input type="button" name="exportReq" value="{$labels.btn_export_reqs}"
		           onclick="location='{$req_export_url}'" />

	      {if $gui->requirements_count > 0}
  		  	      <input type="button" name="create_tcases" value="{$labels.req_select_create_tc}"
		                   onclick="location='{$req_create_tc_url}'" />
        
  		  	      <input type="button" name="copy_req" value="{$labels.btn_copy_requirements}"
		                   onclick="location='{$req_spec_copy_req_url}'" />
    	  {/if}    
	  	{/if}

		</fieldset>
	</form>
</div>
