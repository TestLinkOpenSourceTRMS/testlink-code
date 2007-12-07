{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: keywordsimport.tpl,v 1.1 2007/12/07 07:02:59 franciscom Exp $ *}
{* Purpose: smarty template - keyword import initial page *}
{* revisions:
   20051231 - scs - fixed incorrect cancel button link
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='testproject'}{$smarty.const.TITLE_SEP}{$tproject_name|escape}</h1>

<div class="workBack">
<h1>{lang_get s='title_keyword_import'}</h1>
<p class="hint">	
  {lang_get s='supported_file_formats'}<br/>
	{foreach key=k item=i from=$keywordFormatStrings}
		{$k|escape} : {$i|escape}<br />
	{/foreach}
</p>

<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">
  <table>
	<tr>
		<td>{lang_get s='import_file_type'}</td>
		<td>
			<select name="importType">
				{html_options options=$importTypes selected=$import_type_selected}
			</select>
		</td>
	</tr>
	<tr>
		<td>
			{lang_get s='keywords_file'}
		</td>
		<td>
			<input type="hidden" name="MAX_FILE_SIZE" value="{$importLimit}" /> {* restrict file size *}
			<input type="file" name="uploadedFile" size="30" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			{lang_get s='max_file_size_is'} {$importLimit/1024} {lang_get s='max_size_cvs_file2'}
		</td>
	</tr>
	</table>
	<br/>
	<div class="groupBtn">
		<input type="hidden" name="tproject_id" value="{$tproject_id}" />
		<input type="submit" name="UploadFile" value="{lang_get s='btn_upload_file'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/keywords/keywordsView.php';" />
	</div>
</form>

{if $msg neq ''}
    <script>
    alert("{$msg}");
    </script>
{/if}  

</div>

</body>
</html>