{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$@filesource inc_steps.tpl
             Shows the steps for a testcase in vertical or horizontal layout
@used-by tcView_viewer.tpl,inc_exec_test_spec.tpl

@param $layout "horizontal" or "vertical"
@param $steps Array of the steps
@param $edit_enabled Steps links to edit page if true


*}
{lang_get var="inc_steps_labels" 
          s="show_hide_reorder, step_number,clear_all_status, 
             step_actions,expected_results,
             latest_exec_notes,step_exec_status,
             clear_all_notes,step_exec_notes,
             execution_type_short_descr,delete_step,
             insert_step,show_ghost_string"}

{lang_get s='warning_delete_step' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{if $layout == 'horizontal'}
    {include file="testcases/steps_horizontal.inc.tpl"}
{else}
    {include file="testcases/steps_vertical.inc.tpl"}
{/if}