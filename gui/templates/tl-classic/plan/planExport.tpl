{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: planExport.tpl,v 1.7 2010/11/06 11:42:47 amkhullar Exp $ 

test plan export

internal revisions
*}
{lang_get var="labels" 
          s='export_filename,warning_empty_filename,file_type,warning,export_cfields,title_req_export,
             view_file_format_doc,export_with_keywords,btn_export,btn_cancel,view_file_format_doc'} 

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_filename = "{$labels.warning_empty_filename|escape:'javascript'}";
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
</head>

<body>
<h1 class="title">{$gui->page_title}{$smarty.const.TITLE_SEP}{$gui->object_name|escape}</h1>

<div class="workBack">

{if $gui->do_it eq 1}
  <form method="post" id="export_xml" enctype="multipart/form-data" 
        action="lib/plan/planExport.php"
        onSubmit="javascript:return validateForm(this);">
    <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
    <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">
    <input type="hidden" name="platform_id" id="platform_id" value="{$gui->platform_id}">
    <input type="hidden" name="build_id" id="build_id" value="{$gui->build_id}">
    <input type="hidden" name="exportContent" id="exportContent" value="{$gui->exportContent}">
    <input type="hidden" name="form_token" id="form_token" value="{$gui->treeFormToken}">
    <table>
    <tr>
    <td>
    {$labels.export_filename}
    </td>
    <td>
  	<input type="text" id="export_filename" name="export_filename" maxlength="{#FILENAME_MAXLEN#}" 
			           value="{$gui->export_filename|escape}" size="{#FILENAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="export_filename"}
  	</td>
  	<tr>
  	<td>{$labels.file_type}</td>
  	<td>
  	<select name="exportType">
  		{html_options options=$gui->exportTypes}
  	</select>
	  <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$labels.view_file_format_doc}</a>
  	</td>
  	</tr>
  	</table>
  	
  	<div class="groupBtn">
  		<input type="submit" name="export" value="{$labels.btn_export}" />
  		<input type="button" name="cancel" value="{$labels.btn_cancel}"
    		     {if $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
             {elseif $gui->closeOnCancel} onclick="window.close();"
    		     {else}  onclick="javascript:history.back();" {/if} />
  	</div>
  </form>
{else}
	{$gui->nothing_todo_msg|escape}
{/if}

</div>

</body>
</html>