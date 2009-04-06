{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_gui_import_file.tpl,v 1.1 2009/04/06 10:23:45 franciscom Exp $

rev :
*}
{lang_get var="local_labels" 
          s='file_type,view_file_format_doc,local_file,btn_cancel,btn_upload_file' }

<table>
<tr>
<td> {$local_labels.file_type} </td>
<td> <select name="importType">
     {html_options options=$args->importTypes}
     </select>
	   <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$local_labels.view_file_format_doc}</a>
</td>
</tr>
	<tr><td>{$local_labels.local_file} </td>
  <td><input type="file" name="uploadedFile" size="{#FILENAME_SIZE#}" 
             maxlength="{#FILENAME_MAXLEN#}"/></td>
</tr>
</table>
<p>{$args->fileSizeLimitMsg}</p>
<div class="groupBtn">
	<input type="hidden" name="MAX_FILE_SIZE" value="{$args->maxFileSize}" /> {* restrict file size How ?*}
	<input type="submit" name="uploadFile" value="{$local_labels.btn_upload_file}" />
	<input type="button" name="cancel" value="{$local_labels.btn_cancel}"
		     onclick="javascript: location.href='{$args->return_to_url}';" />
</div>

