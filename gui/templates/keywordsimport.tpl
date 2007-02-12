{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: keywordsimport.tpl,v 1.5 2007/02/12 08:08:15 franciscom Exp $ *}
{* Purpose: smarty template - keyword import initial page *}
{* revisions:
   20051231 - scs - fixed incorrect cancel button link
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='testproject'}{$smarty.const.TITLE_SEP}{$tproject_name|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_keyword_import_to'}</h1>

<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">

	<h2>{lang_get s='title_choose_file_type'}</h2>
	<p>{lang_get s='req_import_type'}
	<select name="importType">
		{html_options options=$importTypes selected=$import_type_selected}
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
		<input type="hidden" name="tproject_id" value="{$tproject_id}" />
		<input type="hidden" name="MAX_FILE_SIZE" value="{$importLimit}" /> {* restrict file size *}
		<input type="submit" name="UploadFile" value="{lang_get s='btn_upload_file'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/keywords/keywordsView.php';" />
	</div>
</form>

{if $file_check.status_ok eq 0}
    <script>
    alert("{$file_check.msg}");
    </script>
{/if}  

</div>

</body>
</html>