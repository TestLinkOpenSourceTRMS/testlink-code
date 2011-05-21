{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	reqViewVersionsViewer.tpl
viewer for requirement

@internal revisions
20110307 - asimon - BUGID 4273: moved print preview to popup to make printing independent from browser easier for the users
                                moved req view button forms and divs around to align buttons in a single row
20110305 - franciscom - display printer friendly button always
20110305 - franciscom - BUGID 4273: Option to print single requirement
20101127 - franciscom - BUGID 4056: Requirement Revisioning
20101119 - asimon - BUGID 4038: clicking requirement link does not open req version
20101113 - franciscom - BUGID 3410: Smarty 3.0 compatibility
20101111 - asimon - replaced openTCaseWindow() by openTCEditWindow() to save popup size
*}
{lang_get var="labels"
          s="requirement_spec,Requirements,scope,status,type,expected_coverage,  
             coverage,btn_delete,btn_cp,btn_edit,btn_del_this_version,btn_new_version,
             btn_del_this_version, btn_freeze_this_version, version, can_not_edit_req,
             testproject,title_last_mod,title_created,by,btn_compare_versions,showing_version,
             revision,btn_view_history,btn_new_revision,btn_print_view"}

             
{$hrefReqSpecMgmt="lib/general/frmWorkArea.php?feature=reqSpecMgmt"}
{$hrefReqSpecMgmt="$basehref$hrefReqSpecMgmt"}

{$hrefReqMgmt="lib/requirements/reqView.php?showReqSpecTitle=1&requirement_id="}
{$hrefReqMgmt="$basehref$hrefReqMgmt"}

{$module='lib/requirements/'}
{$req_id=$args_req.id}
{$req_version_id=$args_req.version_id}

{if $args_show_title}
    {if $args_tproject_name != ''}
     <h2>{$labels.testproject} {$args_tproject_name|escape} </h2>
    {/if}
    {if $args_req_spec_name != ''}
     <h2>{$labels.req_spec} {$args_req_spec_name|escape} </h2>
    {/if}
	<h2>{$labels.title_test_case} {$args_req.title|escape} </h2>
{/if}
{$warning_edit_msg=""}

<div style="display: inline;" class="groupBtn">
{if $args_grants->req_mgmt == "yes"}
	  <form style="display: inline;" id="reqViewF_{$req_version_id}" name="reqViewF_{$req_version_id}" 
	        action="lib/requirements/reqEdit.php" method="post">
	  	<input type="hidden" name="requirement_id" value="{$args_req.id}" />
	  	<input type="hidden" name="req_version_id" value="{$args_req.version_id}" />
		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
	  	<input type="hidden" name="doAction" value="" />
	  	
	  	{* IMPORTANT NOTICE: name can not be dynamic because PHP uses name not ID *}
	  	<input type="hidden" name="log_message" id="log_message_{$req_version_id}" value="" />
	  	
	  	
	  	{if $args_frozen_version eq null}
	  	<input type="submit" name="edit_req" value="{$labels.btn_edit}" onclick="doAction.value='edit'"/>
	  	{/if}
	  	
	  	{if $args_can_delete_req}
	  	<input type="button" name="delete_req" value="{$labels.btn_delete}"
	  	       onclick="action_confirmation({$args_req.id},
	  	                                    '{$args_req.req_doc_id|escape:'javascript'|escape}:{$args_req.title|escape:'javascript'|escape}',
	  	                                    '{$del_msgbox_title}', '{$warning_msg}',pF_delete_req);"/>
	  	{/if}
	  	
	  	{if $args_can_delete_version}
	  	<input type="button" name="delete_req_version" value="{$labels.btn_del_this_version}"
	  	       onclick="action_confirmation({$args_req.version_id},
	  	                '{$labels.version}:{$args_req.version}-{$args_req.req_doc_id|escape:'javascript'|escape}:{$args_req.title|escape:'javascript'|escape}',
	  				                              '{$del_msgbox_title}', '{$warning_msg}',pF_delete_req_version);"	/>
	  				                                
	  	{/if}

		{* freeze, BUGID 3089 *}
		{if $args_frozen_version eq null}
	  	<input type="button" name="freeze_req_version" value="{$labels.btn_freeze_this_version}"
	  	       onclick="action_confirmation({$args_req.version_id},
	  	                '{$labels.version}:{$args_req.version}-{$args_req.req_doc_id|escape:'javascript'|escape}:{$args_req.title|escape:'javascript'|escape}',
	  				                              '{$freeze_msgbox_title}', '{$freeze_warning_msg}',pF_freeze_req_version);"	/>
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
		<input type="hidden" name="tproject_id" id="history_tproject_id" value="{$gui->tproject_id}">
		<input type="submit" name="compare_versions" value="{$labels.btn_view_history}" />
	</form>
{/if}

{* BUGID 4273: Option to print single requirement *}
<form style="display: inline;" method="post" action="" name="reqPrinterFriendly">
	<input type="button" name="printerFriendly" value="{$labels.btn_print_view}" 
	       onclick="javascript:openPrintPreview('req',{$args_req.id},{$args_req.version_id},{$args_req.revision},
	       										'lib/requirements/reqPrint.php?tproject_id={$gui->tproject_id}');"/>
</form>
</div> {* class="groupBtn" *}
<br/><br/>

{* warning message when req is frozen *}
{if $args_frozen_version neq null}
<div class="messages" align="center">{$labels.can_not_edit_req}</div>
{/if}

{* BUGID 4038 *}
{* notification message if we display a specific version *}
{if $gui->version_option > 0}
<div class="messages" align="center">{$labels.showing_version} {$args_req.version}</div>
{/if}

<table class="simple">
  {if $args_show_title}
	<tr>
		<th colspan="2">
		{$args_req.req_doc_id}{$smarty.const.TITLE_SEP}{$args_req.title|escape}</th>
	</tr>
  {/if}
	<tr>
    <th>{$args_req.req_doc_id|escape}{$tlCfg->gui_title_separator_1}{$args_req.title|escape}</th>
	</tr>

  {if $args_show_version}
	  <tr>
	  	<td class="bold" colspan="2">{$labels.version}
	  	{$args_req.version} {$labels.revision} {$args_req.revision}
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
	    <span> {* BUGID 2521 *}
	    {* replaced openTCaseWindow() by openTCEditWindow() to save popup size *}
	    <a href="javascript:openTCEditWindow({$args_req_coverage[row].id})">
	    {$args_gui->tcasePrefix|escape}{$args_gui->glueChar}{$args_req_coverage[row].tc_external_id}{$args_gui->pieceSep}{$args_req_coverage[row].name|escape}</a>
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
      		{$labels.title_created}&nbsp;{localize_timestamp ts=$args_req.creation_ts}&nbsp;
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