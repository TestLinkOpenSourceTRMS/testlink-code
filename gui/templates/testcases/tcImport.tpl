{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 

Purpose: smarty template - manage import of test cases and test suites

@filesource	tcImport.tpl
@internal revisions
20100821 - franciscom - refactoring to use $gui 
*}

{lang_get var="labels"
          s='file_type,view_file_format_doc,local_file,
             max_size_cvs_file1,max_size_cvs_file2,btn_upload_file,
             duplicate_criteria,action_for_duplicates,
             action_on_duplicated_name,warning,btn_cancel,title_imp_tc_data'}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl"}
</head>
<body>

<h1 class="title">{$gui->container_description}{$smarty.const.TITLE_SEP}{$gui->container_name|escape}</h1>

<div class="workBack">
<h1 class="title">{$gui->import_title}</h1>

{if $gui->resultMap eq null}
<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">

  {* restrict file size *}
  {* 
  	***** Info from PHP Manual *****
  	The MAX_FILE_SIZE hidden field (measured in bytes) must precede the file input field, 
  	and its value is the maximum filesize accepted by PHP. 
  	This form element should always be used as it saves users the trouble of waiting for a big file being 
  	transferred only to find that it was too large and the transfer failed. 
  	Keep in mind: fooling this setting on the browser side is quite easy, 
  	so never rely on files with a greater size being blocked by this feature. 
  	It is merely a convenience feature for users on the client side of the application. 
  	The PHP settings (on the server side) for maximum-size, however, cannot be fooled. 
  *}
  
  
  <input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimitBytes}" /> 

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
	{if $gui->hitOptions != ''}
	  <tr><td>{$labels.duplicate_criteria} </td>
	      <td><select name="hit_criteria" id="hit_criteria">
	  			  {html_options options=$gui->hitOptions selected=$gui->hitCriteria}
	  		    </select>
      </td>
	  </tr>
	{/if}

	{if $gui->actionOptions != ''}
	<tr><td>{$labels.action_for_duplicates} </td>
	    <td><select name="action_on_duplicated_name">
				  {html_options options=$gui->actionOptions selected=$gui->action_on_duplicated_name}
			    </select>
    </td>
	</tr>
	{/if}

	</table>
	<p>{$labels.max_size_cvs_file1} {$gui->importLimitKB} {$labels.max_size_cvs_file2}</p>
	<div class="groupBtn">
		<input type="hidden" id="tproject_id" name="tproject_id" value="{$gui->tproject_id}" />
		<input type="hidden" name="useRecursion" value="{$gui->useRecursion}" />
		<input type="hidden" name="bIntoProject" value="{$gui->bIntoProject}" />
		<input type="hidden" name="containerID" value="{$gui->containerID}" />
		<input type="submit" name="UploadFile" value="{$labels.btn_upload_file}" />
		<input type="button" name="cancel" value="{$labels.btn_cancel}" onclick="javascript:history.back();" />
	</div>
</form>
{else}
	{foreach item=result from=$gui->resultMap}
		{$labels.title_imp_tc_data} : <b>{$result[0]|escape}</b> : {$result[1]|escape}<br />
	{/foreach}
  {$tlRefreshTreeByReloadJS}
{/if}

{if $gui->bImport > 0} {$tlRefreshTreeByReloadJS} {/if}

{if $gui->file_check.status_ok == 0}
  <script type="text/javascript">
  // just for debug alert("{$gui->file_check.msg}");
  alert_message("{$labels.warning|escape:'javascript'}","{$gui->file_check.msg|escape:'javascript'}");
  </script>
{/if}  


</div>

</body>
</html>