{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsGeneral.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* I18N: 20050528 - fm *}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_gen_test_rep'} {$tpName}</h1>
<div class="workBack">

{* 20050528 - fm - refactoring *}
{include file="inc_res_by_prio.tpl"}
{include file="inc_res_by_comp.tpl"}
{include file="inc_res_by_owner.tpl"}
{include file="inc_res_by_keyw.tpl"}

</div>

</body>
</html>