{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: keywordsimport.tpl,v 1.1 2005/12/29 21:03:09 schlundus Exp $ *}
{* Purpose: smarty template - keyword import initial page *}
{* revisions:
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_keyword_import_to'} {$productName|escape}</h1>

<div class="workBack">

<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}?idSRS={$reqSpec.id}">

	<h2>{lang_get s='title_choose_file_type'}</h2>
	<p>{lang_get s='req_import_type'}
	<select name="importType">
		{html_options options=$importTypes}
	</select>
	</p>
	<p>	{lang_get s='required_cvs_format'}<br />
		{foreach key=k item=i from=$keywordFormatStrings}
			{$k} : {$i}<br />
		{/foreach}
	</p>

	<h2>{lang_get s='title_choose_local_file'}</h2>
	<p>{lang_get s='local_file'} <input type="file" name="uploadedFile" size="30" /></p>
	<p>{lang_get s='max_size_cvs_file1'} {$importLimitKB} {lang_get s='max_size_cvs_file2'}</p>
	
	<div class="groupBtn">
		<input type="hidden" name="prodID" value="{$productID}" />
		<input type="hidden" name="MAX_FILE_SIZE" value="{$importLimit}" /> {* restrict file size *}
		<input type="submit" name="UploadFile" value="{lang_get s='btn_upload_file'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/admin/keywordsimport.php';" />
	</div>
</form>

</div>

</body>
</html>