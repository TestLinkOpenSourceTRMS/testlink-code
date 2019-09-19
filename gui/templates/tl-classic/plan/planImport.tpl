{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planImport.tpl,v 1.3 2010/11/06 11:42:47 amkhullar Exp $
Purpose: manage import of test plan links (test cases and platforms)

rev:
*}

{lang_get var="labels"
          s='file_type,view_file_format_doc,local_file,
             max_size_cvs_file1,max_size_cvs_file2,btn_upload_file,
             duplicate_criteria,action_for_duplicates,
             action_on_duplicated_name,warning,btn_cancel,title_imp_tc_data'}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}
</head>
<body>

<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
<h1 class="title">{$gui->import_title}</h1>

{if $gui->resultMap eq null}
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

	</table>
	<p>{$labels.max_size_cvs_file1} {$gui->importLimitKB} {$labels.max_size_cvs_file2}</p>
	<div class="groupBtn">
		{* standard way to restrict file size - seems Chrome does not understand this *}
		<input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimitBytes}" /> 
		<input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />

		<input type="submit" name="uploadFile" value="{$labels.btn_upload_file}" />
		<input type="button" name="cancel" value="{$labels.btn_cancel}"  onclick="javascript:history.back();" />
	</div>
</form>
{else}
	{foreach item=result from=$gui->resultMap}
		<b>{$result[0]|escape}</b> : {$result[1]|escape}<br />
	{/foreach}
  {include file="inc_refreshTree.tpl"}
{/if}

{if $gui->import_done}
	{include file="inc_refreshTree.tpl"}
{/if}

{if $gui->file_check.status_ok eq 0}
  <script type="text/javascript">
//  BUGID 3943: Escape all messages (string)
  alert_message("{$labels.warning}","{$gui->file_check.msg|escape:'javascript'}");
  // alert("{$gui->file_check.msg}");
  </script>
{/if}  
</div>

</body>
</html>