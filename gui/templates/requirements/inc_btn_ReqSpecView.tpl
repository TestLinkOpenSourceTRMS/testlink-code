{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_btn_ReqSpecView.tpl,v 1.3 2007/11/29 07:59:00 franciscom Exp $
*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
<div class="groupBtn">
  <form id="req_spec" name="req_spec" action="{$smarty.const.REQ_MODULE}reqSpecEdit.php" method="post">
  	<input type="hidden" name="req_spec_id" value="{$req_spec_id}" />
  	<input type="hidden" name="do_action" value="" />
  	
  	{if $modify_req_rights == "yes"}
  	<input type="submit" name="edit_req_spec" 
  	       value="{lang_get s='btn_edit_spec'}" 
  	       onclick="do_action.value='edit'"/>
  	
  	
  	<input type="button" name="deleteSRS" value="{lang_get s='btn_delete_spec'}"
  	       onclick="delete_confirmation({$req_spec.id},
				                                 '{$req_spec.title|escape:'javascript'}',
				                                 '{$warning_msg}');"	/>


  	<input type="button" name="print_req_spec" value="{lang_get s='btn_print'}"
  		onclick="javascript: window.open('{$basehref}lib/req/reqSpecPrint.php?req_spec_id={$req_spec.id}', 
  		        '_blank','left=100,top=50,fullscreen=no,resizable=yes,toolbar=no,status=no,menubar=no,scrollbars=yes,directories=no,location=no,width=600,height=650');" />
  	<input type="button" name="analyse" value="{lang_get s='btn_analyse'}"
  		onclick="javascript: location.href=fRoot+{$smarty.const.REQ_MODULE}'reqSpecAnalyse.php?req_spec_id={$req_spec.id}';" />
  	{/if}

  	
  	{if $modify_req_rights == "yes"}
  	<br />
  	&nbsp;
  	<br />
  	<input type="button" name="create_req" 
  	       value="{lang_get s='btn_req_create'}"
		       onclick="location='{$req_edit_url}'" />  
  	
  	
	  <input type="button" name="importReq" value="{lang_get s='btn_import'}"
		       onclick="location='{$req_import_url}'" />

  	<input type="button" name="exportReq" value="{lang_get s='btn_export_reqs'}"
		       onclick="location='{$req_export_url}'" />

  	<input type="button" name="reorderReq" value="{lang_get s='req_reorder'}"
		       onclick="location='{$req_reorder_url}'" />

  	<input type="button" name="create_tcases" value="{lang_get s='req_select_create_tc'}"
		       onclick="location='{$req_create_tc_url}'" />

  	{/if}
  </form>
</div>