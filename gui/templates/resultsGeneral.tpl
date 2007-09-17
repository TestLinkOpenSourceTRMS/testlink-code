{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsGeneral.tpl,v 1.11 2007/09/17 06:28:46 franciscom Exp $
Purpose: smarty template - show Test Results and Metrics
Revisions:
*}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_gen_test_rep'} </h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

{if $do_report.status_ok}
  {include file="inc_res_by_comp.tpl"}
  {include file="inc_res_by_owner.tpl"}
  {include file="inc_res_by_keyw.tpl"}

  {lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$gsmarty_timestamp_format}
{else}
  {$do_report.msg}
{/if}  
</div>

</body>
</html>