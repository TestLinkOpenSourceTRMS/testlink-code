{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqExport.tpl,v 1.8 2010/11/06 11:42:47 amkhullar Exp $ *}
{* Purpose: smarty template - req export initial page *}
{* revisions:
*}
{lang_get var="labels"
          s="warning_empty_filename,title_req_export,warning,btn_export,btn_cancel,
             view_file_format_doc,req_spec,export_filename,file_type,export_attachments"}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="req_module" value='lib/requirements/'}
{assign var="url_args" value="reqExport.php"}
{assign var="req_export_url" value="$req_module$url_args"}

{assign var="url_args" value="reqSpecView.php?req_spec_id="}
{assign var="req_spec_view_url" value="$basehref$req_module$url_args"}

{if $gui->req_spec_id == 0}
  {assign var="dummy" value=$gui->tproject_id}
  {assign var="targetUrl" value="lib/project/project_req_spec_mgmt.php?id="}
  {assign var="xurl" value="$basehref$targetUrl"}
  {assign var="cancelUrl" value="$xurl$dummy"}
{else}
  {assign var="req_spec_view_url" value="$basehref$req_module$url_args"}
  {assign var="dummy" value=$gui->req_spec_id}
  {assign var="cancelUrl" value="$req_spec_view_url$dummy"}
{/if}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
// BUGID 3943: Escape all messages (string)
var warning_empty_filename = "{$labels.warning_empty_filename|escape:'javascript'}";
var alert_box_title = "{$labels.warning}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.export_filename.value)) 
  {
      alert_message(alert_box_title,warning_empty_filename);
      selectField(f, 'export_filename');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>

<body>
<h1 class="title">{$labels.req_spec} {$smarty.const.TITLE_SEP} {$gui->req_spec.title|escape}</h1>

<div class="workBack">
<h1 class="title">{$labels.title_req_export}</h1>

<form method="post" enctype="multipart/form-data" action="{$req_export_url}"
      onSubmit="javascript:return validateForm(this);">
    <table>
    <tr>
    <td>
    {$labels.export_filename}
    </td>
    <td>
  	<input type="text" name="export_filename" maxlength="{#FILENAME_MAXLEN#}" 
			           value="{$gui->export_filename|escape}" size="{#FILENAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="export_filename"}
  	</td>
  	<tr>
  	<td>{$labels.file_type}</td>
  	<td>
  	<select name="exportType">
  		{html_options options=$gui->exportTypes}
  	</select>
	  <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$labels.view_file_format_doc}</a>
  	</td>
  	</tr>
	<tr>
    <td>{$labels.export_attachments}</td>
    <td><input type="checkbox" name="exportAttachments" value="1" /></td>
    </tr>
  	</table>
      
	 <div class="groupBtn">
		<input type="hidden" id="doAction" name="doAction" value="export" />
		<input type="hidden" name="req_spec_id" value="{$gui->req_spec_id}" />
		<input type="hidden" name="scope" id="scope" value="{$gui->scope}" />
		<input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
		<input type="submit" id="export" name="export" value="{$labels.btn_export}" 
		       onclick="doAction.value='doExport'" />
    {*       
		<input type="button" name="cancel" value="{$labels.btn_cancel}" 
			onclick="javascript: location.href='{$req_spec_view_url}{$gui->req_spec_id}';" />
    *} 
    <input type="button" name="cancel" value="{$labels.btn_cancel}" 
      onclick="javascript: location.href='{$cancelUrl}';" />
      
	 </div>
</form>

</div>

</body>
</html>