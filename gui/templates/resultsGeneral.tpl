{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsGeneral.tpl,v 1.3 2005/10/05 06:14:26 franciscom Exp $
Purpose: smarty template - show Test Results and Metrics

20051004 - fm - added print button
20050528 - fm - I18N

*}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_gen_test_rep'} {$tpName}</h1>

{include file="inc_print_button.tpl"}
<div class="workBack">

{* 20050528 - fm - refactoring *}
{include file="inc_res_by_prio.tpl"}
{include file="inc_res_by_comp.tpl"}
{include file="inc_res_by_owner.tpl"}
{include file="inc_res_by_keyw.tpl"}

</div>

{include file="inc_print_button.tpl"}

</body>
</html>