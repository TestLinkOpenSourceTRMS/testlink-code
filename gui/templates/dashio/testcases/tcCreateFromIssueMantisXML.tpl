{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource: tcCreateFromIssueMantisXML.tpl
Purpose: smarty template - 
*}
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
          s='file_type,view_file_format_doc,local_file,
             max_size_cvs_file1,max_size_cvs_file2,btn_upload_file,
             duplicate_criteria,action_for_duplicates,testcase,testsuite,
             action_on_duplicated_name,warning,btn_cancel,title_imp_tc_data'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}
</head>
<body>

<h1 class="{#TITLE_CLASS#}">{$labels.testsuite}{$smarty.const.TITLE_SEP}{$gui->container_name|escape}</h1>

<div class="workBack">
<h1 class="title">{$gui->import_title}</h1>

{if $gui->resultMap eq null}
<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">

  <table>
  <tr>
  <td> {$labels.file_type} </td>
  <td> <select name="importType" id="importType">
         {html_options options=$gui->importTypes}
	     </select>
	<a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$labels.view_file_format_doc}</a>
	</td>
	</tr>
	<tr><td>{$labels.local_file} </td>
	    <td><input type="file" name="uploadedFile" 
	                           size="{#FILENAME_SIZE#}" maxlength="{#FILENAME_MAXLEN#}"/></td>
	</tr>
	</table>
	<p>{$labels.max_size_cvs_file1} {$gui->importLimitKB} {$labels.max_size_cvs_file2}</p>
	<div class="groupBtn">
		<input type="hidden" name="containerID" value="{$gui->containerID}" />
		<input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimitBytes}" /> {* restrict file size *}
		<input class="{#BUTTON_CLASS#}" type="submit" 
           name="UploadFile"  id="UploadFile"
           value="{$labels.btn_upload_file}" />

		<input class="{#BUTTON_CLASS#}" type="button" 
           name="cancel" id="cancel"
           value="{$labels.btn_cancel}" onclick="javascript:history.back();" />
	</div>
</form>
{else}
  {foreach item=result from=$gui->resultMap}
    {$labels.testcase} : <b>{$result[0]|escape}</b> : {$result[1]|escape}<br>
  {/foreach}

  {include file="inc_refreshTree.tpl"}
{/if}

{if $gui->refreshTree}
	{include file="inc_refreshTree.tpl"}
{/if}

{if $gui->file_check.status_ok eq 0}
  <script type="text/javascript">
  alert_message("{$labels.warning|escape:'javascript'}","{$gui->file_check.msg|escape:'javascript'}");
  </script>
{/if}  


</div>

</body>
</html>