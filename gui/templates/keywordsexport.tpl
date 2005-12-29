{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: keywordsexport.tpl,v 1.1 2005/12/29 21:03:09 schlundus Exp $ *}
{* Purpose: smarty template - keyword import initial page *}
{* revisions:
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_req_import_to'} {$reqSpec.title|escape}</h1>

<div class="workBack">

<form method="post" enctype="multipart/form-data">

	<h2>{lang_get s='title_choose_file_type'}</h2>
	<p>{lang_get s='req_import_type'}
	<select name="exportType">
		{html_options options=$importTypes}
	</select>
	</p>
	<div class="groupBtn">
		<input type="hidden" name="prodID" value="{$productID}" />
		<input type="submit" name="export" value="{lang_get s='btn_export'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/keywords/keywordsView.php';" />
	</div>
</form>

</div>

</body>
</html>