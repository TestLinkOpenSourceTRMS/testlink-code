{* TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: keywordsExport.tpl,v 1.5 2009/04/07 18:55:29 schlundus Exp $
Purpose: smarty template - keyword export 
rev:
*}

{assign var="action_url" value="lib/keywords/keywordsExport.php?doAction=do_export"}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
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
<h1 class="title">{$main_descr|escape}</h1>

<div class="workBack">
<h1 class="title">{$action_descr|escape}</h1>

  <form method="post" id="export_xml" enctype="multipart/form-data" 
        action="{$action_url}"
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
	  	<td>
	  		{lang_get s='file_type'}
	  	</td>
	  	<td>
		  	<select name="exportType">
		  		{html_options options=$exportTypes}
		  	</select>
		 	<a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{lang_get s="view_file_format_doc"}</a>
	  	</td>
	</tr>
  	</table>
  	
  	<div class="groupBtn">
  		<input type="submit" name="export" value="{lang_get s='btn_export'}" />
  		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/keywords/keywordsView.php';" />
  	</div>
  </form>
</div>

</body>
</html>
