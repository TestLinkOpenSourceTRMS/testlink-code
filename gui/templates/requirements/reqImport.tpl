{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqImport.tpl,v 1.6 2008/05/05 09:11:18 franciscom Exp $
Purpose: smarty template - requirements import initial page
Author: Martin Havlat

rev:
20050830 - MHT - result presentation updated
20051015 - scs - fixed back button
20051202 - scs - fixed 211
20061014 - franciscom - added alert due to:
                        no text file ($ftype_ok)
                        bad syntax ($fsyntax_ok)
*}
{include file="inc_head.tpl"}
{assign var="req_module" value='lib/requirements/'}
{assign var="url_args" value="reqSpecView.php?req_spec_id="}
{assign var="req_spec_view_url" value="$basehref$req_module$url_args$req_spec_id"}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
<body>
<h1>{lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$reqSpec.title|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_req_import'}</h1>

  {if $importResult != '' && $file_check.status_ok }
  	<p class="info">{$importResult}</p>

  	<table class="simple">
  	<tr>
  		<th>{lang_get s="req_doc_id"}</th>
  		<th>{lang_get s="title"}</th>
  		<th style="width: 20%;">{lang_get s="Result"}</th>
  	</tr>
  	{section name=result loop=$arrImport}
  	<tr>
  		<td>{$arrImport[result][0]|escape}</td>
  		<td>{$arrImport[result][1]|escape}</td>
  		<td>{$arrImport[result][2]|escape}</td>
  	</tr>
  	{sectionelse}
  	<tr><td>{lang_get s='req_msg_norequirement'}</td></tr>
  	{/section}
  	</table>



  {elseif $try_upload && $file_check.status_ok && ($arrImport neq "") }

  	{* second screen *}
  	<h2>{lang_get s='title_req_import_check_input'}</h2>

  	<p>{lang_get s='req_import_check_note'}</p>

  	<div>
  	<form method='post' action='{$SCRIPT_NAME}?req_spec_id={$reqSpec.id}'>

  		<p>{lang_get s='req_import_option_header'}
  		<select name="conflicts">
  			<option value ="skip">{lang_get s='req_import_option_skip'}</option>
  			<option value ="overwrite" selected="selected">{lang_get s='req_import_option_overwrite'}</option>
  		</select></p>

  		<p><input type="checkbox" name="noEmpty" checked="checked" />{lang_get s='req_import_dont_empty'}</p>

  		<input type="hidden" name="req_spec_id" value="{$reqSpec.id}" />
  		<input type='hidden' value='{$uploadedFile}' name='uploadedFile' />
  		<input type='hidden' value='{$importType}' name='importType' />

  		<div class="groupBtn">
  			<input type='submit' name='executeImport' value="{lang_get s='btn_import'}" />
  			<input type="button" name="cancel" value="{lang_get s='btn_cancel'}"
  				onclick="javascript: location.href='{$req_spec_view_url}';" />
  		</div>
  	</form>
  	</div>

  	<div>
  	<table class="simple">
  		<tr>
  			<th>{lang_get s="req_doc_id"}</th>
  			<th>{lang_get s="title"}</th>
  			<th>{lang_get s="scope"}</th>
  			<th>{lang_get s="status"}</th>
  		</tr>
  		{section name=row loop=$arrImport}
  		<tr>
  			<td>{$arrImport[row][0]|escape}</td>
  			<td>{$arrImport[row][1]|escape}</td>
  			<td>{$arrImport[row][2]|strip_tags|strip|truncate:100}</td>
  			<td>{$arrImport[row][3]|escape}</td>
  		</tr>
  		{sectionelse}
  		<tr><td><span class="bold">{lang_get s='req_msg_norequirement'}</span></td></tr>
  		{/section}
  	</table>
  	</div>

  {else}

  {* first screen *}
  <form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}?req_spec_id={$reqSpec.id}">

    <table>
    <tr>
    <td> {lang_get s='file_type'} </td>
    <td> <select name="importType">
         {html_options options=$importTypes}
   	     </select>
    	   <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{lang_get s="view_file_format_doc"}</a>
    </td>
    </tr>
    	<tr><td>{lang_get s='local_file'} </td>
	    <td><input type="file" name="uploadedFile"
	                           size="{#FILENAME_SIZE#}" maxlength="{#FILENAME_MAXLEN#}"/></td>
  	</tr>
  </table>
  	<p>{lang_get s='max_size_cvs_file1'} {$importLimitKB} {lang_get s='max_size_cvs_file2'}</p>

  	<div class="groupBtn">
  		<input type="hidden" name="req_spec_id" value="{$reqSpec.id}" />
  		<input type="hidden" name="MAX_FILE_SIZE" value="{$importLimit}" /> {* restrict file size *}
  		<input type="submit" name="UploadFile" value="{lang_get s='btn_upload_file'}" />
  		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}"
  			onclick="javascript: location.href='{$req_spec_view_url}';" />
  	</div>
  </form>
  
  {* must understand if must be removed - franciscom - {$fsyntax_msg} *}

  {if $file_check.status_ok eq 0}
    <script>
    alert("{$file_check.msg}");
    </script>
  {elseif $try_upload  && ($arrImport eq "") }
    <script>
    alert("{lang_get s='check_req_file_structure'}");
    </script>
  {/if}


 {/if}
</div>

</body>
</html>