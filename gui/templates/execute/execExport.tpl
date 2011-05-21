{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource	execExport.tpl

execution test case set export

@internal revisions
20100926- franciscom - BUGID 3421: Test Case Execution feature - Add Export All test Case in TEST SUITE button
*}
{lang_get var="labels" 
          s='export_filename,warning_empty_filename,file_type,warning,export_cfields,title_req_export,
             view_file_format_doc,export_with_keywords,btn_export,btn_cancel'} 

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}

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
        action="lib/execute/execExport.php"
        onSubmit="javascript:return validateForm(this);">
    <input type="hidden" name="tprojectID" id="tprojectID" value="{$gui->tproject_id}">
    <input type="hidden" name="tplanID" id="tplanID" value="{$gui->tplan_id}">
    <input type="hidden" name="platformID" id="platformID" value="{$gui->platform_id}">
    <input type="hidden" name="buildID" id="buildID" value="{$gui->build_id}">
    <input type="hidden" name="tsuiteID" id="tsuiteID" value="{$gui->tsuite_id}">
    <input type="hidden" name="tcversionSet" id="tcversionSet" value="{$gui->tcversionSet}">
    <table>
    <tr>
    <td>
    {$labels.export_filename}
    </td>
    <td>
  	<input type="text" name="export_filename" maxlength="{#FILENAME_MAXLEN#}" 
			           value="{$gui->export_filename|escape}" size="{#FILENAME_SIZE#}"/>
			  				{include file="error_icon.tpl" field="export_filename"}
  	</td>
  	<tr>
  	<td>{$labels.file_type}</td>
  	<td>
  	<select name="exportType">
  		{html_options options=$gui->exportTypes}
  	</select>
	  <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{lang_get s="view_file_format_doc"}</a>
  	</td>
  	</tr>
  	</table>
  	
  	<div class="groupBtn">
  		<input type="submit" name="export" value="{$labels.btn_export}" />
      {if $gui->drawCancelButton}
  		<input type="button" name="cancel" value="{$labels.btn_cancel}"
    		     {if $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
    		     {else}  onclick="javascript:history.back();" {/if} />
      {/if}
  	</div>
  </form>
{else}
	{$gui->nothing_todo_msg|escape}
{/if}

</div>

</body>
</html>