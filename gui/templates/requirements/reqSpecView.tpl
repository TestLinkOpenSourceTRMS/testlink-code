{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqSpecView.tpl,v 1.5 2007/11/28 08:15:10 franciscom Exp $ *}
{* 
   Purpose: smarty template - view a requirement specification
   Author: Martin Havlat 

   rev: 20071106 - franciscom - added ext js library
        20070102 - franciscom - added javascript validation of checked requirements 
*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="bn" value=$smarty.template|basename}
{assign var="buttons_template" value=$smarty.template|replace:"$bn":"inc_btn_$bn"}

{assign var="req_module" value=$smarty.const.REQ_MODULE}
{assign var="url_args" value="reqEdit.php?do_action=create&req_spec_id="}
{assign var="req_edit_url" value="$basehref$req_module$url_args$req_spec_id"}

{assign var="url_args" value="reqImport.php?req_spec_id="}
{assign var="req_import_url"  value="$basehref$req_module$url_args$req_spec_id"}

{assign var="url_args" value="reqEdit.php?do_action=reorder&req_spec_id="}
{assign var="req_reorder_url"  value="$basehref$req_module$url_args$req_spec_id"}


{lang_get s='delete_confirm_question' var="warning_msg" }

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var o_label ="{lang_get s='requirement_spec'}";
var del_action=fRoot+'{$smarty.const.REQ_MODULE}reqSpecEdit.php?do_action=do_delete&req_spec_id=';
</script>

</head>


<body>

<div class="workBack">
<h1> 
 {lang_get s='help' var='common_prefix'}
 {lang_get s='req_spec' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 {include file="inc_help.tpl" help="requirementsCoverage" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
	{lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$req_spec.title|escape}
</h1>
<br>
{include file="$buttons_template}

<table class="simple" style="width: 90%">
	<tr>
		<th>{lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$req_spec.title|escape}</th>
	</tr>
	<tr>
		<td>
			<fieldset><legend class="legend_container">{lang_get s='scope'}</legend>
			{$req_spec.scope}
			</fieldset>
		</td>
	</tr>
  {if $req_spec.total_req neq "0"}
  <tr>
  <td>{lang_get s='req_total'}{$smarty.const.TITLE_SEP}{$req_spec.total_req}</td>
   </tr>
  {/if}
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
      {lang_get s='title_created'}&nbsp;{localize_timestamp ts=$req_spec.creation_ts }&nbsp;
      		{lang_get s='by'}&nbsp;{$req_spec.author|escape}
  </td>
  </tr>
  {if $req_spec.modifier ne ""}
    <tr class="time_stamp_creation">
    <td colspan="2">
    {lang_get s='title_last_mod'}&nbsp;{localize_timestamp ts=$req_spec.modification_ts}
		  &nbsp;{lang_get s='by'}&nbsp;{$req_spec.modifier|escape}
    </td>
    </tr>
  {/if}

</table>

{if $modify_req_rights neq 'yes'}
	{assign var="bDownloadOnly" value=true}
{/if}
{include file="inc_attachments.tpl" id=$req_spec.id  tableName="req_spec"
         attachmentInfos=$attachments  downloadOnly=$bDownloadOnly}

</div>
{if $refresh_tree}
   {include file="inc_refreshTree.tpl"}
{/if}
</body>
</html>
