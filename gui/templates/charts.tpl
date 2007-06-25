{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: charts.tpl,v 1.2 2007/06/25 06:21:29 franciscom Exp $ *}
{* Purpose: smarty template - show graphical charts *}
{include file="inc_head.tpl"}
{$codex}
<body>
<h1>{lang_get s='graphical_reports'}</h1>
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

<h6>{lang_get s='maani_copyright'}</h6>

{foreach from=$charts key=title item=code}
<div class="workBack">
<h3>{$title|escape}</h3>
	{$code}
</div>
{/foreach}

</body>
</html>
