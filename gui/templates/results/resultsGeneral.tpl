{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsGeneral.tpl,v 1.15 2009/11/04 08:09:34 franciscom Exp $
Purpose: smarty template - show Test Results and Metrics
Revisions:
*}
{lang_get var="labels"
     s='trep_kw,trep_owner,trep_comp,generated_by_TestLink_on, 
       	 th_overall_priority, th_progress, th_expected, th_overall, th_milestone,
       	 th_tc_priority_high, th_tc_priority_medium, th_tc_priority_low,
         title_res_by_kw,title_res_by_owner,title_res_by_top_level_suites,
         title_gen_test_rep,title_report_tc_priorities,title_report_milestones,
         title_metrics_x_build,title_res_by_platform,th_platform,important_notice,
         report_tcase_platorm_relationship'
}


{assign var=this_template_dir value=$smarty.template|dirname}
{include file="inc_head.tpl"}

<body>

<h1 class="title">{$labels.title_gen_test_rep}</h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$session.testprojectName arg_tplan_name=$tplan_name}	

{if $do_report.status_ok}

  {if $gui->showPlatforms}
   <hr>
   <h2> {$labels.important_notice}</h2>
   {$labels.report_tcase_platorm_relationship}
   <hr>
  {/if}  
  	{* ----- results by builds -------------------------------------- *}
	<h2>{$labels.title_metrics_x_build}</h1>

	<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
  	<tr>
  		<th style="width: 10%;">{lang_get s='th_build'}</th>
    	<th>{lang_get s='th_tc_total'}</th>
      	{foreach item=the_column from=$buildColDefinition}
        	<th>{$the_column.qty}</th>
        	<th>{$the_column.percentage}</th>
    	{/foreach}
    	<th>{lang_get s='th_perc_completed'}</th>
  	</tr>

	{foreach item=res from=$buildResults}
  	<tr>
  		<td>{$res.build_name|escape}</td>
  		{if isset($res.total_tc)}
	  		<td>{$res.total_tc}</td>
	    	{foreach key=status item=the_column from=$buildColDefinition}
	        	<td>{$res.details[$status].qty}</td>
	        	<td>{$res.details[$status].percentage}</td>
	    	{/foreach}
	  		<td>{$res.percentage_completed}</td>
	  	{else}
	  		{foreach item=the_column from=$buildColDefinition}
	  		<td>&nbsp;</td>
	  		{/foreach}
	  	{/if}
  	</tr>
	{/foreach}
	
	</table>

  	{* ----- results by test suites -------------------------------------- *}

  	{* by TestSuite *}
  	{include file="$this_template_dir/inc_results_show_table.tpl"
           args_title=$labels.title_res_by_top_level_suites
           args_first_column_header=$labels.trep_comp
           args_first_column_key='tsuite_name'
           args_show_percentage=false
           args_column_definition=$gui->columnsDefinition->testsuites
           args_column_data=$gui->statistics->testsuites}

  
  	{* by ASSIGNED Tester that is not the same that EFFECTIVE TESTER *}
  	{include file="$this_template_dir/inc_results_show_table.tpl"
           args_title=$labels.title_res_by_owner
           args_first_column_header=$labels.trep_owner
           args_first_column_key='name'
           args_show_percentage=true
           args_column_definition=$gui->columnsDefinition->assigned_testers
           args_column_data=$gui->statistics->assigned_testers}

    {if $gui->showPlatforms}
      {include file="$this_template_dir/inc_results_show_table.tpl"
             args_title=$labels.title_res_by_platform
             args_first_column_header=$labels.th_platform
             args_first_column_key='name'
             args_show_percentage=true
             args_column_definition=$gui->columnsDefinition->platform
             args_column_data=$gui->statistics->platform}
    {/if}
  
  	{* Keywords 
     Warning: args_first_column_key='keyword_name' is related to name used 
              on method that generate statistics->keywords map.
  	*}
  	{include file="$this_template_dir/inc_results_show_table.tpl"
           args_title=$labels.title_res_by_kw
           args_first_column_header=$labels.trep_kw
           args_first_column_key='name'
           args_show_percentage=true
           args_column_definition=$gui->columnsDefinition->keywords
           args_column_data=$gui->statistics->keywords}


  	{* ----- results by milestones / priorities -------------------------------------- *}

	{if $session['testprojectOptPriority']}
		<h2>{$labels.title_report_tc_priorities}</h2>
		
		<table class="simple" style="width: 50%; text-align: center; margin-left: 0px;">
		<tr>
			<th>{$labels.th_overall_priority}</th>
			<th>{$labels.th_progress}</th>
		</tr>
  		<tr>
			<td>{$labels.th_tc_priority_high}</td>
 			<td>{$gui->statistics->priority_overall.3} {$tlCfg->gui_separator_open}
  					{$gui->statistics->priority_overall.high_percentage} %{$tlCfg->gui_separator_close}</td>
  		</tr>
  		<tr>
			<td>{$labels.th_tc_priority_medium}</td>
  			<td>{$gui->statistics->priority_overall.2} {$tlCfg->gui_separator_open}
  					{$gui->statistics->priority_overall.medium_percentage} %{$tlCfg->gui_separator_close}</td>
  		</tr>
  		<tr>
			<td>{$labels.th_tc_priority_low}</td>
  			<td>{$gui->statistics->priority_overall.1} {$tlCfg->gui_separator_open}
  					{$gui->statistics->priority_overall.low_percentage} %{$tlCfg->gui_separator_close}</td>
  		</tr>
		</table>

		{if $gui->statistics->milestones != ""}

			<h2>{$labels.title_report_milestones}</h2>

			<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
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
  						{$res.target_date|escape} {$tlCfg->gui_separator_close}</td>
	  			<td class="{if $res.high_incomplete}failed{else}passed{/if}">
	  					{$res.result_high_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.3} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.high_percentage} %</td>
	  			<td class="{if $res.medium_incomplete}failed{else}passed{/if}">
	  					{$res.result_medium_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.2} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.medium_percentage} %</td>
	  			<td class="{if $res.low_incomplete}failed{else}passed{/if}">
	  					{$res.result_low_percentage} % {$tlCfg->gui_separator_open} 
	  					{$res.results.1} {$tlCfg->gui_separator_close}</td>
	  			<td>{$res.low_percentage} %</td>
				<td>{$res.percentage_completed} %</td>
  			</tr>
  			{/foreach}
		</table>

	{/if}
		
	{elseif $gui->statistics->milestones != ""}
		<h2>{$labels.title_report_milestones}</h2>

		<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr>
			<th>{lang_get s='th_milestone'}</th>
			<th>{lang_get s='th_tc_total'}</th>
			<th>{lang_get s='th_completed'}</th>
			<th>{lang_get s='th_progress'}</th>
			<th>{lang_get s='th_goal'}</th>
		</tr>

 		{foreach item=res from=$gui->statistics->milestones}
  		<tr>
  			<td>{$res.name|escape} {$tlCfg->gui_separator_open}
  					{$res.target_date|escape} {$tlCfg->gui_separator_close}</td>
  			<td>{$res.tc_total}</td>
  			<td>{$res.tc_completed}</td>
			<td class="{if $res.all_incomplete}failed{else}passed{/if}">
					{$res.percentage_completed} %</td>
			<td>{$res.B} %</td>
  		</tr>
  		{/foreach}
		</table>
	{/if}

{else}
  	{$do_report.msg}
{/if}  
</div>

<p style="margin: 10px;">{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}</p>

</body>
</html>