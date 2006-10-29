{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsBuild.tpl,v 1.4 2006/10/29 10:40:01 kevinlevy Exp $ *}
{* Purpose: smarty template - show Test Results of one build *}
{include file="inc_head.tpl"}
{*
	20051126 - scs - added escaping of tpname
*}

<body>

<h1>{$tpName|escape} {lang_get s='title_met_of_build'} {$buildName|escape}</h1>
<div class="workBack">

{include file="inc_res_by_prio.tpl"}
{include file="inc_res_by_comp.tpl"}
{* KL - 20061029 - it's debatable if we want this type of table 
	report now that there is unlimited hiearchy
	the query results page can provide very similiar functionality
{include file="inc_res_by_ts.tpl"} 
*}

{include file="inc_res_by_keyw.tpl"}

</div>

</body>
</html>