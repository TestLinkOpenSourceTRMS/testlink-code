{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: resultsImport.tpl,v 1.2 2008/05/06 06:26:11 franciscom Exp $
Purpose: smarty template - manage import of test cases and test suites
*}
{include file="inc_head.tpl"}

<body>
{config_load file="input_dimensions.conf" section="tcImport"} {* Constant definitions *}

<h1 class="title">{$container_description}{$smarty.const.TITLE_SEP}{$container_name|escape}</h1>

<div class="workBack">
<h1 class="title">{$import_title}</h1>

{if $resultMap eq null}
<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">
  <table>
  <tr>
  	<td>{lang_get s='file_type'}</td>
    <td><select name="importType">
		      {html_options options=$importTypes}
	      </select>
      	<a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{lang_get s="view_file_format_doc"}</a>
	  </td>
  </tr>
  	
	<tr>
	 <td>{lang_get s='local_file'}</td> 
	 <td><input type="file" name="uploadedFile" 
	                        size="{#FILENAME_SIZE#}" maxlength="{#FILENAME_MAXLEN#}"/></td>
  </tr>                              
	</table>
	<p>{lang_get s='max_size_cvs_file1'} {$importLimitKB} {lang_get s='max_size_cvs_file2'}</p>
	
	<div class="groupBtn">
		<input type="hidden" name="bRecursive" value="{$bRecursive}" />
		<input type="hidden" name="build" value="{$buildID}" />
		<input type="hidden" name="bIntoProject" value="{$bIntoProject}" />
		<input type="hidden" name="containerID" value="{$containerID}" />
		<input type="hidden" name="MAX_FILE_SIZE" value="{$importLimit}" /> {* restrict file size *}
		<input type="submit" name="UploadFile" value="{lang_get s='btn_upload_file'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/results/resultsImport.php';" />
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

{* 20061114 - franciscom *}
{if $file_check.status_ok eq 0}
    <script>
    alert("{$file_check.msg}");
    </script>
{/if}  


</div>

</body>
</html>