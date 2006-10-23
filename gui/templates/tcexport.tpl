{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcexport.tpl,v 1.3 2006/10/23 06:42:22 franciscom Exp $ *}
{* Purpose: smarty template - keyword export initial page *}
{* revisions:
*}
{include file="inc_head.tpl"}

<body>
<h1>{$page_title} {$object_name|escape}</h1>

<div class="workBack">

<form method="post" enctype="multipart/form-data" action="lib/testcases/tcexport.php">

	<h2>{lang_get s='title_choose_file_type'}</h2>
	<p>{lang_get s='req_import_type'}
	<select name="exportType">
		{html_options options=$exportTypes}
	</select>
	</p>
	<div class="groupBtn">
		<input type="hidden" name="testcase_id" value="{$tcID}" />
		<input type="hidden" name="tcversion_id" value="{$tcVersionID}" />
		<input type="hidden" name="containerID" value="{$containerID}" />
		<input type="hidden" name="bRecursive" value="{$bRecursive}" />
		<input type="checkbox" name="bKeywords" value="0" />{lang_get s='export_with_keywords'}<br /><br />
		<input type="submit" name="export" value="{lang_get s='btn_export'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: history.back();" />
	</div>
</form>

</div>

</body>
</html>