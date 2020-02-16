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
{include file="aside.tpl"}  
<div id="main-content">
<h1 class="{#TITLE_CLASS#}">{$gui->main_descr|escape}</h1>

<div class="workBack">
<h1 class="title">{$gui->action_descr|escape}</h1>

  <form method="post" id="export_xml" enctype="multipart/form-data" 
        action="{$gui->actionUrl}"
        onSubmit="javascript:return validateForm(this);">
  
    <table>
    <tr>
	    <td>
		    {$lbl.export_filename}
	    </td>
	    <td>
		  	<input type="text" 
               name="export_filename" id="export_filename"
               maxlength="{#FILENAME_MAXLEN#}" 
				       value="{$gui->export_filename|escape}" size="{#FILENAME_SIZE#}"/>
				  				{include file="error_icon.tpl" field="export_filename"}
	  	</td>
  	<tr>
	  	<td>
	  		{$lbl.file_type}
	  	</td>
	  	<td>
		  	<select name="exportType" id="exportType">
		  		{html_options options=$gui->exportTypes}
		  	</select>
		 	<a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$lbl.view_file_format_doc}</a>
	  	</td>
	</tr>
  	</table>
  	
  	<div class="groupBtn">
  		<input class="{#BUTTON_CLASS#}" type="submit" 
             name="export" id="export" value="{$lbl.btn_export}" />
  		<input class="{#BUTTON_CLASS#}" type="button" 
             name="cancel" id="cancel" value="{$lbl.btn_cancel}" 
			onclick="javascript: location.href=fRoot+'{$gui->cancelUrl}';" />
  	</div>
  </form>
</div>
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>