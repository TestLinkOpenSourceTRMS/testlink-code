{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqExport.tpl,v 1.1 2007/11/22 07:34:04 franciscom Exp $ *}
{* Purpose: smarty template - req export initial page *}
{* revisions:
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{assign var="req_module" value=$smarty.const.REQ_MODULE}
{assign var="url_args" value="reqExport.php"}
{assign var="req_export_url" value="$req_module$url_args"}

{assign var="url_args" value="reqSpecView.php?req_spec_id="}
{assign var="req_spec_view_url" value="$basehref$req_module$url_args$req_spec_id"}


{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}

{literal}
<script type="text/javascript">
{/literal}
var warning_empty_filename = "{lang_get s='warning_empty_filename'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.export_filename.value)) 
  {
      alert(warning_empty_filename);
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
    {lang_get s='export_filename'}
    </td>
    <td>
  	<input type="text" name="export_filename" maxlength="{#FILENAME_MAXLEN#}" 
			           value="{$export_filename|escape}" size="{#FILENAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="export_filename"}
  	</td>
  	<tr>
  	<td>{lang_get s='file_type'}</td>
  	<td>
  	<select name="exportType">
  		{html_options options=$exportTypes}
  	</select>
	  <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{lang_get s="view_file_format_doc"}</a>
  	</td>
  	</tr>
  	</table>
      
	 <div class="groupBtn">
		<input type="hidden" name="req_spec_id" value="{$req_spec_id}" />
		<input type="submit" name="export" value="{lang_get s='btn_export'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$idSRS}';" />
	 </div>
</form>

</div>

</body>
</html>