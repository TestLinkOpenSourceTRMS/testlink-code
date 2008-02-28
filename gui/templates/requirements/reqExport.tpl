{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqExport.tpl,v 1.2 2008/02/28 22:15:39 franciscom Exp $ *}
{* Purpose: smarty template - req export initial page *}
{* revisions:
*}
{lang_get var="labels"
          s="warning_empty_filename,warning,btn_export,btn_cancel,
             view_file_format_doc,export_filename,file_type"}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="req_module" value=$smarty.const.REQ_MODULE}
{assign var="url_args" value="reqExport.php"}
{assign var="req_export_url" value="$req_module$url_args"}

{assign var="url_args" value="reqSpecView.php?req_spec_id="}
{assign var="req_spec_view_url" value="$basehref$req_module$url_args$req_spec_id"}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_filename = "{$labels.warning_empty_filename}";
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
<h1>{lang_get s='req_spec'} {$smarty.const.TITLE_SEP} {$req_spec.title|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_req_export'}</h1>

<form method="post" enctype="multipart/form-data" action="{$req_export_url}"
      onSubmit="javascript:return validateForm(this);">


    <table>
    <tr>
    <td>
    {$labels.export_filename}
    </td>
    <td>
  	<input type="text" name="export_filename" maxlength="{#FILENAME_MAXLEN#}" 
			           value="{$export_filename|escape}" size="{#FILENAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="export_filename"}
  	</td>
  	<tr>
  	<td>{$labels.file_type}</td>
  	<td>
  	<select name="exportType">
  		{html_options options=$exportTypes}
  	</select>
	  <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$labels.view_file_format_doc}</a>
  	</td>
  	</tr>
  	</table>
      
	 <div class="groupBtn">
		<input type="hidden" name="req_spec_id" value="{$req_spec_id}" />
		<input type="submit" name="export" value="{$labels.btn_export}" />
		<input type="button" name="cancel" value="{$labels.btn_cancel}" 
			onclick="javascript: location.href='{$req_spec_view_url}';" />
	 </div>
</form>

</div>

</body>
</html>