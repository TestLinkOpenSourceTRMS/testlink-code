{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: keywordsImport.tpl,v 1.4 2009/08/24 19:18:45 schlundus Exp $ *}
{* Purpose: smarty template - keyword import initial page *}
{* revisions:
   20051231 - scs - fixed incorrect cancel button link
*}
{include file="inc_head.tpl"}

<body>
<h1 class="title">{lang_get s='testproject'}{$smarty.const.TITLE_SEP}{$tproject_name|escape}</h1>

<div class="workBack">
<h1 class="title">{lang_get s='title_keyword_import'}</h1>

<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">
  <table>
	<tr>
		<td>{lang_get s='file_type'}</td>
		<td>
			<select name="importType">
				{html_options options=$importTypes selected=$import_type_selected}
			</select>
				<a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{lang_get s="view_file_format_doc"}</a>

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
			{$fileSizeLimitMsg}
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
    <script type="text/javascript">
    alert("{$msg}");
    </script>
{/if}  

</div>

</body>
</html>