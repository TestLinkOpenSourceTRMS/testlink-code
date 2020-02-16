{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: infoWindow.tpl,v 1.2 2008/05/06 06:25:29 franciscom Exp $ *}
{* Author martin Havlat *}
{* Purpose: smarty template - view info in top window 
 * Using: define $internalTemplate with included template (see e.g. int_ReqView.tpl)
 * Show the content as text without links
 *}
{* Revisions:
*}

{include file="inc_head.tpl"}
<body>
<h1 class="title">{$title|escape|default:"Info"}</h1>
<div class="groupBtn">
	<input type="button" name="closeWindow" value="{lang_get s='btn_close'}" 
		onclick="window.close()" />
</div>

<div class="workBack">

{include file=$internalTemplate}

</div>

</body>
</html>