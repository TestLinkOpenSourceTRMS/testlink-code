{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	reqViewRevisionViewer.tpl
viewer for requirement

@internal revisions
@since 1.9.10
*}
{lang_get var="labels"
          s="requirement_spec,Requirements,scope,status,type,expected_coverage,  
             coverage,btn_delete,btn_cp,btn_edit,btn_del_this_version,btn_new_version,
             btn_del_this_version, btn_freeze_this_version, version, can_not_edit_req,
             testproject,title_last_mod,title_created,by,btn_compare_versions,showing_version,
             btn_revisions,revision,btn_print_view"}

             
{if $args_show_title }
    {if $args_tproject_name != ''}
     <h2>{$labels.testproject} {$args_tproject_name|escape} </h2>
    {/if}
    {if $args_req_spec_name != ''}
     <h2>{$labels.req_spec} {$args_req_spec_name|escape} </h2>
    {/if}
	  <h2>{$labels.title_test_case} {$args_req.title|escape} </h2>
{/if}

{$warning_edit_msg=""}

{* Option to print single requirement *}
<div>
	<form method="post" action="" name="reqPrinterFriendly">
		<input type="button" name="printerFriendly" value="{$labels.btn_print_view}"
		       onclick="javascript:openPrintPreview('req',{$args_req.id},{$args_req.version_id},
		                                          {$args_req.revision},'lib/requirements/reqPrint.php');"/>
	</form>
</div>

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
	  	<td class="bold" id="tooltip-{$args_req.target_id}" colspan="2">{$labels.version}
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