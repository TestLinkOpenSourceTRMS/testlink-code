{* TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource: reqExport.tpl

*}
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
  s="warning_empty_filename,title_req_export,warning,btn_export,btn_cancel,
     view_file_format_doc,req_spec,export_filename,file_type,
     export_attachments"}

{$req_module='lib/requirements/'}
{$url_args="reqExport.php"}
{$req_export_url="$req_module$url_args"}
{$url_args="reqSpecView.php?req_spec_id="}
{$req_spec_view_url="$basehref$req_module$url_args"}

{if $gui->req_spec_id == 0}
  {$dummy=$gui->tproject_id}
  {$targetUrl="lib/project/project_req_spec_mgmt.php?id="}
  {$xurl="$basehref$targetUrl"}
  {$cancelUrl="$xurl$dummy"}
{else}
  {$req_spec_view_url="$basehref$req_module$url_args"}
  {$dummy=$gui->req_spec_id}
  {$cancelUrl="$req_spec_view_url$dummy"}
{/if}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var warning_empty_filename = "{$labels.warning_empty_filename|escape:'javascript'}";
var alert_box_title = "{$labels.warning}";

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
</head>

<body>
<h1 class="{#TITLE_CLASS#}">{$labels.req_spec} {$smarty.const.TITLE_SEP} {$gui->req_spec.title|escape}</h1>

<div class="workBack">
<h1 class="title">{$labels.title_req_export}</h1>

<form method="post" 
      enctype="multipart/form-data" action="{$req_export_url}"
      onSubmit="javascript:return validateForm(this);">
    <table>
    <tr>
    <td>
    {$labels.export_filename}
    </td>
    <td>
  	<input type="text" 
           name="export_filename" id="export_filename"
           maxlength="{#FILENAME_MAXLEN#}" 
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
		<input class="{#BUTTON_CLASS#}" type="submit" 
           id="export" name="export" 
           value="{$labels.btn_export}" 
		       onclick="doAction.value='doExport'" />
    <input class="{#BUTTON_CLASS#}" type="button" 
           name="cancel" id="cancel"
           value="{$labels.btn_cancel}"
           onclick="javascript: location.href='{$cancelUrl}';" />
	 </div>
</form>

</div>

</body>
</html>