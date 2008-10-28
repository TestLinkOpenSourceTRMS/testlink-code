{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: charts.tpl,v 1.1 2008/10/28 09:54:11 franciscom Exp $ *}
{* Purpose: smarty template - show graphical charts *}
{include file="inc_head.tpl"}
{* Who creates it ?*}
{*  {$codex} *}
<body>
<h1 class="title">{lang_get s='graphical_reports'}</h1>
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	

<div class="workBack">
{foreach from=$gui->charts key=title item=code}
<h3>{$title|escape}</h3>
	<img src="{$basehref}{$code}">
{/foreach}
</div>
</body>
</html>
