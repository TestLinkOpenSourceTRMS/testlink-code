{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: charts.tpl,v 1.2 2008/11/22 08:49:39 franciscom Exp $
Purpose: show graphical charts 

rev: 20081122 - franciscom - added new message when charts can not be displayed
                due to missing PHP extension

*}
{include file="inc_head.tpl"}
{lang_get var='labels' s='graphical_reports,error_gd_missing'}

<body>
<h1 class="title">{$labels.graphical_reports}</h1>

{if $gui->can_use_charts}
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	
{/if}

<div class="workBack">
{if $gui->can_use_charts}
    {foreach from=$gui->charts key=title item=code}
    <h3>{$title|escape}</h3>
    	<img src="{$basehref}{$code}">
    {/foreach}
{else}
    {$labels.error_gd_missing}
{/if}
</div>
</body>
</html>
