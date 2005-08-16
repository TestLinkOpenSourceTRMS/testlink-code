{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcImport.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* I18N: 20050528 - fm *}
{include file="inc_head.tpl"}

<body>
<h1>{$productName} {lang_get s='title_imp_cvs'}</h1>

{if $imported != ''}
	<p class='info'>{lang_get s='cvs_import_ok'}</p>
{/if}

<div class="workBack">


{if $uploadedFile != ''}

<h2>{lang_get s='check_imp_data'}</h2>

<div>
<form method='post' action='{$SCRIPT_NAME}'>
	<input type='submit' name='import' value="{lang_get s='btn_import_cvs'}">
	<input type='hidden' value='{$uploadedFile}' name='location'>
</form>
<p>{lang_get s='info_imp_data_line1'}<br> 
{lang_get s='info_imp_data_line2'}</p>
</div>
<div>
	{$overview}
</div>

{else}

<h2>{lang_get s='title_choose_local_file'}</h2>

<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">
<input type="hidden" name="MAX_FILE_SIZE" value="30000" /> {* restrict file size *}
	<p>Local File:
		<input type="file" name="uploadedFile" size="30" />
		<input type="submit" value="{lang_get s='btn_upload_file'}" />
	</p>
</form>
<p>{lang_get s='max_size_cvs_file'}</p>
<p>{lang_get s='required_cvs_format'}<br /> 
{lang_get s='the_format'}</p>
{/if}

</div>

</body>
</html>