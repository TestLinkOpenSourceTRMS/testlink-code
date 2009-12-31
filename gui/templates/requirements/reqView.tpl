{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: reqView.tpl,v 1.29 2009/12/31 10:27:20 franciscom Exp $

rev: 20080512 - franciscom - added paremt_descr 
     20071226 - franciscom - fieldset class added (thanks ext js team)

*********************************************************************************
*********************************************************************************
ATTENTION THIS CODE IS DEPRECATED 

USE reqViewVersions.tpl and reqViewVersionsViewer.tpl
*********************************************************************************
*********************************************************************************

*}
{* ------------------------------------------------------------------------- *}

{lang_get var="labels"
          s="req,scope,status,coverage,req_msg_notestcase,type,expected_coverage,
             title_created,by,title_last_mod,btn_edit,btn_delete,btn_cp,btn_new_version,
             no_records_found"}
             
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='warning_delete_requirement' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'lib/requirements/reqEdit.php?doAction=doDelete&requirement_id=';
</script>
</head>

{* ------------------------------------------------------------------------- *}
<body {$body_onload}>

<h1 class="title">
  {$toggle_direct_link_img} &nbsp;
	{if $gui->showReqSpecTitle}{$gui->parent_descr|escape}{$tlCfg->gui_title_separator_2}{/if}
	{$gui->main_descr|escape}
</h1>

<div class="workBack">
   <div class="direct_link" style='display:none'><a href"{$gui->direct_link}" target="_blank">{$gui->direct_link}</a></div>


{* contribution by asimon83/mx-julian *}
{if $gui->req.id}
{* end contribution by asimon83/mx-julian *}
	
	{if $gui->grants->req_mgmt == "yes"}
	<div class="groupBtn">
	<form id="req" name="req" action="lib/requirements/reqEdit.php" method="post">
		<input type="hidden" name="requirement_id" value="{$gui->req_id}" />
		<input type="hidden" name="doAction" value="" />
		
		<input type="submit" name="edit_req" value="{$labels.btn_edit}" onclick="doAction.value='edit'"/>
		
		
		<input type="button" name="delete_req" value="{$labels.btn_delete}"
		       onclick="delete_confirmation({$gui->req.id},'{$gui->req.title|escape:'javascript'|escape}',
					                                '{$del_msgbox_title}', '{$warning_msg}');"	/>
					                                
					                                
		<input type="submit" name="copy_req" value="{$labels.btn_cp}" onclick="doAction.value='copy'"/>
		<input type="submit" name="new_version" value="{$labels.btn_new_version}" onclick="doAction.value='doCreateVersion'"/>
	</form>
	</div>
	{/if}
	
	
	<table class="simple" style="width: 90%">
		<tr>
			<th>{$gui->req.req_doc_id|escape}{$tlCfg->gui_title_separator_1}{$gui->main_descr|escape}</th>
		</tr>
	
	  <tr>
	  <td>{$labels.status}{$smarty.const.TITLE_SEP}{$gui->reqStatus[$gui->req.status]}</td>
	  </tr>

	  <tr>
	  <td>{$labels.type}{$smarty.const.TITLE_SEP}{$gui->reqTypeDomain[$gui->req.type]}</td>
	  </tr>
	  <tr>
	  <td>{$labels.expected_coverage}{$smarty.const.TITLE_SEP}{$gui->req.expected_coverage}</td>
	  </tr>
	
	  <tr>
			<td>
				<fieldset class="x-fieldset x-form-label-left"><legend class="legend_container">{$labels.scope}</legend>
				{$gui->req.scope}
				</fieldset>
			</td>
	  </tr>
	  <tr>
			<td>
				<fieldset class="x-fieldset x-form-label-left"><legend class="legend_container">{$labels.coverage}</legend>
						  {section name=row loop=$gui->req.coverage}
				  <span> {* BUGID 2521 *}
				  <a href="javascript:openTCaseWindow({$gui->req.coverage[row].id})">
				  {$gui->tcasePrefix|escape}{$gui->glueChar}{$gui->req.coverage[row].tc_external_id}{$gui->pieceSep}{$gui->req.coverage[row].name|escape}</a>
				  </span><br />
			   {sectionelse}
				<span>{$labels.req_msg_notestcase}</span>
			  {/section}
	
				</fieldset>
			</td>
	  </tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
		  <td>
	  	{$gui->cfields}
	  	</td>
		</tr>

	  <tr class="time_stamp_creation">
	  <td colspan="2">
	      {$labels.title_created}&nbsp;{localize_timestamp ts=$gui->req.creation_ts }&nbsp;
	      		{$labels.by}&nbsp;{$gui->req.author|escape}
	  </td>
	  </tr>
	  {if $gui->req.modifier != ""}
	    <tr class="time_stamp_creation">
	    <td colspan="2">
	    {$labels.title_last_mod}&nbsp;{localize_timestamp ts=$gui->req.modification_ts}
			  &nbsp;{$labels.by}&nbsp;{$gui->req.modifier|escape}
	    </td>
	    </tr>
	  {/if}
	
	</table>
	
	{assign var="bDownloadOnly" value=true}
	{if $gui->grants->req_mgmt == 'yes'}
	  {assign var="bDownloadOnly" value=false}
	{/if}
	{include file="inc_attachments.tpl" 
	         attach_id=$gui->req.id  
	         attach_tableName="requirements"
	         attach_attachmentInfos=$gui->attachments  
	         attach_downloadOnly=$bDownloadOnly}

{* contribution by asimon83/mx-julian *}         
{else}
	{lang_get s='no_records_found' }
{/if}
{* end contribution by asimon83/mx-julian *}
         	
	</div>
</body>