{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcImport.tpl,v 1.9 2010/02/21 15:07:49 franciscom Exp $
Purpose: smarty template - manage import of test cases and test suites

rev: 20091122 - franciscom - refacoting to use alert_message()
*}

{lang_get var="labels"
          s='file_type,view_file_format_doc,local_file,
             max_size_cvs_file1,max_size_cvs_file2,btn_upload_file,
             action_on_duplicated_name,warning,btn_cancel,title_imp_tc_data'}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}
</head>
<body>

<h1 class="title">{$container_description}{$smarty.const.TITLE_SEP}{$container_name|escape}</h1>

<div class="workBack">
<h1 class="title">{$import_title}</h1>

{if $resultMap eq null}
<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">

  <table>
  <tr>
  <td> {$labels.file_type} </td>
  <td> <select name="importType">
         {html_options options=$gui->importTypes}
	     </select>
	<a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$labels.view_file_format_doc}</a>
	</td>
	</tr>
	<tr><td>{$labels.local_file} </td>
	    <td><input type="file" name="uploadedFile" 
	                           size="{#FILENAME_SIZE#}" maxlength="{#FILENAME_MAXLEN#}"/></td>
	</tr>
	{if $gui->actionOptions != ''}
	<tr><td>{$labels.action_on_duplicated_name} </td>
	    <td><select name="action_on_duplicated_name">
				  {html_options options=$gui->actionOptions selected=$gui->action_on_duplicated_name}
			    </select>
    </td>
	</tr>
	{/if}

	</table>
	<p>{$labels.max_size_cvs_file1} {$gui->importLimitKB} {$labels.max_size_cvs_file2}</p>
	<div class="groupBtn">
		<input type="hidden" name="useRecursion" value="{$gui->useRecursion}" />
		<input type="hidden" name="bIntoProject" value="{$bIntoProject}" />
		<input type="hidden" name="containerID" value="{$containerID}" />
		<input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimitKB}" /> {* restrict file size *}
		<input type="submit" name="UploadFile" value="{$labels.btn_upload_file}" />
		<input type="button" name="cancel" value="{$labels.btn_cancel}" 
			                   onclick="javascript:history.back();" />
	</div>
</form>
{else}
	{foreach item=result from=$resultMap}
		{$labels.title_imp_tc_data} : <b>{$result[0]|escape}</b> : {$result[1]|escape}<br />
	{/foreach}
  {include file="inc_refreshTree.tpl"}
{/if}

{if $bImport > 0}
	{include file="inc_refreshTree.tpl"}
{/if}

{if $file_check.status_ok eq 0}
  <script type="text/javascript">
  alert_message("{$labels.warning}","{$file_check.msg}");
  // alert("{$file_check.msg}");
  </script>
{/if}  


</div>

</body>
</html>