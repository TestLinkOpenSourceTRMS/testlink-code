{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcExport.tpl,v 1.14 2010/11/06 11:42:47 amkhullar Exp $

test case export initial page

@internal revisions
@since 1.9.15

*}

{lang_get var="labels"
          s='export_filename,warning_empty_filename,file_type,warning,export_cfields,title_req_export,
             view_file_format_doc,export_with_keywords,btn_export,export_tcase_external_id,btn_cancel,
             view_file_format_doc,export_with_prefix,export_summary,export_steps,export_preconditions,
             export_testcase_requirements,export_attachments'}

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

function mirrorCheckbox(sourceOID,targetOID)
{
  var scb = document.getElementById(sourceOID);
  var tcb = document.getElementById(targetOID);

  if(scb.checked)
  {
    tcb.disabled = 0;
  }
  else
  {
    tcb.checked = 0;
    tcb.disabled = 1;
  }
}


</script>
</head>

<body>
<h1 class="title">{$gui->page_title}{$smarty.const.TITLE_SEP}{$gui->object_name|escape}</h1>

<div class="workBack">

{if $gui->do_it eq 1}
  <form method="post" id="export_xml" enctype="multipart/form-data"
        action="{$basehref}lib/testcases/tcExport.php"
        onSubmit="javascript:return validateForm(this);">

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
	  <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$labels.view_file_format_doc}</a>
  	</td>
  	</tr>
    <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>

    <tr>
    <td>{$labels.export_tcase_external_id}</td>
    <td><input type="checkbox" name="exportTestCaseExternalID" id="exportTestCaseExternalID"
               value="1" onclick="mirrorCheckbox('exportTestCaseExternalID','addPrefix');" checked />
    {$labels.export_with_prefix}
    <input type="checkbox" name="addPrefix" id="addPrefix" value="1">
    </td>
    <tr>
    <td>{$labels.export_summary}</td>
    <td><input type="checkbox" name="exportTCSummary" value="1" checked /></td>
    </tr>
    <tr>
    <td>{$labels.export_preconditions}</td>
    <td><input type="checkbox" name="exportTCPreconditions" value="1" checked /></td>
    </tr>
    <tr>
    <td>{$labels.export_steps}</td>
    <td><input type="checkbox" name="exportTCSteps" value="1" checked /></td>
    </tr>

    </tr>
    <tr>
    <td>{$labels.export_testcase_requirements}</td>
    <td><input type="checkbox" name="exportReqs" value="1" checked /></td>
    </tr>
    <tr>
    <td>{$labels.export_cfields}</td>
    <td><input type="checkbox" name="exportCFields" value="1" checked /></td>
    </tr>
    <tr>
    <td>{$labels.export_with_keywords}</td>
    <td><input type="checkbox" name="exportKeywords" value="1" /></td>
    </tr>
	<tr>
    <td>{$labels.export_attachments}</td>
    <td><input type="checkbox" name="exportAttachments" value="1" /></td>
    </tr>




  	</table>

  	<div class="groupBtn">
      <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
  		<input type="hidden" name="testcase_id" value="{$gui->tcID}" />
  		<input type="hidden" name="tcversion_id" value="{$gui->tcVersionID}" />
  		<input type="hidden" name="containerID" value="{$gui->containerID}" />
  		<input type="hidden" name="useRecursion" value="{$gui->useRecursion}" />
  		<input type="submit" name="export" value="{$labels.btn_export}" />
  		<input type="button" name="cancel" value="{$labels.btn_cancel}"
    		     {if $gui->goback_url != ''}  onclick="location='{$gui->goback_url}'"
             {elseif $gui->cancelActionJS != ''} onclick="javascript:{$gui->cancelActionJS};"
    		     {else}  onclick="javascript:history.back();" {/if} />
  	</div>
  </form>
{else}
	{$gui->nothing_todo_msg|escape}
{/if}

</div>

</body>
</html>
