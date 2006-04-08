{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: reqexport.tpl,v 1.1 2006/04/08 19:53:56 schlundus Exp $ *}
{* Purpose: smarty template - req export initial page *}
{* revisions:
*}
{include file="inc_head.tpl"}

<body>
<h1>{lang_get s='title_req_export_to'} {$reqSpec.title|escape}</h1>

<div class="workBack">

<form method="post" enctype="multipart/form-data" action="lib/req/reqexport.php">

	<h2>{lang_get s='title_choose_file_type'}</h2>
	<p>{lang_get s='req_import_type'}
	<select name="exportType">
		{html_options options=$importTypes}
	</select>
	</p>
	<div class="groupBtn">
		<input type="hidden" name="idSRS" value="{$idSRS}" />
		<input type="submit" name="export" value="{lang_get s='btn_export'}" />
		<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
			onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$idSRS}';" />
	</div>
</form>

</div>

</body>
</html>