{*
 Testlink Open Source Project - http://testlink.sourceforge.net/
 @filesource mainPageRight.tpl
 main page right side
 @internal revisions
 
*}
{lang_get var="labels"
          s="current_test_plan,ok,testplan_role,msg_no_rights_for_tp,
             title_test_execution,href_execute_test,href_rep_and_metrics,
             href_update_tplan,href_newest_tcversions,title_plugins,
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
{$display_left_block_top = false}
{$display_left_block_bottom = false}

{if $gui->grants.testplan_planning == "yes" || $gui->grants.mgt_testplan_create == "yes" ||
	  $gui->grants.testplan_user_role_assignment == "yes" or $gui->grants.testplan_create_build == "yes"}
   {$display_right_block_1=true}

    <script  type="text/javascript">
    function display_right_block_1()
    {
      var rp1 = new Ext.Panel({ title:'{$labels.title_test_plan_mgmt}',
                                collapsible:false, collapsed: false, draggable: false,
                                contentEl: 'test_plan_mgmt_topics', baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;", width:'100%',
                                renderTo: 'menu_right_block_{$menuLayout.testPlan}'
                                });
    }
    </script>
{/if}

{if $gui->countPlans > 0 && ($gui->grants.testplan_execute == "yes" || $gui->grants.testplan_metrics == "yes")}
   {$display_right_block_2=true}

    <script  type="text/javascript">
    function display_right_block_2()
    {
      var rp2 = new Ext.Panel({ title: '{$labels.title_test_execution}',
                                collapsible: false, collapsed: false, draggable: false,
                                contentEl: 'test_execution_topics', baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;", width: '100%',
                                renderTo: 'menu_right_block_{$menuLayout.testExecution}'                       
                              });
     }
    </script>
{/if}

{if $gui->countPlans > 0 && $gui->grants.testplan_planning == "yes"}
   {$display_right_block_3=true}

    <script  type="text/javascript">
    function display_right_block_3()
    {
      var rp3 = new Ext.Panel({ title: '{$labels.title_test_case_suite}',
                                collapsible:false, collapsed: false, draggable: false,
                                contentEl: 'testplan_contents_topics', baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;", width: '100%',
                                renderTo: 'menu_right_block_{$menuLayout.testPlanContents}'
                              });
     }
    </script>

{/if}

{$display_right_block_top=false}
{$display_right_block_bottom=false}

{if isset($gui->plugins.EVENT_RIGHTMENU_TOP) &&  $gui->plugins.EVENT_RIGHTMENU_TOP}
  {$display_right_block_top=true}
{/if}
{if isset($gui->plugins.EVENT_RIGHTMENU_BOTTOM) &&  $gui->plugins.EVENT_RIGHTMENU_BOTTOM}
  {$display_right_block_bottom=true}
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

  <div id='menu_right_block_top'></div><br />
  <div id='menu_right_block_1'></div><br />
  <div id='menu_right_block_2'></div><br />
  <div id="menu_right_block_3"></div><br />
  <div id='menu_right_block_bottom'></div><br />

  {* ----------------------------------------------------------------------------------- *}
	{if $display_right_block_1}
    <div id='test_plan_mgmt_topics'>
    
      {if $gui->grants.mgt_testplan_create == "yes"}
       		<a href="lib/plan/planView.php">{$labels.href_plan_management}</a>
	    {/if}
	    
	    {if $gui->grants.testplan_create_build == "yes" and $gui->countPlans > 0}
	    	<br />
       	<a href="lib/plan/buildView.php?tplan_id={$gui->testplanID}">{$labels.href_build_new}</a>
      {/if}
	    
      {if $gui->grants.testplan_milestone_overview == "yes" and $gui->countPlans > 0}
         <br />
         <a href="lib/plan/planMilestonesView.php">{$labels.href_plan_mstones}</a>
      {/if}
    </div>
  {/if}
  {* ----------------------------------------------------------------------------------- *}

	{* ------------------------------------------------------------------------------------------ *}
	{if $display_right_block_2}
    <div id='test_execution_topics'>
		{if $gui->grants.testplan_execute == "yes"}
			<a href="{$gui->launcher}?feature=executeTest">{$labels.href_execute_test}</a>
      <br /> 
		
      {if $gui->grants.exec_testcases_assigned_to_me == "yes"}
			 <a href="{$gui->url.testcase_assignments}">{$labels.href_my_testcase_assignments}</a>
			 <br />
      {/if} 
		{/if} 
      
		{if $gui->grants.testplan_metrics == "yes"}
			<a href="{$gui->launcher}?feature=showMetrics">{$labels.href_rep_and_metrics}</a>
			<br />
  			<a href="{$gui->url.metrics_dashboard}">{$labels.href_metrics_dashboard}</a>
		{/if} 
    </div>
	{/if}
  {* ------------------------------------------------------------------------------------------ *}

  {* ------------------------------------------------------------------------------------------ *}
	{if $display_right_block_3}
    <div id='testplan_contents_topics'>
    {if $gui->grants.testplan_add_remove_platforms == "yes"}
  	  <a href="lib/platforms/platformsAssign.php?tplan_id={$gui->testplanID}">{$labels.href_platform_assign}</a>
  		<br />
    {/if} 
		
	  <a href="{$gui->launcher}?feature=planAddTC">{$labels.href_add_remove_test_cases}</a>
	  <br />

    <a href="{$gui->launcher}?feature=tc_exec_assignment">{$labels.href_tc_exec_assignment}</a>
    <br />
		
    {if $session['testprojectOptions']->testPriorityEnabled && 
        $gui->grants.testplan_set_urgent_testcases == "yes"}
      <a href="{$gui->launcher}?feature=test_urgency">{$labels.href_plan_assign_urgency}</a>
      <br />
    {/if}

    {if $gui->grants.testplan_update_linked_testcase_versions == "yes"}
	   	<a href="{$gui->launcher}?feature=planUpdateTC">{$labels.href_update_tplan}</a>
	    <br />
    {/if} 

    {if $gui->grants.testplan_show_testcases_newest_versions == "yes"}
	   	<a href="{$gui->launcher}?feature=newest_tcversions">{$labels.href_newest_tcversions}</a>
	    <br />
    {/if} 

    </div>
  {/if}

  {if $display_right_block_top}
    <script type="text/javascript">
    function display_right_block_top() 
    {
      var pt = new Ext.Panel({
                              title: '{$labels.title_plugins}',
                              collapsible: false,
                              collapsed: false,
                              draggable: false,
                              contentEl: 'plugin_right_top',
                              baseCls: 'x-tl-panel',
                              bodyStyle: "background:#c8dce8;padding:3px;",
                              renderTo: 'menu_right_block_top',
                              width: '100%'
                             });
    }
    </script>
    {if isset($gui->plugins.EVENT_RIGHTMENU_TOP)}
      <div id="plugin_right_top">
        {foreach from=$gui->plugins.EVENT_RIGHTMENU_TOP item=menu_item}
          {$menu_item}
          <br/>
        {/foreach}
      </div>
    {/if}
  {/if}

  {if $display_right_block_bottom}
    <script type="text/javascript">
    function display_right_block_bottom() 
    {
      var pb = new Ext.Panel({
                              title: '{$labels.title_plugins}',
                              collapsible: false,
                              collapsed: false,
                              draggable: false,
                              contentEl: 'plugin_right_bottom',
                              baseCls: 'x-tl-panel',
                              bodyStyle: "background:#c8dce8;padding:3px;",
                              renderTo: 'menu_right_block_bottom',
                              width: '100%'
                             });
    }
    </script>
    {if isset($gui->plugins.EVENT_RIGHTMENU_BOTTOM)}
      <div id="plugin_right_bottom">
        {foreach from=$gui->plugins.EVENT_RIGHTMENU_BOTTOM item=menu_item}
          {$menu_item}
          <br/>
        {/foreach}
      </div>
    {/if}  
  {/if}
  {* ------------------------------------------------------------------------------------------ *}

</div>
<script>
jQuery( document ).ready(function() {
jQuery(".chosen-select").chosen({ width: "85%" });
});
</script>