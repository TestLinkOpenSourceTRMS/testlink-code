{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
Filename: tcPrint.tpl

Print just ONE test case

Revisions:
20110302 - franciscom - start
*}
{lang_get var="labels" 
          s='export_filename,warning_empty_filename,file_type,warning,export_cfields,title_req_export,
             view_file_format_doc,export_with_keywords,btn_export,btn_cancel'} 

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
</head>

<body>
<h1 class="title">{$gui->page_title|escape}</h1>

<div class="workBack">
<form method="post" id="print_testcase" action="lib/testcases/tcPrint.php">
	<input type="hidden" name="testcase_id" value="{$gui->tcase_id}">
	<input type="hidden" name="tcversion_id" value="{$gui->tcversion_id}">

	<select id="outputFormat" name="outputFormat" onchange='submit();'>
		{html_options options=$gui->outputFormatDomain selected=''}
	</select>
</form>
</div>

</body>
</html>
