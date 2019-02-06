{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: cfieldsImport.tpl,v 1.3 2010/11/06 11:42:47 amkhullar Exp $
Purpose: smarty template - manage import of custom fields

rev: 
*}

{lang_get var="labels"
          s='file_type,view_file_format_doc,local_file,warning,warning_empty_filename,
             max_size_cvs_file1,max_size_cvs_file2,btn_upload_file,
             btn_goback,not_imported,imported,btn_cancel,title_imp_tc_data'}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}
{literal}
<script type="text/javascript">
{/literal}
// BUGID 3943: Escape all messages (string)
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_filename = "{$labels.warning_empty_filename|escape:'javascript'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.targetFilename.value)) 
  {
      alert_message(alert_box_title,warning_empty_filename);
      selectField(f, 'targetFilename');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>


<body>

<h1 class="title">{$gui->page_title|escape}</h1>

<div class="workBack">
{if $gui->file_check.show_results}
	  {if $gui->file_check.import_msg.ok != ''}
	      {$labels.imported}<br>
	      {foreach item=result from=$gui->file_check.import_msg.ok}
	      	<b>{$result|escape}</b><br />
	      {/foreach}
	  {/if} 
	  <br>
	  {if $gui->file_check.import_msg.ko != ''}
	      {$labels.not_imported}<br>
	      {foreach item=result from=$gui->file_check.import_msg.ko}
	      	<b>{$result|escape}</b><br />
	      {/foreach}
	  {/if} 
	  <form method="post" action="{$SCRIPT_NAME}">
	      <br>
	  		<input type="button" name="goback" value="{$labels.btn_goback}"
    		                     {if $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
    		                     {else}  onclick="javascript:history.back();" {/if} />
	  </form>
    
{else}
    <form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}"
          onSubmit="javascript:return validateForm(this);">
    
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
    	    <td><input type="file" name="targetFilename" value=""
    	                           size="{#FILENAME_SIZE#}" maxlength="{#FILENAME_MAXLEN#}"/></td>
    	</tr>
    	</table>
    	<p>{$labels.max_size_cvs_file1} {$gui->importLimitKB} {$labels.max_size_cvs_file2}</p>
    	<div class="groupBtn">
    		<input type="hidden" name="doAction" id="doAction" value="doImport" />
    		{* restrict file size - input name must be UPPER CASE ??? *}
    		<input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimitKB}" /> 
    		<input type="submit" name="UploadFile" value="{$labels.btn_upload_file}" />
    		<input type="button" name="cancel" value="{$labels.btn_cancel}"
    		                     {if $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
    		                     {else}  onclick="javascript:history.back();" {/if} />
    	</div>
    </form> 
{/if}


{if $gui->file_check.status_ok eq 0}
    <script>
    alert_message(alert_box_title,"{$gui->file_check.msg|escape:'javascript'}");
    </script>
{/if}  


</div>

</body>
</html>