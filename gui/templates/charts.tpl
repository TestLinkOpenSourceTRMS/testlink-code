{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: charts.tpl,v 1.4 2008/05/06 06:25:29 franciscom Exp $ *}
{* Purpose: smarty template - show graphical charts *}
{include file="inc_head.tpl"}
{$codex}
<body>
<h1 class="title">{lang_get s='graphical_reports'}</h1>
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

<div class="workBack">
{foreach from=$charts key=title item=code}
<h3>{$title|escape}</h3>
	{$code}
{/foreach}
</div>

<p>{lang_get s='maani_copyright'}</p>

</body>
</html>
