{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource keywordsExport.tpl
Purpose: smarty template - keyword export 
rev:
*}
{lang_get var='lbl'
          s='file_type,btn_export,export_filename,
             view_file_format_doc,keywords_file,btn_cancel'}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}

<script type="text/javascript">
var warning_empty_filename = "{lang_get s='warning_empty_filename'}";

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
	<div class="container">
		<h1 class="title">{$gui->action_descr|escape}</h1>
		  <form method="post" id="export_xml" enctype="multipart/form-data" 
		        action="{$gui->actionUrl}"
		        onSubmit="javascript:return validateForm(this);">
		  
		  <div class="form-group row">
		  	<label for="export_filename">{$lbl.export_filename}</label>
		  	<input type="text" class="form-control" id="export_filename" name="export_filename" maxlength="{#FILENAME_MAXLEN#}" 
				           value="{$gui->export_filename|escape}" size="{#FILENAME_SIZE#}"/>
				  				{include file="error_icon.tpl" field="export_filename"}
		  </div>
	      <div class="form-group row">
	      	<label for="exportType">{$lbl.file_type}</label>
	      	<select name="exportType" id=exportType class="form-control" >
		  		{html_options options=$gui->exportTypes}
		  	</select>
		 	<a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$lbl.view_file_format_doc}</a>
	      </div>
  	
  	<div class="groupBtn">
  		<input type="submit" class="btn btn-default" name="export" value="{$lbl.btn_export}" />
  		<input type="button" class="btn btn-default" name="cancel" value="{$lbl.btn_cancel}" 
			onclick="javascript: location.href=fRoot+'{$gui->cancelUrl}';" />
  	</div>
  </form>
</div>

</body>
</html>
