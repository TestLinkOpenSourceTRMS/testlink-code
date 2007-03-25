{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: charts.tpl,v 1.1 2007/03/25 20:11:59 schlundus Exp $ *}
{* Purpose: smarty template - show graphical charts *}
{include file="inc_head.tpl"}
{$codex}
<body>
<h1>{$tpname|escape} - {lang_get s='graphical_reports'}</h1>
<h6>{lang_get s='maani_copyright'}</h6>

{foreach from=$charts key=title item=code}
<div class="workBack">
<h3>{$title|escape}</h3>
	{$code}
</div>
{/foreach}

</body>
</html>
