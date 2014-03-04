{*
 Testlink Open Source Project - http://testlink.sourceforge.net/
 @filesource mainPageRight.tpl
 main page right side
 @internal revisions
 
*}
{lang_get var="labels"
          s="current_test_plan,ok,testplan_role,msg_no_rights_for_tp,
             title_test_execution,href_execute_test,href_rep_and_metrics,
             href_update_tplan,href_newest_tcversions,
             href_my_testcase_assignments,href_platform_assign,
             href_tc_exec_assignment,href_plan_assign_urgency,
             href_upd_mod_tc,title_test_plan_mgmt,title_test_case_suite,
             href_plan_management,href_assign_user_roles,
             href_build_new,href_plan_mstones,href_plan_define_priority,
             href_metrics_dashboard,href_add_remove_test_cases"}


{$menuLayout=$tlCfg->gui->layoutMainPageRight}
{$display_right_block_1=false}
{$display_right_block_2=false}
{$display_right_block_3=false}

{if $gui->grants.testplan_planning == "yes" || $gui->grants.mgt_testplan_create == "yes" ||
	  $gui->grants.testplan_user_role_assignment == "yes" or $gui->grants.testplan_create_build == "yes"}
   {$display_right_block_1=true}

    <script  type="text/javascript">
    function display_right_block_1()
    {
        var rp1 = new Ext.Panel({
                                title:'{$labels.title_test_plan_mgmt}',
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'test_plan_mgmt_topics',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: 'menu_right_block_{$menuLayout.testPlan}',
                                width:'100%'
                                });
     }
    </script>
{/if}

{if $gui->countPlans > 0 && ($gui->grants.testplan_execute == "yes" || $gui->grants.testplan_metrics == "yes")}
   {$display_right_block_2=true}

    <script  type="text/javascript">
    {literal}
    function display_right_block_2()
    {
        var rp2 = new Ext.Panel({
                                 title: {/literal}'{$labels.title_test_execution}'{literal},
                                 collapsible:false,
                                 collapsed: false,
                                 draggable: false,
                                 contentEl: 'test_execution_topics',
                                 baseCls: 'x-tl-panel',
                                 bodyStyle: "background:#c8dce8;padding:3px;",
                                 renderTo: {/literal}'menu_right_block_{$menuLayout.testExecution}'{literal},
                                 width:'100%'
                                });
     }
    {/literal}
    </script>
{/if}

{if $gui->countPlans > 0 && $gui->grants.testplan_planning == "yes"}
   {$display_right_block_3=true}

    <script  type="text/javascript">
    {literal}
    function display_right_block_3()
    {
        var rp3 = new Ext.Panel({
                            title: {/literal}'{$labels.title_test_case_suite}'{literal},
                            collapsible:false,
                            collapsed: false,
                            draggable: false,
                            contentEl: 'testplan_contents_topics',
                            baseCls: 'x-tl-panel',
                            bodyStyle: "background:#c8dce8;padding:3px;",
                            renderTo: {/literal}'menu_right_block_{$menuLayout.testPlanContents}'{literal},
                            width:'100%'
                                });
     }
    {/literal}
    </script>

{/if}

{* ----- Right Column begin ---------------------------------------------------------- *}
<div class="vertical_menu" style="float: right; margin:10px 10px 10px 10px">
{* ----------------------------------------------------------------------------------- *}
	{if $gui->num_active_tplans > 0}
	  <div class="">
     {lang_get s='help' var='common_prefix'}
     {lang_get s='test_plan' var="xx_alt"}
     {$text_hint="$common_prefix: $xx_alt"}
     {include file="inc_help.tpl" helptopic="hlp_testPlan" show_help_icon=true 
              inc_help_alt="$text_hint" inc_help_title="$text_hint"  
              inc_help_style="float: right;vertical-align: top;"}

{* |truncate:#TESTPLAN_TRUNCATE_SIZE# *}
 	   <form name="testplanForm" action="lib/general/mainPage.php">
       {if $gui->countPlans > 0}
		     {$labels.current_test_plan}:<br/>
		     <select class="chosen-select" name="testplan" onchange="this.form.submit();">
		     	{section name=tPlan loop=$gui->arrPlans}
		     		<option value="{$gui->arrPlans[tPlan].id}"
		     		        {if $gui->arrPlans[tPlan].selected} selected="selected" {/if}
		     		        title="{$gui->arrPlans[tPlan].name|escape}">
		     		        {$gui->arrPlans[tPlan].name|escape}
		     		</option>
		     	{/section}
		     </select>
		     
		     {if $gui->countPlans == 1}
		     	<input type="button" onclick="this.form.submit();" value="{$labels.ok}"/>
		     {/if}
		     
		     {if $gui->testplanRole neq null}
		     	<br />{$labels.testplan_role} {$gui->testplanRole|escape}
		     {/if}
	     {else}
         {if $gui->num_active_tplans > 0}{$labels.msg_no_rights_for_tp}{/if}
		   {/if}
	   </form>
	  </div>
  {/if}
	<br />

  <div id='menu_right_block_1'></div><br />
  <div id='menu_right_block_2'></div><br />
  <div id="menu_right_block_3"></div><br />
  
  {* ----------------------------------------------------------------------------------- *}
	{if $display_right_block_1}
    <div id='test_plan_mgmt_topics'>
    
      {if $gui->grants.mgt_testplan_create == "yes"}
	    	<img src="{$tlImages.bullet}" />
       		<a href="lib/plan/planView.php">{$labels.href_plan_management}</a>
	    {/if}
	    
	    {if $gui->grants.testplan_create_build == "yes" and $gui->countPlans > 0}
	    	<br />
	    	<img src="{$tlImages.bullet}" />
           	<a href="lib/plan/buildView.php">{$labels.href_build_new}</a>
      {/if} {* testplan_create_build *}
	    
	    {if $gui->grants.testplan_user_role_assignment == "yes" && $gui->countPlans > 0}
	    	<br />
	    	<img src="{$tlImages.bullet}" />
       	    <a href="lib/usermanagement/usersAssign.php?featureType=testplan&amp;featureID={$gui->testplanID}">{$labels.href_assign_user_roles}</a>
	    {/if}
      
	    {if $gui->grants.testplan_milestone_overview == "yes" and $gui->countPlans > 0}
            <br />
        	<img src="{$tlImages.bullet}" />
           	<a href="lib/plan/planMilestonesView.php">{$labels.href_plan_mstones}</a>
	    {/if}
	    
    </div>
  {/if}
  {* ----------------------------------------------------------------------------------- *}

	{* ------------------------------------------------------------------------------------------ *}
	{if $display_right_block_2}
    <div id='test_execution_topics'>
		{if $gui->grants.testplan_execute == "yes"}
			<img src="{$tlImages.bullet}" />
			<a href="{$gui->launcher}?feature=executeTest">{$labels.href_execute_test}</a>
      <br /> 
		
      {if $gui->grants.exec_testcases_assigned_to_me == "yes"}
			 <img src="{$tlImages.bullet}" />
			 <a href="{$gui->url.testcase_assignments}">{$labels.href_my_testcase_assignments}</a>
			 <br />
      {/if} 
		{/if} 
      
		{if $gui->grants.testplan_metrics == "yes"}
			<img src="{$tlImages.bullet}" />
			<a href="{$gui->launcher}?feature=showMetrics">{$labels.href_rep_and_metrics}</a>
			<br />
      {* do not understand this check => will remove *}        
      {* {if $gui->grants.exec_testcases_assigned_to_me == "yes"} *}
  			<img src="{$tlImages.bullet}" />
  			<a href="{$gui->url.metrics_dashboard}">{$labels.href_metrics_dashboard}</a>
      {* {/if} *}
		{/if} 
    </div>
	{/if}
  {* ------------------------------------------------------------------------------------------ *}

  {* ------------------------------------------------------------------------------------------ *}
	{if $display_right_block_3}
    <div id='testplan_contents_topics'>
    {if $gui->grants.testplan_add_remove_platforms == "yes"}
  		<img src="{$tlImages.bullet}" />
  	  <a href="lib/platforms/platformsAssign.php?tplan_id={$gui->testplanID}">{$labels.href_platform_assign}</a>
  		<br />
    {/if} 
		
		<img src="{$tlImages.bullet}" />
	  <a href="{$gui->launcher}?feature=planAddTC">{$labels.href_add_remove_test_cases}</a>
	  <br />
		
    {if $gui->grants.testplan_update_linked_testcase_versions == "yes"}
		  <img src="{$tlImages.bullet}" />
	   	<a href="{$gui->launcher}?feature=planUpdateTC">{$labels.href_update_tplan}</a>
	    <br />
    {/if} 

    {if $gui->grants.testplan_show_testcases_newest_versions == "yes"}
		  <img src="{$tlImages.bullet}" />
	   	<a href="{$gui->launcher}?feature=newest_tcversions">{$labels.href_newest_tcversions}</a>
	    <br />
    {/if} 

		<img src="{$tlImages.bullet}" />
	  <a href="{$gui->launcher}?feature=tc_exec_assignment">{$labels.href_tc_exec_assignment}</a>
	  <br />

		{if $session['testprojectOptions']->testPriorityEnabled && 
        $gui->grants.testplan_set_urgent_testcases == "yes"}
			<img src="{$tlImages.bullet}" />
	   	<a href="{$gui->launcher}?feature=test_urgency">{$labels.href_plan_assign_urgency}</a>
		  <br />
		{/if}
    </div>
  {/if}
  {* ------------------------------------------------------------------------------------------ *}

</div>
<script>
jQuery( document ).ready(function() {
jQuery(".chosen-select").chosen({ width: "85%" });
});
</script>