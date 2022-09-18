{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource tcImport.tpl
Purpose: smarty template - manage import of test cases and test suites

*}

{lang_get var="labels"
          s='file_type,view_file_format_doc,local_file,
             max_size_cvs_file1,max_size_cvs_file2,btn_upload_file,
             duplicate_criteria,action_for_duplicates,testcase,
             action_on_duplicated_name,warning,btn_cancel,title_imp_tc_data'}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_del_onclick.tpl"}
</head>
<body>

<h1 class="{#TITLE_CLASS#}">{$gui->pageTitle|escape}</h1>

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
	<p>{$labels.max_size_cvs_file1} {$gui->importLimitKB|escape} {$labels.max_size_cvs_file2}</p>
	<div class="groupBtn">
		<input type="hidden" name="useRecursion" value="{$gui->useRecursion|escape}" />
		<input type="hidden" name="bIntoProject" value="{$gui->bIntoProject|escape}" />
		<input type="hidden" name="containerID" value="{$gui->containerID}" />
    <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />

    {* to save context *}       
    <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />

    {* restrict file size *}           
		<input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimitBytes|escape}" /> 

		<input class="{#BUTTON_CLASS#}" type="submit" 
           name="UploadFile" id="UploadFile"
           value="{$labels.btn_upload_file}" />
      <input class="{#BUTTON_CLASS#}" type="button" 
             name="cancel" id="cancel" 
             value="{$labels.btn_cancel}"
             {if property_exists($gui,'goback_url') && $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
             {elseif $gui->cancelActionJS != ''} onclick="javascript:{$gui->cancelActionJS};"
             {else}  onclick="javascript:history.back();" {/if} />
	</div>
</form>
{else}
  {foreach item=result from=$gui->resultMap}
    {$labels.testcase} : <b>{$result[0]|escape}</b> : {$result[1]|escape}<br>
  {/foreach}

  {include file="inc_refreshTree.tpl"}
{/if}

{if $gui->bImport > 0}
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