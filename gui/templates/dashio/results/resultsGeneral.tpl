{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - show Test Results and Metrics

@filesource	resultsGeneral.tpl
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
         info_gen_test_rep,title_res_by_kw_on_plat,title_res_by_prio_on_plat,test_suite,title_res_by_tl_testsuite_on_plat,title_res_by_prio,title_res_by_tl_testsuite,title_res_build,title_res_by_build_on_plat,export_as_spreadsheet'}

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


    <br>
    {if $gui->showPlatforms}
      <h1 class="{#TITLE_CLASS#}">{$labels.title_res_by_platform}</h1>
      {include file="results/inc_results_show_table.tpl"
             args_title=''
             args_first_column_header=$labels.th_platform
             args_first_column_key='name'
             args_show_percentage=true
             args_column_definition=$gui->columnsDefinition->platform
             args_column_data=$gui->statistics->platform}
      
      {if $gui->columnsDefinition->platform != ""}
        <p class="italic">{$labels.info_res_by_platform}</p>
        <br />
      {/if}
    {/if}


  {* ----- results by builds -------------------------------------- *}
	<h1 class="{#TITLE_CLASS#}">{$labels.title_metrics_x_build}</h1>
	{if $gui->displayBuildMetrics}
    {include file="results/inc_results_show_table.tpl"
       args_title=''
       args_column_for_total='total_assigned'
       args_first_column_header=$labels.th_build
       args_first_column_key='build_name'
       args_show_percentage=true
       args_column_definition=
         $gui->columnsDefinition->overallBuildStatus
       args_column_data=$gui->statistics->overallBuildStatus}

    {* Display message explaining that only Active Builds 
       with test cases assigned to tester will be displayed *}
    {if $gui->buildMetricsFeedback != ''}
      <p class="italic">{$gui->buildMetricsFeedback|escape}</p>
    {/if}

    <br />
	{/if}
	

  {* NEW *}
  {if $gui->displayBuildByPlatMetrics && $gui->showPlatforms}
    <h1 class="{#TITLE_CLASS#}">{$labels.title_res_build}</h1>
    {foreach from=$platforms key=platId item=pname}
      {if isset($gui->statistics->buildByPlatMetrics[$platId]) }
        {$tit = $labels.title_res_by_build_on_plat}
        {$tit = "$tit $pname"}         
        {include file="results/inc_results_show_table.tpl"
           args_title=$tit
           args_column_for_total='total_assigned'
           args_first_column_header=$labels.th_build
           args_first_column_key='build_name'
           args_show_percentage=true
           args_column_definition=
             $gui->columnsDefinition->buildByPlatMetrics
           args_column_data=$gui->statistics->buildByPlatMetrics[$platId] 
        }
      {/if}    
    {/foreach}

    {* Display message explaining that only Active Builds 
       with test cases assigned to tester will be displayed *}
    {if $gui->buildMetricsFeedback != ''}
      <p class="italic">{$gui->buildMetricsFeedback|escape}</p>
    {/if}
    <br />
  {/if}


  	{* ----- results by test suites ------------------- *}
  	{* by TestSuite *}
    <h1 class="{#TITLE_CLASS#}">{$labels.title_res_by_tl_testsuite}</h1>
    {foreach from=$platforms key=platId item=pname}
      {if isset($gui->statistics->testsuites[$platId]) }
        {$tit = ''}
        {if $pname != ''}
          {$tit = $labels.title_res_by_tl_testsuite_on_plat}
          {$tit = "$tit $pname"}
        {/if}         
        {include file="results/inc_results_show_table.tpl"
           args_title=$tit
           args_first_column_header=$labels.test_suite
           args_first_column_key='name'
           args_show_percentage=true
           args_column_definition=$gui->columnsDefinition->testsuites
           args_column_data=$gui->statistics->testsuites[$platId] 
        }
      {/if} 
    {/foreach}

    {if $gui->columnsDefinition->testsuites != ""}
  	  <p class="italic">{$labels.info_res_by_top_level_suites}</p>
  	  <br />
  	{/if}

  
  	{* by ASSIGNED Tester that is not the same that EFFECTIVE TESTER 
  	{include file="results/inc_results_show_table.tpl"
           args_title=$labels.title_res_by_owner
           args_first_column_header=$labels.trep_owner
           args_first_column_key='name'
           args_show_percentage=true
           args_column_definition=$gui->columnsDefinition->assigned_testers
           args_column_data=$gui->statistics->assigned_testers} *}


    {if $gui->tprojOpt->testPriorityEnabled}
      <h1 class="{#TITLE_CLASS#}">{$labels.title_res_by_prio}</h1>
      {foreach from=$platforms key=platId item=pname}
        {if isset($gui->statistics->priorities[$platId]) }
          
          {$tit = ""}
          {if $pname != ''}
            {$tit = $labels.title_res_by_prio_on_plat}
            {$tit = "$tit $pname"}
          {/if}  

          {include file="results/inc_results_show_table.tpl"
             args_title=$tit
             args_first_column_header=$labels.priority
             args_first_column_key='name'
             args_show_percentage=true
             args_column_definition=$gui->columnsDefinition->priorities
             args_column_data=$gui->statistics->priorities[$platId] 
          }
        {/if} 
      {/foreach}
      
      {if $gui->columnsDefinition->priorities != ""}
        <p class="italic">{$labels.info_report_tc_priorities}</p>
        <br />
      {/if}
    {/if}
  
  	{* Keywords 
     Warning: 
     args_first_column_key='keyword_name' is related to name used 
     on method that generate statistics->keywords map.
  	*}

    {$writeTitle=true}
    {$dataExists=false}
    {foreach from=$platforms key=platId item=pname}
      {if isset($gui->statistics->keywords[$platId]) }
        {if $writeTitle}
          <h1 class="{#TITLE_CLASS#}">{$labels.title_res_by_kw}</h1>
          {$writeTitle=fals}
        {/if}  
        
        {$dataExists=true}
        {$tit = ""}
        {if $pname != ''}
          {$tit = $labels.title_res_by_kw_on_plat}
          {$tit = "$tit $pname"}
        {/if}

        {include file="results/inc_results_show_table.tpl"
               args_title=$tit
               args_first_column_header=$labels.trep_kw
               args_first_column_key='name'
               args_show_percentage=true
               args_column_definition=$gui->columnsDefinition->keywords
               args_column_data=$gui->statistics->keywords[$platId]}
      {/if}        
    {/foreach}

    {if $dataExists && $gui->columnsDefinition->keywords != ""}
      <p class="italic">{$labels.info_res_by_kw}</p>
      <br />
    {/if}


  	{* ----- results by milestones / priorities ------------ *}

	{if $gui->tprojOpt->testPriorityEnabled}
		{if $gui->statistics->milestones != ""}

			<h2>{$labels.title_report_milestones}</h2>

			<table class="simple_tableruler sortable" style="text-align: center; margin-left: 0px;">
			<tr>
				<th>{$labels.th_milestone}</th>
				<th>{$labels.th_tc_priority_high}</th>
				<th>{$labels.th_expected}</th>
				<th>{$labels.th_tc_priority_medium}</th>
				<th>{$labels.th_expected}</th>
				<th>{$labels.th_tc_priority_low}</th>
				<th>{$labels.th_expected}</th>
				<th>{$labels.th_overall}</th>
			</tr>
 			{foreach item=res from=$gui->statistics->milestones}
  			<tr>
  				<td>{$res.name|escape} {$tlCfg->gui_separator_open}
  						{if $res.start_date|escape != "0000-00-00"}
						{$labels.from} {$res.start_date|escape}
						{/if}
  						{$labels.until} {$res.target_date|escape} {$tlCfg->gui_separator_close}</td>
	  			<td class="{if $res.high_incomplete}failed{else}passed{/if}">
	  					{$res.result_high_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.3}/{$res.tcs_priority.3} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.high_percentage} %</td>
	  			<td class="{if $res.medium_incomplete}failed{else}passed{/if}">
	  					{$res.result_medium_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.2}/{$res.tcs_priority.2} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.medium_percentage} %</td>
	  			<td class="{if $res.low_incomplete}failed{else}passed{/if}">
	  					{$res.result_low_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.1}/{$res.tcs_priority.1} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.low_percentage} %</td>
				<td>{$res.percentage_completed} %</td>
  			</tr>
  			{/foreach}
		</table>
      <p class="italic">{$labels.info_report_milestones_prio}</p>
      <br />

	{/if}
		
	{elseif $gui->statistics->milestones != ""}
		<h2>{$labels.title_report_milestones}</h2>

		<table class="simple_tableruler sortable" style="text-align: center; margin-left: 0px;">
		<tr>
			<th>{$labels.th_milestone}</th>
			<th>{$labels.th_tc_total}</th>
			<th>{$labels.th_completed}</th>
			<th>{$labels.th_progress}</th>
			<th>{$labels.th_goal}</th>
		</tr>

 		{foreach item=res from=$gui->statistics->milestones}
  		<tr>
  			<td>{$res.name|escape} {$tlCfg->gui_separator_open}
  					{if $res.start_date|escape != "0000-00-00"}
					{$labels.from} {$res.start_date|escape}
					{/if}
  					{$labels.until} {$res.target_date|escape} {$tlCfg->gui_separator_close}</td>
  			<td>{$res.tc_total}</td>
  			<td>{$res.tc_completed}</td>
			<td class="{if $res.medium_incomplete}failed{else}passed{/if}">
					{$res.percentage_completed} % {$tlCfg->gui_separator_open} 
					{$res.results.2}/{$res.tcs_priority.2} {$tlCfg->gui_separator_close}</td>
			<td>{$res.medium_percentage} %</td>
  		</tr>
  		{/foreach}
		</table>
      <p class="italic">{$labels.info_report_milestones_no_prio}</p>
      <br />
	{/if}
	
	<p class="italic">{$labels.info_gen_test_rep}</p>
	<p>{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}</p>
	<p>{$labels.elapsed_seconds} {$gui->elapsed_time}</p>

{else}
  	{$gui->do_report.msg}
{/if}  
</div>

</body>
</html>
