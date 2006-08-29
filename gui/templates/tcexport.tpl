{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcexport.tpl,v 1.1 2006/08/29 20:26:20 schlundus Exp $ *}
{* Purpose: smarty template - keyword export initial page *}
{* revisions:
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_req_import_to'} {$reqSpec.title|escape}</h1>

<div class="workBack">

<form method="post" enctype="multipart/form-data" action="lib/testcases/tcexport.php">

	<h2>{lang_get s='title_choose_file_type'}</h2>
	<p>{lang_get s='req_import_type'}
	<select name="exportType">
		{html_options options=$exportTypes}
	</select>
	</p>
	<div class="groupBtn">
		<input type="hidden" name="tcID" value="{$tcID}" />
		<input type="hidden" name="tcVersionID" value="{$tcVersionID}" />
		<input type="submit" name="export" value="{lang_get s='btn_export'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: history.back();" />
	</div>
</form>

</div>

</body>
</html>