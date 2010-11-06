{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: platformsExport.tpl,v 1.3 2010/11/06 11:42:47 amkhullar Exp $ 
Purpose: smarty template - platforms export
rev:

*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

{lang_get var="labels" 
          s='btn_export,btn_cancel,warning,export_filename,file_type,
             view_file_format_doc,warning_empty_filename'}

{literal}
<script type="text/javascript">
{/literal}
// BUGID 3943: Escape all messages (string)
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_filename = "{$labels.warning_empty_filename|escape:'javascript'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.export_filename.value)) 
  {
      alert_message(alert_box_title,warning_empty_filename);
      selectField(f, 'export_filename');
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
{if $gui->do_it eq 1}
  <form method="post" id="export_xml" enctype="multipart/form-data" 
        action="lib/platforms/platformsExport.php"
        onsubmit="javascript:return validateForm(this);">
  
    <table>
	    <tr>
	    	<td>{$labels.export_filename}</td>
	    	<td>
	  			<input type="text" name="export_filename" maxlength="{#FILENAME_MAXLEN#}" 
				       value="{$gui->export_filename|escape}" size="{#FILENAME_SIZE#}"/>
			  	{include file="error_icon.tpl" field="export_filename"}
	  		</td>
	  	</tr>
	  	<tr>
	  		<td>{$labels.file_type}</td>
	  		<td>
	  			<select name="exportType">
	  				{html_options options=$gui->exportTypes}
	  			</select>
		  		<a href="{$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}">{$labels.view_file_format_doc}</a>
	  		</td>
	  	</tr>
  	</table>
  	
  	<div class="groupBtn">
  	  	<input type="hidden" name="doAction" id="doAction" value="" />
  		<input type="submit" name="doExport" id="doExport" value="{$labels.btn_export}" 
  		                     onclick="doAction.value=this.id" />
    	<input type="button" name="cancel" value="{$labels.btn_cancel}"
    		  {if $gui->goback_url  != ''}  onclick="location='{$gui->goback_url }'"
    		  {else}  onclick="javascript:history.back();" {/if} />
  	</div>
  </form>
{else}
	{$gui->nothing_todo_msg|escape}
{/if}

</div>

</body>
</html>
