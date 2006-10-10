{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcImport.tpl,v 1.9 2006/10/10 20:09:14 schlundus Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* I18N: 20050528 - fm *}
{* 20050828 - scs - changes for importing tc to a specific category *}
{* 20050831 - scs - import limits are now define in config.inc.php *}
{* 20051104 - scs - product name wasn't escaped *}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_tc_import_to'} {$productName|escape}</h1>

<div class="workBack">

{if $resultMap eq null}
<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">
	<h2>{lang_get s='title_choose_file_type'}</h2>
	<p>{lang_get s='req_import_type'}
	<select name="importType">
		{html_options options=$importTypes}
	</select>
	</p>
	<p>	{lang_get s='required_cvs_format'}<br />
		{foreach key=k item=i from=$tcFormatStrings}
			{$k} : {$i}<br />
		{/foreach}
	</p>

	<h2>{lang_get s='title_choose_local_file'}</h2>
	<p>{lang_get s='local_file'} <input type="file" name="uploadedFile" size="30" /></p>
	<p>{lang_get s='max_size_cvs_file1'} {$importLimitKB} {lang_get s='max_size_cvs_file2'}</p>
	
	<div class="groupBtn">
		<input type="hidden" name="bRecursive" value="{$bRecursive}" />
		<input type="hidden" name="bIntoProject" value="{$bIntoProject}" />
		<input type="hidden" name="containerID" value="{$containerID}" />
		<input type="hidden" name="MAX_FILE_SIZE" value="{$importLimit}" /> {* restrict file size *}
		<input type="submit" name="UploadFile" value="{lang_get s='btn_upload_file'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/testcases/tcImport.php';" />
	</div>
</form>
{else}
	{foreach item=result from=$resultMap}
		{lang_get s='title_imp_tc_data'} : <b>{$result[0]|escape}</b> : {$result[1]|escape}<br />
	{/foreach}
	{include file="inc_refreshTree.tpl"}
{/if}
{if $bImport > 0}
	{include file="inc_refreshTree.tpl"}
{/if}

</div>

</body>
</html>