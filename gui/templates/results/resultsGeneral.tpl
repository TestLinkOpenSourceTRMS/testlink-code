{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsGeneral.tpl,v 1.2 2008/03/03 18:51:59 franciscom Exp $
Purpose: smarty template - show Test Results and Metrics
Revisions:
*}
{lang_get var="labels"
          s='trep_kw,trep_owner,trep_comp,
             title_res_by_kw,title_res_by_owner,title_res_by_top_level_suites'}


{assign var=this_template_dir value=$smarty.template|dirname}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_gen_test_rep'} </h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

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
           args_column_definition=$columnsDefinition->keywords
           args_column_data=$statistics->keywords}

  {lang_get s="generated_by_TestLink_on"} {$smarty.now|date_format:$gsmarty_timestamp_format}
{else}
  {$do_report.msg}
{/if}  
</div>

</body>
</html>