{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqImport.tpl,v 1.4 2005/11/26 13:27:24 schlundus Exp $ *}
{* Purpose: smarty template - requirements import initial page *}
{* Author: Martin Havlat *}
{* revisions:
20050830 - MHT - result presentation updated
20051015 - am - fixed back button
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_req_import_to'} {$reqSpec.title|escape}</h1>

<div class="workBack">

{if $importResult != ''}

	{* third screen *}
	<div class="groupBtn">
		<input type="button" name="back" value="{lang_get s='btn_back2srs'}" 
			onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$reqSpec.id}';" />
	</div>
	<p class="info">{$importResult}</p>
	
	<table class="simple">
	<tr>
		<th>{lang_get s="Title"}</th>
		<th style="width: 20%;">{lang_get s="Result"}</th>
	</tr>
	{section name=result loop=$arrImport}
	<tr>
		<td>{$arrImport[result][0]|escape}</td>
		<td>{$arrImport[result][1]|escape}</td>
	</tr>
	{sectionelse}
	<tr><td>{lang_get s='req_msg_norequirement'}</td></tr>
	{/section}
	</table>
	


{elseif $importType != ''}

	{* second screen *}
	<h2>{lang_get s='title_req_import_check_input'}</h2>

	<p>{lang_get s='req_import_check_note'}</p>

	<div>
	<form method='post' action='{$SCRIPT_NAME}?idSRS={$reqSpec.id}'>

		<p>{lang_get s='req_import_option_header'}
		<select name="conflicts">
			<option value ="double">{lang_get s='req_import_option_double'}</option>
			<option value ="skip">{lang_get s='req_import_option_skip'}</option>
			<option value ="overwrite" selected="selected">{lang_get s='req_import_option_overwrite'}</option>
		</select></p>

		<p><input type="checkbox" name="noEmpty" checked="checked" />{lang_get s='req_import_dont_empty'}</p>

		<input type="hidden" name="idSRS" value="{$reqSpec.id}" />
		<input type='hidden' value='{$uploadedFile}' name='uploadedFile'>
		<input type='hidden' value='{$importType}' name='importType'>

		<div class="groupBtn">
			<input type='submit' name='executeImport' value="{lang_get s='btn_import_cvs'}">
			<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
				onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$reqSpec.id}';" />
		</div>
	</form>
	</div>

	<div>
	<table class="simple">
		<tr>
			<th>{lang_get s="title"}</th>
			<th>{lang_get s="scope"}</th>
			<th>{lang_get s="status"}</th>
		</tr>
		{section name=row loop=$arrImport}
		<tr>
			<td>{$arrImport[row][0]|escape}</td>
			<td>{$arrImport[row][1]|truncate:100|regex_replace:"/<.*>/":" "}</td>
			<td>{$arrImport[row][2]|escape}</td>
		</tr>
		{sectionelse}
		<tr><td><span class="bold">{lang_get s='req_msg_norequirement'}</span></td></tr>
		{/section}
	</table>
	</div>

{else}

{* first screen *}
<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}?idSRS={$reqSpec.id}">

	<h2>{lang_get s='title_choose_file_type'}</h2>
	<p>{lang_get s='req_import_type'}
	<select name="importType">
		<option value ="csv">CSV</option>
		<option value ="csv_doors">CSV (Doors)</option>
	</select>
	</p>
	<p>{lang_get s='required_cvs_format'}<br />{lang_get s='req_import_format_description1'}
	<br />{lang_get s='req_import_format_description2'}</p>

	<h2>{lang_get s='title_choose_local_file'}</h2>
	<p>{lang_get s='local_file'} <input type="file" name="uploadedFile" size="30" /></p>
	<p>{lang_get s='max_size_cvs_file1'} {$importLimitKB} {lang_get s='max_size_cvs_file2'}</p>
	
	<div class="groupBtn">
		<input type="hidden" name="idSRS" value="{$reqSpec.id}" />
		<input type="hidden" name="MAX_FILE_SIZE" value="{$importLimit}" /> {* restrict file size *}
		<input type="submit" name="UploadFile" value="{lang_get s='btn_upload_file'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$reqSpec.id}';" />
	</div>
</form>
{/if}

</div>

</body>
</html>