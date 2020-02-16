{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - show Test Results and Metrics

@filesource	resultsByTSuite.tpl
*}
{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels"
     s='trep_kw,trep_owner,trep_comp,generated_by_TestLink_on, priority,
       	 th_overall_priority, th_progress, th_expected, th_overall, th_milestone,
       	 th_tc_priority_high, th_tc_priority_medium, th_tc_priority_low,
         title_res_by_kw,title_res_by_owner,title_res_by_top_level_suites,
         title_report_tc_priorities,title_report_milestones,elapsed_seconds,
         title_metrics_x_build,title_res_by_platform,th_platform,important_notice,
         report_tcase_platorm_relationship, th_tc_total, th_completed, th_goal,
         th_build, th_tc_assigned, th_perc_completed, from, until,
         info_res_by_top_level_suites, info_report_tc_priorities, info_res_by_platform,send_by_email_to_me,
         info_report_milestones_prio, info_report_milestones_no_prio, info_res_by_kw,send_test_report,
         info_gen_test_rep,title_res_by_kw_on_plat,title_res_by_prio_on_plat,test_suite,title_res_by_tl_testsuite_on_plat,title_res_by_prio,title_res_by_tl_testsuite,title_res_build,title_res_by_build_on_plat,
         export_as_spreadsheet,title_res_by_l1l2_testsuite,
         metrics_by_l1l2_testsuite,firstExec,latestExec,
         section_link_report_by_tsuite_on_plat,l1l2'}

{include file="inc_head.tpl"}

{if $gui->showPlatforms}
  {$platforms = $gui->platformSet}
{else}
  {$platforms = $gui->fakePlatform}    
{/if}

<body>
<h1 class="{#TITLE_CLASS#}">{$gui->title}</h1>

<div style="display: flex;">
<form name="send_by_email_to_me" 
      id="send_by_email_to_me"
      action="{$gui->actionSendMail}" method="POST">
  &nbsp;&nbsp;
  <input hidden name="sendByEmail" value="1">
  
  <input type="image" name="reportByMail" id="reportByMail" 
         src="{$tlImages.email}" title="{$labels.send_by_email_to_me}"
         onclick="submit();">
</form>

<form name="exportSpreadsheet" id="exportSpreadsheet" method="POST"
      action={$gui->actionSpreadsheet}>
  &nbsp;&nbsp;
  <input type="image" name="exportSpreadSheet" id="exportSpreadSheet" 
         src="{$tlImages.export_excel}" title="{$labels.export_as_spreadsheet}">
</form>
</div>

{if null != $gui->mailFeedBack && $gui->mailFeedBack->msg != ""}
  <p class='info'>{$gui->mailFeedBack->msg}</p>
{/if}


<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	


{if $gui->do_report.status_ok}

  {if $gui->showPlatforms}
   <hr>
   <h2> {$labels.important_notice}</h2>
   {$labels.report_tcase_platorm_relationship}
   <hr>
  {/if}  


  	{* ----- results by test suites ------------------- *}
  	{* by TestSuite *}
    <h1 class="{#TITLE_CLASS#}">{$labels.metrics_by_l1l2_testsuite}</h1>
    {foreach from=$platforms key=platId item=pname}
      {if isset($gui->statistics->testsuites[$platId]) }
        {$tit = ''}
        {if $pname != ''}
          {$tit = $labels.section_link_report_by_tsuite_on_plat}
          {$tit = "$tit $pname"}
        {/if}         
        {include file="results/show_table_with_exec_span.inc.tpl"
           args_title=$tit
           args_first_column_header=$labels.l1l2
           args_first_column_key='name'
           args_show_percentage=true
           args_column_definition=$gui->columnsDefinition->testsuites
           args_column_data=$gui->dataByPlatform->testsuites[$platId] 
        }
      {/if} 
      <hr>
      <p>
    {/foreach}

	<p class="italic">{$labels.info_gen_test_rep}</p>
	<p>{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}</p>
	<p>{$labels.elapsed_seconds} {$gui->elapsed_time}</p>

{else}
  	{$gui->do_report.msg}
{/if}  
</div>

</body>
</html>
