{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcExport.tpl,v 1.2 2008/01/05 22:00:53 schlundus Exp $ *}
{* Purpose: smarty template - keyword export initial page *}
{* rev:
       20071013 - franciscom - file name management
       20070113 - franciscom - added message when there is nothing to export 
*}
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
<h1>{$container_description}{$smarty.const.TITLE_SEP}{$object_name|escape}</h1>

<div class="workBack">
<h1>{$page_title}</h1>

{if $do_it eq 1}
  <form method="post" id="export_xml" enctype="multipart/form-data" 
        action="lib/testcases/tcExport.php"
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
    <tr>
    <td>{lang_get s='export_with_keywords'}</td>
    <td><input type="checkbox" name="bKeywords" value="0" /></td>
    </tr>

  	</table>
  	
  	<div class="groupBtn">
  		<input type="hidden" name="testcase_id" value="{$tcID}" />
  		<input type="hidden" name="tcversion_id" value="{$tcVersionID}" />
  		<input type="hidden" name="containerID" value="{$containerID}" />
  		<input type="hidden" name="bRecursive" value="{$bRecursive}" />
  		<input type="submit" name="export" value="{lang_get s='btn_export'}" />
  		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
  			onclick="javascript:history.back();" />
  	</div>
  </form>
{else}
	{$nothing_todo_msg|escape}
{/if}

</div>

</body>
</html>
