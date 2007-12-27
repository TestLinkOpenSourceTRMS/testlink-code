{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: reqView.tpl,v 1.4 2007/12/27 09:30:24 franciscom Exp $

rev: 20071226 - franciscom - fieldset class added (thanks ext je team)

*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get s='warning_delete_requirement' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$smarty.const.REQ_MODULE}reqEdit.php?do_action=do_delete&requirement_id=';
</script>
</head>

<body {$body_onload}>

<div class="workBack">
<h1> {$main_descr|escape}</h1>

<table class="simple" style="width: 90%">
	<tr>
		<th>{lang_get s='req'}{$smarty.const.TITLE_SEP}{$req.title|escape}</th>
	</tr>
  <tr>
  <td>{lang_get s='req_doc_id'}{$smarty.const.TITLE_SEP}{$req.req_doc_id}</td>
  </tr>

  <tr>
		<td>
			<fieldset class="x-fieldset x-form-label-left"><legend class="legend_container">{lang_get s='scope'}</legend>
			{$req.scope}
			</fieldset>
		</td>
  </tr>
  <tr>
  <td>{lang_get s='status'}{$smarty.const.TITLE_SEP}{$selectReqStatus[$req.status]}</td>
  </tr>
  <tr>
		<td>
			<fieldset class="x-fieldset x-form-label-left"><legend class="legend_container">{lang_get s='coverage'}</legend>
					  {section name=row loop=$req.coverage}
			  <span>{$req.coverage[row].name}</span><br />
		   {sectionelse}
			<span>{lang_get s='req_msg_notestcase'}</span>
		  {/section}

			</fieldset>
		</td>
  </tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	
	<tr>
	  <td>
  	{$cf}
  	</td>
	</tr>

  <tr class="time_stamp_creation">
  <td colspan="2">
      {lang_get s='title_created'}&nbsp;{localize_timestamp ts=$req.creation_ts }&nbsp;
      		{lang_get s='by'}&nbsp;{$req.author|escape}
  </td>
  </tr>
  {if $req.modifier ne ""}
    <tr class="time_stamp_creation">
    <td colspan="2">
    {lang_get s='title_last_mod'}&nbsp;{localize_timestamp ts=$req.modification_ts}
		  &nbsp;{lang_get s='by'}&nbsp;{$req.modifier|escape}
    </td>
    </tr>
  {/if}
</table>

{if $modify_req_rights neq 'yes'}
	{assign var="bDownloadOnly" value=true}
{/if}
{include file="inc_attachments.tpl" id=$req.id  tableName="requirements"
         attachmentInfos=$attachments  downloadOnly=$bDownloadOnly}




  {* ----------------------------------------------------------------------------------------- *}
  <div class="groupBtn">
    <form id="req" name="req" action="{$smarty.const.REQ_MODULE}reqEdit.php" method="post">
    	<input type="hidden" name="requirement_id" value="{$req_id}" />
    	<input type="hidden" name="do_action" value="" />
    	
    	{if $modify_req_rights == "yes"}
    	<input type="submit" name="edit_req" 
    	       value="{lang_get s='btn_edit'}" 
    	       onclick="do_action.value='edit'"/>
    	
    	
    	<input type="button" name="delete_req" value="{lang_get s='btn_delete'}"
    	       onclick="delete_confirmation({$req.id},'{$req.title|escape:'javascript'}',
 					                                '{$del_msgbox_title}', '{$warning_msg}');"	/>
    	{/if}
    </form>
  </div>
</div>
</body>