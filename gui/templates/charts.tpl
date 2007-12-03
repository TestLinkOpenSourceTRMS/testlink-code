{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: charts.tpl,v 1.3 2007/12/03 22:55:14 havlat Exp $ *}
{* Purpose: smarty template - show graphical charts *}
{include file="inc_head.tpl"}
{$codex}
<body>
<h1>{lang_get s='graphical_reports'}</h1>
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
