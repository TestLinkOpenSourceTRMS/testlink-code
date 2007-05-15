{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsGeneral.tpl,v 1.8 2007/05/15 13:56:38 franciscom Exp $
Purpose: smarty template - show Test Results and Metrics
Revisions:
20051004 - fm - added print button
20050528 - fm - I18N; refactoring
20051121 - scs - added escaping of tpname
20051204 - mht - removed obsolete print button
*}

{include file="inc_head.tpl"}

<body>

<h1>{$tpName|escape} {lang_get s='title_gen_test_rep'} </h1>

<div class="workBack">
{include file="inc_res_by_comp.tpl"}
{include file="inc_res_by_owner.tpl"}
{include file="inc_res_by_keyw.tpl"}
</div>

</body>
</html>