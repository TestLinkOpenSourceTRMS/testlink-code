{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: reqView.tpl,v 1.19 2009/08/03 08:15:43 franciscom Exp $

rev: 20080512 - franciscom - added paremt_descr 
     20071226 - franciscom - fieldset class added (thanks ext js team)

*}
{* ------------------------------------------------------------------------- *}

{lang_get var="labels"
          s="req,scope,status,coverage,req_msg_notestcase,
             title_created,by,title_last_mod,btn_edit,btn_delete"}
             
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
	{if $gui->showReqSpecTitle}{$gui->parent_descr|escape}{$tlCfg->gui_title_separator_2}{/if}
	{$gui->main_descr|escape}
</h1>

<div class="workBack">

	{if $gui->grants->req_mgmt == "yes"}
	<div class="groupBtn">
    <form id="req" name="req" action="lib/requirements/reqEdit.php" method="post">
    	<input type="hidden" name="requirement_id" value="{$gui->req_id}" />
    	<input type="hidden" name="doAction" value="" />
    	
    	<input type="submit" name="edit_req" 
    	       value="{$labels.btn_edit}" 
    	       onclick="doAction.value='edit'"/>
    	
    	
    	<input type="button" name="delete_req" value="{$labels.btn_delete}"
    	       onclick="delete_confirmation({$gui->req.id},'{$gui->req.title|escape:'javascript'|escape}',
 					                                '{$del_msgbox_title}', '{$warning_msg}');"	/>
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

</div>
</body>