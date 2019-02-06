{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_gui_import_file.tpl,v 1.2 2010/09/19 17:39:40 franciscom Exp $

rev :
*}
{lang_get var="local_labels" 
          s='file_type,view_file_format_doc,local_file,btn_cancel,btn_upload_file,
             action_for_duplicates,skip_frozen_req'
}

<table>
<tr>
<td> {$local_labels.file_type} </td>
<td> <select name="importType">
     {html_options options=$args->importTypes}
     </select>
	   <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$local_labels.view_file_format_doc}</a>
</td>
</tr>
<tr>
  <td>{$local_labels.local_file} </td>
  <td><input type="file" name="uploadedFile" size="{#FILENAME_SIZE#}" 
             maxlength="{#FILENAME_MAXLEN#}"/></td>
</tr>

<tr>
  <td>{$local_labels.skip_frozen_req}</td>
  <td><input type="checkbox" name="skip_frozen_req" {$args->skip_frozen_req_checked} /></td>
</tr>

{if $gui->hitOptions != ''}
  <tr><td>{$gui->duplicate_criteria_verbose} </td>
      <td><select name="hitCriteria" id="hitCriteria">
  			  {html_options options=$gui->hitOptions selected=$gui->hitCriteria}
  		    </select>
    </td>
  </tr>
{/if}

{if $gui->actionOptions != ''}
<tr><td>{$local_labels.action_for_duplicates} </td>
    <td><select name="actionOnHit">
			  {html_options options=$gui->actionOptions selected=$gui->actionOnHit}
		    </select>
  </td>
</tr>
{/if}
</table>
<p>{$args->fileSizeLimitMsg}</p>
<div class="groupBtn">
	<input type="hidden" name="MAX_FILE_SIZE" value="{$args->maxFileSize}" /> {* restrict file size How ?*}
	<input type="submit" name="uploadFile" value="{$local_labels.btn_upload_file}" />
	<input type="button" name="cancel" value="{$local_labels.btn_cancel}"
		     onclick="javascript: location.href='{$args->return_to_url}';" />
</div>

