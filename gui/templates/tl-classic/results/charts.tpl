{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: charts.tpl,v 1.3 2009/04/16 11:11:59 havlat Exp $
Purpose: show graphical charts 

rev: 20081122 - franciscom - added new message when charts can not be displayed
                due to missing PHP extension

*}
{include file="inc_head.tpl"}

<body>
<h1 class="title">{lang_get s='graphical_reports'}</h1>

{if $gui->can_use_charts == 'OK'}
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	
{/if}

<div class="workBack">
{if $gui->can_use_charts == 'OK'}
    {foreach from=$gui->charts key=title item=code}
    <h3>{$title|escape}</h3>
    	<img src="{$basehref}{$code}">
    {/foreach}
{else}
    {$gui->can_use_charts}
{/if}
</div>
</body>
</html>
