{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	reqViewVersionsViewer.tpl
viewer for requirement

@internal revisions
@since 1.9.4
20111029 - franciscom - TICKET 4786: Add right to allow UNFREEZE a requirement
20110817 - franciscom - TICKET 4702: Requirement View - display log message - image added
20110816 - franciscom - TICKET 4702: Requirement View - display log message

@since 1.9.3
20110530 - asimon - BUGID 4298: added functionality for direct links to open specific requirement versions
20110308 - asimon - BUGID 4273: backported printing of single req from master
20101127 - franciscom - BUGID 4056: Requirement Revisioning
20101119 - asimon - BUGID 4038: clicking requirement link does not open req version
20101111 - asimon - replaced openTCaseWindow() by openTCEditWindow() to save popup size
*}
{lang_get var="labels"
          s="requirement_spec,Requirements,scope,status,type,expected_coverage,  
             coverage,btn_delete,btn_cp,btn_edit,btn_del_this_version,btn_new_version,
             btn_del_this_version, btn_freeze_this_version, version, can_not_edit_req,
             testproject,title_last_mod,title_created,by,btn_compare_versions,showing_version,
             revision,btn_view_history,btn_new_revision,btn_print_view,specific_direct_link,
             design,execution_history,btn_unfreeze_this_version"}

{assign var="hrefReqSpecMgmt" value="lib/general/frmWorkArea.php?feature=reqSpecMgmt"}
{assign var="hrefReqSpecMgmt" value=$basehref$hrefReqSpecMgmt}

{assign var="hrefReqMgmt" value="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
{assign var="hrefReqMgmt" value=$basehref$hrefReqMgmt}

{assign var="module" value='lib/requirements/'}
{assign var="req_id" value=$args_req.id}
{assign var="req_version_id" value=$args_req.version_id}

{if $args_show_title }
    {if isset($args_tproject_name) && $args_tproject_name != ''}
     <h2>{$labels.testproject} {$args_tproject_name|escape} </h2>
    {/if}
    {if $args_req_spec_name != ''}
     <h2>{$labels.req_spec} {$args_req_spec_name|escape} </h2>
    {/if}
	  <h2>{$tlImages.toggle_direct_link} &nbsp; {$labels.title_test_case} {$args_req.title|escape} </h2>
	  <div class="direct_link" style='display:none'>
		  <a href="{$gui->direct_link}&version={$args_req.version}" target="_blank">{$labels.specific_direct_link}</a><br/>
	  </div>
{/if}
{assign var="warning_edit_msg" value=""}

<div style="display: inline;" class="groupBtn">
{if $args_grants->req_mgmt == "yes"}
	  <form style="display: inline;" id="reqViewF_{$req_version_id}" name="reqViewF_{$req_version_id}" 
	        action="lib/requirements/reqEdit.php" method="post">
	  	<input type="hidden" name="requirement_id" value="{$args_req.id}" />
	  	<input type="hidden" name="req_version_id" value="{$args_req.version_id}" />
	  	<input type="hidden" name="doAction" value="" />
	  	
	  	{* IMPORTANT NOTICE: name can not be dynamic becasue PHP uses name not ID *}
	  	<input type="hidden" name="log_message" id="log_message_{$req_version_id}" value="" />
	  	
	  	
	  	{if $args_frozen_version eq null}
	  	<input type="submit" name="edit_req" value="{$labels.btn_edit}" onclick="doAction.value='edit'"/>
	  	{/if}
	  	
	  	{* BUGID 4588 - If more than one version is displayed show "Delete" and "Delete this Version" button*}
	  	{if $args_can_delete_req && !$gui->version_option}
	  	<input type="button" name="delete_req" value="{$labels.btn_delete}"
	  	       onclick="delete_confirmation({$args_req.id},
	  	                                    '{$args_req.req_doc_id|escape:'javascript'|escape}:{$args_req.title|escape:'javascript'|escape}',
	  				                              '{$del_msgbox_title}', '{$warning_msg}',pF_delete_req);"	/>
	  	{/if}
	  	
	  	{* BUGID 4588 - If a single version is displayed do only show "Delete this Version" button *}
	  	{if $args_can_delete_version || $gui->version_option}
	  	<input type="button" name="delete_req_version" value="{$labels.btn_del_this_version}"
	  	       onclick="delete_confirmation({$args_req.version_id},
	  	                '{$labels.version}:{$args_req.version}-{$args_req.req_doc_id|escape:'javascript'|escape}:{$args_req.title|escape:'javascript'|escape}',
	  				                              '{$del_msgbox_title}', '{$warning_msg}',pF_delete_req_version);"	/>
	  				                                
	  	{/if}

		{if $args_frozen_version eq null}
	  	<input type="button" name="freeze_req_version" value="{$labels.btn_freeze_this_version}"
	  	       onclick="delete_confirmation({$args_req.version_id},
	  	                '{$labels.version}:{$args_req.version}-{$args_req.req_doc_id|escape:'javascript'|escape}:{$args_req.title|escape:'javascript'|escape}',
	  				                              '{$freeze_msgbox_title}', '{$freeze_warning_msg}',pF_freeze_req_version);"	/>

		{else}
			{if $args_grants->unfreeze_req}
	  		<input type="button" name="unfreeze_req_version" value="{$labels.btn_unfreeze_this_version}"
	  	       onclick="delete_confirmation({$args_req.version_id},
	  	                '{$labels.version}:{$args_req.version}-{$args_req.req_doc_id|escape:'javascript'|escape}:{$args_req.title|escape:'javascript'|escape}',
	  				                              '{$unfreeze_msgbox_title}', '{$unfreeze_warning_msg}',pF_unfreeze_req_version);"	/>
			{/if}
	  	{/if}

	    {if $args_can_copy}  				                                
	  	<input type="submit" name="copy_req" value="{$labels.btn_cp}" onclick="doAction.value='copy'"/>
	  	{/if}
	  	<input type="button" name="new_revision" id="new_revision" value="{$labels.btn_new_revision}" 
	  	       onclick="doAction.value='doCreateRevision';javascript:ask4log('reqViewF','log_message','{$req_version_id}');"/>
	  	<input type="button" name="new_version" id="new_version" value="{$labels.btn_new_version}" 
	  	       onclick="doAction.value='doCreateVersion';javascript:ask4log('reqViewF','log_message','{$req_version_id}');"/>
	  </form>
{/if}
	
	{* compare versions *}
	{if $gui->req_has_history}
	<form style="display: inline;" method="post" action="lib/requirements/reqCompareVersions.php" name="version_compare">
			<input type="hidden" name="requirement_id" value="{$args_req.id}" />
			<input type="submit" name="compare_versions" value="{$labels.btn_view_history}" />
		</form>
	{/if}

{* BUGID 4273: Option to print single requirement *}
<form style="display: inline;" method="post" action="" name="reqPrinterFriendly">
	<input type="button" name="printerFriendly" value="{$labels.btn_print_view}" 
	       onclick="javascript:openPrintPreview('req',{$args_req.id},{$args_req.version_id},
		                                          {$args_req.revision},'lib/requirements/reqPrint.php');"/>
</form>
  </div> {* class="groupBtn" *}
<br/><br/>

{* warning message when req is frozen *}
{if $args_frozen_version neq null}
<div class="messages" align="center">{$labels.can_not_edit_req}</div>
{/if}

{*  BUGID 4038 *}
{* notification message if we display a specific version *}
{if $gui->version_option > 0}
<div class="messages" align="center">{$labels.showing_version} {$args_req.version}</div>
{/if}

<table class="simple">
	<tr>
    <th>{$args_req.req_doc_id|escape}{$tlCfg->gui_title_separator_1}{$args_req.title|escape}</th>
	</tr>

  {if $args_show_version}
	  <tr>
	    {if $args_req.revision_id gt 0}
	    	{assign var="tpt" value=$args_req.revision_id}
	    {else}
	    	{assign var="tpt" value=$args_req.version_id}
	    {/if}
	  	<td class="bold" colspan="2" id="tooltip-{$tpt}">{$labels.version}
	  	{$args_req.version} {$labels.revision} {$args_req.revision}
	  	<img src="{$tlImages.log_message_small}" style="border:none" />
	  	</td>
	  </tr>
	{/if}

  <tr>
	  <td>{$labels.status}{$smarty.const.TITLE_SEP}{$args_gui->reqStatusDomain[$args_req.status]}</td>
	</tr>
	<tr>
	  <td>{$labels.type}{$smarty.const.TITLE_SEP}{$args_gui->reqTypeDomain[$args_req.type]}</td>
	</tr>
	{if $args_gui->req_cfg->expected_coverage_management && $args_gui->attrCfg.expected_coverage[$args_req.type]} 
	<tr>
	  <td>{$labels.expected_coverage}{$smarty.const.TITLE_SEP}{$args_req.expected_coverage}</td>
	</tr>
	{/if}

	<tr>
		<td>
			<fieldset class="x-fieldset x-form-label-left"><legend class="legend_container">{$labels.scope}</legend>
			{$args_req.scope}
			</fieldset>
		</td>
	</tr>
	<td>
	  <fieldset class="x-fieldset x-form-label-left"><legend class="legend_container">{$labels.coverage}</legend>
	  {if $args_req_coverage != ''}
	  {section name=row loop=$args_req_coverage}
	    <span>
	    <img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/history_small.png"
	         onclick="javascript:openExecHistoryWindow({$args_req_coverage[row].id});"
	         title="{$labels.execution_history}" />
	    <img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/edit_icon.png"
	         onclick="javascript:openTCaseWindow({$args_req_coverage[row].id});"
	         title="{$labels.design}" />
	    {$args_gui->tcasePrefix|escape}{$args_gui->glueChar}{$args_req_coverage[row].tc_external_id}{$args_gui->pieceSep}{$args_req_coverage[row].name|escape}
	    </span><br />
	   {sectionelse}
	  <span>{$labels.req_msg_notestcase}</span>
	  {/section}
	  {/if}
	  
	  </fieldset>
			</td>
	 </tr>
	<tr>
			<td>&nbsp;</td>
	</tr>

	<tr class="time_stamp_creation">
  		<td >
      		{$labels.title_created}&nbsp;{localize_timestamp ts=$args_req.creation_ts }&nbsp;
      		{$labels.by}&nbsp;{$args_req.author|escape}
  		</td>
  </tr>
	{if $args_req.modifier != ""}
  <tr class="time_stamp_creation">
  		<td >
    		{$labels.title_last_mod}&nbsp;{localize_timestamp ts=$args_req.modification_ts}
		  	&nbsp;{$labels.by}&nbsp;{$args_req.modifier|escape}
    	</td>
  </tr>
	{/if}
	<tr>
	</tr>
	<tr>
	</tr>
</table>

	{if $args_cf neq ''}
	<div>
        <div id="cfields_design_time" class="custom_field_container">{$args_cf}</div>
	</div>
	{/if}