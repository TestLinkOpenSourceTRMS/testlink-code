{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsBuild.tpl,v 1.8 2007/05/15 13:56:38 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results of one build *}
{include file="inc_head.tpl"}
<body>

<h1>{$tpName|escape} {lang_get s='title_met_of_build'} {$buildName|escape}</h1>
<div class="workBack">
{include file="inc_res_by_comp.tpl"}
{include file="inc_res_by_ts.tpl"} 
{include file="inc_res_by_keyw.tpl"}

</div>

</body>
</html>
