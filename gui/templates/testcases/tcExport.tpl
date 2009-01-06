{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcExport.tpl,v 1.7 2009/01/06 15:34:05 franciscom Exp $ 
Purpose: smarty template - keyword export initial page 
Revisions:
       20071013 - franciscom - file name management
       20070113 - franciscom - added message when there is nothing to export 
* ----------------------------------------------------------------- *}
{lang_get var="labels" 
          s='export_filename,warning_empty_filename,file_type,
             view_file_format_doc,export_with_keywords,btn_export,btn_cancel'} 

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}

<script type="text/javascript">
var warning_empty_filename = "{$labels.warning_empty_filename}";
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
{/literal}
</script>
</head>

<body>
<h1 class="title">{$gui->page_title}{$smarty.const.TITLE_SEP}{$gui->object_name|escape}</h1>

<div class="workBack">

{if $gui->do_it eq 1}
  <form method="post" id="export_xml" enctype="multipart/form-data" 
        action="lib/testcases/tcExport.php"
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
	  <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{lang_get s="view_file_format_doc"}</a>
  	</td>
  	</tr>
    <tr>
    <td>{$labels.export_with_keywords}</td>
    <td><input type="checkbox" name="bKeywords" value="0" /></td>
    </tr>

  	</table>
  	
  	<div class="groupBtn">
  		<input type="hidden" name="testcase_id" value="{$gui->tcID}" />
  		<input type="hidden" name="tcversion_id" value="{$gui->tcVersionID}" />
  		<input type="hidden" name="containerID" value="{$gui->containerID}" />
  		<input type="hidden" name="bRecursive" value="{$gui->bRecursive}" />
  		<input type="submit" name="export" value="{$labels.btn_export}" />
  		<input type="button" name="cancel" value="{$labels.btn_cancel}" 
  			                   onclick="javascript:history.back();" />
  	</div>
  </form>
{else}
	{$gui->nothing_todo_msg|escape}
{/if}

</div>

</body>
</html>
