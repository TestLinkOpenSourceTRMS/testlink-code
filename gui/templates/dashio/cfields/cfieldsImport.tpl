{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource cfieldsImport.tpl
Purpose: smarty template - manage import of custom fields

*}
{$cfg_section = $smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
          s='file_type,view_file_format_doc,local_file,warning,
             warning_empty_filename,
             max_size_cvs_file1,max_size_cvs_file2,btn_upload_file,
             btn_goback,not_imported,imported,btn_cancel,title_imp_tc_data'}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}
<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_filename = "{$labels.warning_empty_filename|escape:'javascript'}";

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

</head>


<body>
{include file="aside.tpl"}  
<div id="main-content">

<h1 class="{#TITLE_CLASS#}">{$gui->page_title|escape}</h1>

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
	  		<input class="{#BUTTON_CLASS#}" type="button" 
               name="goback" id="goback" 
               value="{$labels.btn_goback}"
    		                     {if $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
    		                     {else}  onclick="javascript:history.back();" {/if} />
	  </form>
    
{else}
    <form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}"
          onSubmit="javascript:return validateForm(this);">
    
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
    	    <td><input type="file" 
                     name="targetFilename" id="targetFilename"
                     value="" size="{#FILENAME_SIZE#}" 
                     maxlength="{#FILENAME_MAXLEN#}"/></td>
    	</tr>
    	</table>
    	<p>{$labels.max_size_cvs_file1} {$gui->importLimitKB} {$labels.max_size_cvs_file2}</p>
    	<div class="groupBtn">
    		<input type="hidden" name="doAction" id="doAction" value="doImport" />
    		{* restrict file size - input name must be UPPER CASE ??? *}
    		<input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimitKB}" /> 
    		<input class="{#BUTTON_CLASS#}" type="submit" 
               name="UploadFile" id="UploadFile" 
               value="{$labels.btn_upload_file}" />

    		<input class="{#BUTTON_CLASS#}" type="button" 
               name="cancel" id="cancel"
               value="{$labels.btn_cancel}"
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
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>