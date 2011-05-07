{* TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	keywordsExport.tpl
Purpose: smarty template - keyword export 

@internal revisions
*}

{$action_url = "lib/keywords/keywordsExport.php?doAction=do_export"}
{$cfg_section = $smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}

{lang_get var='labels'
          s='export_filename,file_type,view_file_format_doc,btn_export,btn_cancel,warning_empty_filename'}


<script type="text/javascript">
var warning_empty_filename = "{$labels.warning_empty_filename}";
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
</head>


<body>
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
<h1 class="title">{$gui->action_descr|escape}</h1>

  <form method="post" id="export_xml" enctype="multipart/form-data" 
        action="{$action_url}"
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
	  	<td>
	  		{$labels.file_type}
	  	</td>
	  	<td>
		  	<select name="exportType">
		  		{html_options options=$gui->exportTypes}
		  	</select>
		 	<a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$labels.view_file_format_doc}</a>
	  	</td>
	</tr>
  	</table>
  	
  	<div class="groupBtn">
  		<input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
  		<input type="submit" name="export" value="{$labels.btn_export}" />
  		<input type="button" name="cancel" value="{$labels.btn_cancel}" 
			onclick="javascript: location.href=fRoot+'lib/keywords/keywordsView.php?tproject_id={$gui->tproject_id}';" />
  	</div>
  </form>
</div>

</body>
</html>