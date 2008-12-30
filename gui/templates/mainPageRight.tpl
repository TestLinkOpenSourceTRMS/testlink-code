{*
 Testlink Open Source Project - http://testlink.sourceforge.net/
 $Id: mainPageRight.tpl,v 1.11 2008/12/30 13:34:24 franciscom Exp $
 Purpose: smarty template - main page / site map

 rev :
       20081228 - franciscom - new feature user can choose vertical order of link groups
       20070523 - franciscom - new config constant $smarty.const.TL_ITEM_BULLET_IMG
       20070508 - franciscom - milestones re-enabled
                               improving test plan messaging
*}
{lang_get var="labels"
          s="title_test_plan,ok,testplan_role,msg_no_rights_for_tp,
             title_test_execution,href_execute_test,href_rep_and_metrics,
             href_update_tplan,href_newest_tcversions,
             href_tc_exec_assignment,href_plan_assign_urgency,
             href_upd_mod_tc,title_test_plan_mgmt,title_test_case_suite,
             href_plan_management,href_assign_user_roles,
             href_build_new,href_plan_mstones,href_plan_define_priority,
             href_metrics_dashboard,href_add_remove_test_cases"}


{assign var="menuLayout" value=$tlCfg->gui->layoutMainPageRight}
{assign var="display_right_block_1" value=false}
{assign var="display_right_block_2" value=false}
{assign var="display_right_block_3" value=false}

{if $testplan_planning == "yes" || $testplan_creating == "yes" ||
	  $tp_user_role_assignment == "yes" or $testplan_create_build == "yes"}
   {assign var="display_right_block_1" value=true}

    <script>
    {literal}
    function display_right_block_1()
    {
        var rp1 = new Ext.Panel({
                                title: {/literal}'{$labels.title_test_plan_mgmt}'{literal},
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'test_plan_mgmt_topics',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_right_block_{$menuLayout.testPlan}'{literal},
                                width:'100%'
                                });
     }
    {/literal}
    </script>

{/if}

{if $countPlans > 0 }
   {assign var="display_right_block_2" value=true}

    <script>
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

{if $countPlans > 0 && $testplan_planning == "yes"}
   {assign var="display_right_block_3" value=true}

    <script>
    {literal}
    function display_right_block_3()
    {
        var rp3 = new Ext.Panel({
                            title: {/literal}'{$labels.title_test_case_suite}'{literal},
                            collapsible:false,
                            collapsed: false,
                            draggable: true,
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
	{if $num_active_tplans > 0}
	  <div class="testproject_title">
     {lang_get s='help' var='common_prefix'}
     {lang_get s='test_plan' var="xx_alt"}
     {assign var="text_hint" value="$common_prefix: $xx_alt"}
     {include file="inc_help.tpl" helptopic="hlp_testPlan" 
              inc_help_alt="$text_hint" inc_help_title="$text_hint"  
              inc_help_style="float: right;vertical-align: top;"}


 	   <form name="testplanForm" action="lib/general/mainPage.php">
       {if $countPlans > 0}
		     {$labels.title_test_plan}
		     <select style="width:50%;z-index:1"  name="testplan" onchange="this.form.submit();">
		     	{section name=tPlan loop=$arrPlans}
		     		<option value="{$arrPlans[tPlan].id}"
		     		        {$arrPlans[tPlan].selected}
		     		        title="{$arrPlans[tPlan].name|escape}">
		     		        {$arrPlans[tPlan].name|truncate:#TESTPLAN_TRUNCATE_SIZE#|escape}
		     		</option>
		     	{/section}
		     </select>
		     
		     {if $countPlans == 1}
		     	<input type="button" onclick="this.form.submit();" value="{$labels.ok}"/>
		     {/if}
		     
		     {if $testPlanRole neq null}
		     	<br />{$labels.testplan_role} {$testPlanRole|escape}
		     {/if}
	     {else}
         {if $num_active_tplans > 0}{$labels.msg_no_rights_for_tp}{/if}
		   {/if}
	   </form>
	  </div>
  {/if}
	<br />

  <div id='menu_right_block_1'></div><br>
  <div id='menu_right_block_2'></div><br>
  <div id="menu_right_block_3"></div><br>
  
  {* ----------------------------------------------------------------------------------- *}
	{if $display_right_block_1 }
    <div id='test_plan_mgmt_topics'>
    
      {if $testplan_creating == "yes"}
	    	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
       		<a href="lib/plan/planView.php">{$labels.href_plan_management}</a>
	    {/if}
	    
	    {if $testplan_create_build == "yes" and $countPlans > 0}
	    	<br />
	    	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
           	<a href="lib/plan/buildView.php">{$labels.href_build_new}</a>
      {/if} {* testplan_create_build *}
	    
	    {if $tp_user_role_assignment == "yes" && $countPlans > 0}
	    	<br />
	    	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
       	    <a href="lib/usermanagement/usersAssign.php?feature=testplan&amp;featureID={$sessionTestPlanID}">{$labels.href_assign_user_roles}</a>
	    {/if}
      
	    {if $testplan_planning == "yes" and $countPlans > 0 }
            <br />
        	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
           	<a href="lib/plan/planMilestones.php">{$labels.href_plan_mstones}</a>
	    {/if}
	    
    </div>
  {/if}
  {* ----------------------------------------------------------------------------------- *}

	{* ------------------------------------------------------------------------------------------ *}
	{if $display_right_block_2 }
    <div id='test_execution_topics'>
		  <p>
		  {if $testplan_execute == "yes" }
		  	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	          <a href="{$launcher}?feature=executeTest">{$labels.href_execute_test}</a>
		  {/if} 
      
  	  {if $testplan_metrics == "yes"}
	          <br />
		  	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	          <a href="{$launcher}?feature=showMetrics">{$labels.href_rep_and_metrics}</a>
		  {/if} 
 	    <br />
 		  <img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	         <a href="{$metrics_dashboard_url}">{$labels.href_metrics_dashboard}</a>
	    </p>
    </div>
	{/if}
  {* ------------------------------------------------------------------------------------------ *}

  {* ------------------------------------------------------------------------------------------ *}
	{if $display_right_block_3 }
    <div id='testplan_contents_topics'>
		<p>
		
		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	    <a href="{$launcher}?feature=planAddTC">{$labels.href_add_remove_test_cases}</a>
	    <br />
		
		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   	<a href="{$launcher}?feature=planUpdateTC">{$labels.href_update_tplan}</a>
	    <br />

		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   	<a href="{$launcher}?feature=newest_tcversions">{$labels.href_newest_tcversions}</a>
	    <br />

		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   	<a href="{$launcher}?feature=tc_exec_assignment">{$labels.href_tc_exec_assignment}</a>
	    <br />

		{if $session['testprojectOptPriority']}
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   		<a href="{$launcher}?feature=test_urgency">{$labels.href_plan_assign_urgency}</a>
		    <br />
		{/if}
      
		</p>
    </div>
  {/if}
  {* ------------------------------------------------------------------------------------------ *}

</div>