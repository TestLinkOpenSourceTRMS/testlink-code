{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsGeneral.tpl,v 1.6 2008/06/26 21:46:48 havlat Exp $
Purpose: smarty template - show Test Results and Metrics
Revisions:
*}
{lang_get var="labels"
          s='trep_kw,trep_owner,trep_comp,generated_by_TestLink_on,
             title_res_by_kw,title_res_by_owner,title_res_by_top_level_suites'}


{assign var=this_template_dir value=$smarty.template|dirname}
{include file="inc_head.tpl"}

<body>

<h1 class="title">{lang_get s='title_gen_test_rep'}</h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$session.testprojectName arg_tplan_name=$tplan_name}	

{if $do_report.status_ok}
  
  	{* by TestSuite *}
  	{include file="$this_template_dir/inc_results_show_table.tpl"
           args_title=$labels.title_res_by_top_level_suites
           args_first_column_header=$labels.trep_comp
           args_first_column_key='tsuite_name'
           args_show_percentage=false
           args_column_definition=$columnsDefinition->testsuites
           args_column_data=$statistics->testsuites}

  
  	{* by Tester *}
  	{include file="$this_template_dir/inc_results_show_table.tpl"
           args_title=$labels.title_res_by_owner
           args_first_column_header=$labels.trep_owner
           args_first_column_key='tester_name'
           args_show_percentage=true
           args_column_definition=$columnsDefinition->testers
           args_column_data=$statistics->testers}
  
  	{* Keywords 
     Warning: args_first_column_key='keyword_name' is related to name used 
              on method that generate statistics->keywords map.
  	*}
  	{include file="$this_template_dir/inc_results_show_table.tpl"
           args_title=$labels.title_res_by_kw
           args_first_column_header=$labels.trep_kw
           args_first_column_key='keyword_name'
           args_show_percentage=true
           args_column_definition=$columnsDefinition->keywords
           args_column_data=$statistics->keywords}

  	{* ----- results by milestones / priorities ----- *}
	{if $session['testprojectOptPriority']}
		<h2>{lang_get s='title_report_tc_priorities'}</h2>
	{elseif $statistics->milestones ne ""}
		<h2>{lang_get s='title_report_milestones'}</h2>
	{/if}
	
	{if $session['testprojectOptPriority'] && $statistics->milestones ne ""}
			<table class="simple" style="text-align: center;">
		<tr>
		<th>{lang_get s='th_milestone'}</th>
		<th>{lang_get s='th_tc_priority'}</th>
		<th>{lang_get s='trep_comp_perc'}</th>
		</tr>
{*	
 		{foreach item=res from=$args_column_data}
  			<tr>
  			<td>{$res.$args_first_column_key|escape}</td>
  			<td>{$res.total_tc}</td>
      		{foreach item=the_column from=$res.details}
          	<td>{$the_column.qty}</td>
        	{if $args_show_percentage}
          	<td>{$the_column.percentage}</td>
        	{/if}
      		{/foreach}
  		<td>{$res.percentage_completed}</td>
  		</tr>
  		{/foreach}
*}		</table>

	{elseif $session['testprojectOptPriority']}
		<p>TODO: priorities without milestones</p>

	{elseif $statistics->milestones ne ""}
		<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
		<tr>
			<th>{lang_get s='th_milestone'}</th>
			<th>{lang_get s='th_tc_total'}</th>
			<th>{lang_get s='th_completed'}</th>
			<th>{lang_get s='th_not_run'}</th>
			<th>{lang_get s='th_progress'}</th>
			<th>{lang_get s='th_goal'}</th>
		</tr>

 		{foreach item=res from=$statistics->milestones}
  		<tr>
  			<td>{$res.name|escape} {$tlCfg->gui_separator_open}{$res.target_date|escape}
  					{$tlCfg->gui_separator_close}</td>
  			<td>{$res.tc_total}</td>
  			<td>{$res.tc_completed}</td>
  			<td>{$res.tc_not_run}</td>
			<td {if $res.B_incomplete}class="failed"{/if}>{$res.percentage_completed}</td>
			<td>{$res.B}</td>
  		</tr>
  		{/foreach}
		</table>
	{/if}

  	{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}

{else}
  	{$do_report.msg}
{/if}  
</div>

</body>
</html>