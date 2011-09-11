{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@fielsource inc_btn_reqSpecView.tpl

@internal revisions
20110910 - franciscom - TICKET 4661: Implement Requirement Specification Revisioning for better traceabilility
*}
{lang_get var='labels'
          s='btn_req_create,btn_new_req_spec,btn_export_req_spec,req_select_create_tc,
             btn_import_req_spec,btn_import_reqs,btn_export_reqs,btn_edit_spec,btn_delete_spec,
             btn_print_view,btn_show_direct_link,btn_copy_requirements,btn_copy_req_spec,
             req_spec_operations,req_operations,btn_freeze_req_spec,btn_new_revision,btn_view_history'}
             
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{$urlArgs=$gui->req_spec.id}

<!--- Contents FROM inc_btn_reqSpecView.tpl -->
<div class="groupBtn">
 	<form style="display: inline;" id="req_spec" name="req_spec" 
 		  action="lib/requirements/reqSpecEdit.php" method="post">
		<fieldset class="groupBtn">
			<h2>{$labels.req_spec_operations}</h2>
			<input type="hidden" name="req_spec_id" id="req_spec_id" value="{$gui->req_spec_id}" />
			<input type="hidden" name="req_spec_revision_id" value="{$gui->req_spec_revision_id}" />
			<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />
			<input type="hidden" name="doAction" value="" />
			<input type="hidden" name="log_message" id="log_message" value="" />

  			{if $gui->grants->req_mgmt == "yes"}
  	        	<input type="button" id="btn_new_req_spec" name="btn_new_req_spec"
  	        		   value="{$labels.btn_new_req_spec}"
		           	   onclick="location='{$gui->actions->req_spec_new}'" />  
		           	   
  				<input type="submit" name="edit_req_spec"  value="{$labels.btn_edit_spec}" 
  	         		   onclick="doAction.value='edit'"/>
  				<input type="button" name="deleteSRS" value="{$labels.btn_delete_spec}"
  	       				onclick="action_confirmation('{$urlArgs}','{$gui->req_spec.title|escape:'javascript'|escape}',
                                        			 '{$del_msgbox_title}','{$warning_msg}');"	/>

		    	<input type="button" name="importReqSpec" value="{$labels.btn_import_req_spec}"
		           		onclick="location='{$gui->actions->req_spec_import}'" />

 		    	<input type="button" name="exportReq" value="{$labels.btn_export_req_spec}"
		        	   onclick="location='{$gui->actions->req_spec_export}'" />

            	<input type="button" name="freeze_req_spec" value="{$labels.btn_freeze_req_spec}"
                	   onclick="action_confirmation({$urlArgs},'{$gui->req_spec.title|escape:'javascript'|escape}',
                   								'{$freeze_msgbox_title|escape:'javascript'|escape}', 
                   								'{$freeze_warning_msg|escape:'javascript'|escape}',
                   								pF_freeze_req_spec);"/>

				<input type="button" name="new_revision" id="new_revision" value="{$labels.btn_new_revision}" 
       					onclick="doAction.value='doCreateRevision';javascript:ask4log('req_spec','log_message');"/>

			{/if}
			</fieldset>

	</form>
	
	{* TICKET 4661 *}
	{if $gui->revCount > 1}
		<form id="req_spec_version_compare" name="req_spec_version_compare"
			  method="post" action="lib/requirements/reqSpecCompareRevisions.php">
		  <input type="hidden" name="req_spec_id" value="{$args_reqspec_id}" />
		  <input type="submit" name="compare_versions" value="{$labels.btn_view_history}" />
		</form>
	{/if}
	
	<form id="printerFriendlyContainer" name="printerFriendlyContainer">
	<input type="button" name="printerFriendly" value="{$labels.btn_print_view}"
		   onclick="javascript:openPrintPreview('reqSpec',{$args_reqspec_id},-1,-1,
		                                        'lib/requirements/reqSpecPrint.php?tproject_id={$gui->tproject_id}');"/>
	</form>
	<br>
	
	<form id="req_operations" name="req_operations">
		<fieldset class="groupBtn">
			<h2>{$labels.req_operations}</h2>
	  		{if $gui->grants->req_mgmt == "yes"}
		  	  <input type="button" name="create_req" value="{$labels.btn_req_create}"
			           onclick="location='{$gui->actions->req_edit}'" />  
			           
			    <input type="button" name="importReq" value="{$labels.btn_import_reqs}"
			           onclick="location='{$gui->actions->req_import}'" />
			           
	 		    <input type="button" name="exportReq" value="{$labels.btn_export_reqs}"
			           onclick="location='{$gui->actions->req_export}'" />
	
		      {if $gui->requirements_count > 0}
	  		  	      <input type="button" name="create_tcases" value="{$labels.req_select_create_tc}"
			                   onclick="location='{$gui->actions->req_create_tc}'" />
	        
	  		  	      <input type="button" name="copy_req" value="{$labels.btn_copy_requirements}"
			                   onclick="location='{$gui->actions->req_spec_copy_req}'" />
	    	  {/if}    
		  	{/if}
		</fieldset>
	</form>
</div>