{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPageRight.tpl,v 1.2 2008/01/11 00:57:07 havlat Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :
       20070523 - franciscom - new config constant $smarty.const.TL_ITEM_BULLET_IMG
       20070508 - franciscom - milestones re-enabled                                                 
                               improving test plan messaging 
*}

{* Right Column                             *}
<div class="vertical_menu" style="float: right">
	{*** Begin Test Project section ***}
	
	{if $num_active_tplans > 0}
	<div class="testproject_title">
 {lang_get s='help' var='common_prefix'}
 {lang_get s='test_plan' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 {include file="inc_help.tpl" help="testPlan" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;vertical-align: top;"}

    
 	  <form name="testplanForm" action="lib/general/mainPage.php">
      {if $show_filter_tp_by_product }
      	<input type="hidden" name="filter_tp_by_product_hidden" value="0" /> 
        <input type="checkbox" name="filter_tp_by_product"  value="1" 
      	       {if $filter_tp_by_product}
      	         	checked="checked"
               {/if}  	                     	 
               onclick="this.form.submit();" />
      
			  {lang_get s='filter_tp_by_product'}
			  <br />	
		  {/if}  	                     	 
        
    {if $countPlans > 0}
		{lang_get s='title_test_plan'}
		<select style="display:inline;width:50%;"  name="testplan" onchange="this.form.submit();">
			{section name=tPlan loop=$arrPlans}
				<option value="{$arrPlans[tPlan].id}" 
				        {$arrPlans[tPlan].selected}
				        title="{$arrPlans[tPlan].name|escape}">
				        {$arrPlans[tPlan].name|truncate:#TESTPLAN_TRUNCATE_SIZE#|escape}
				</option>
			{/section}
			</select>
		{if $countPlans == 1}
			<input type="button" onclick="this.form.submit();" value="{lang_get s='ok'}"/>
		{/if}
		{if $testPlanRole neq null}
			<br />{lang_get s='testplan_role'} {$testPlanRole|escape}
		{/if}
	{else}
    {if $num_active_tplans > 0}
		{lang_get s='msg_no_rights_for_tp'}
	{/if}  
		{/if}
	 </form>
	</div>
  {/if}
	<br />
  {* ------------------------------------------------------------------------------------------ *}
  
  
	{* ------------------------------------------------------------------------------------------ *}
  {if $countPlans > 0}
    {$smarty.const.MENU_ITEM_OPEN}
	  <h3>{lang_get s='title_test_execution'}</h3>
		<p>
		{if $testplan_execute == "yes" }
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	        <a href="{$launcher}?feature=executeTest">{lang_get s='href_execute_test'}</a>
		{/if} {* testplan_execute *}


  	{if $testplan_metrics == "yes"}
	        <br />
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	        <a href="{$launcher}?feature=showMetrics">{lang_get s='href_rep_and_metrics'}</a>
		{/if} {* testplan_metrics *}
 	  <br />
 		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	       <a href="{$metrics_dashboard_url}">{lang_get s='href_metrics_dashboard'}</a>
	  </p>
    {$smarty.const.MENU_ITEM_CLOSE}
	
	
		{if $testplan_planning == "yes"}
    {$smarty.const.MENU_ITEM_OPEN}
	  
	    <h3>{lang_get s='title_test_case_suite'}</h3>
		<p>
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	        <a href="{$launcher}?feature=planAddTC">{lang_get s='href_add_test_case'}</a>
	        <br />
	        
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   		<a href="{$launcher}?feature=planRemoveTC">{lang_get s='href_remove_test_case'}</a>
	        <br />

			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   		<a href="{$launcher}?feature=newest_tcversions">{lang_get s='href_newest_tcversions'}</a>
	        <br />
	        
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   		<a href="{$launcher}?feature=tc_exec_assignment">{lang_get s='href_tc_exec_assignment'}</a>
	        <br />

      {* 20070204 - franciscom *}
	    {*
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   		<a href="{$launcher}?feature=priority">{lang_get s='href_plan_assign_priority'}</a>
	    <br />
      *} 
			{*
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   		<a href="lib/plan/planUpdateTC.php">{lang_get s='href_upd_mod_tc'}</a>
	    *}
		</p>
    {$smarty.const.MENU_ITEM_CLOSE}
	  {/if} {* testplan_planning *}
  {/if}

  {* ----------------------------------------------------------------------------------------- *}
	{if $testplan_planning == "yes" or $testplan_creating == "yes" or 
	    $tp_user_role_assignment == "yes" or $testplan_create_build == "yes"}
    {$smarty.const.MENU_ITEM_OPEN}
    <h3>{lang_get s='title_test_plan_mgmt'}</h3>
	{if $testplan_creating == "yes"}
		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
   		<a href="lib/plan/planView.php">{lang_get s='href_plan_management'}</a>
	{/if}
	{if $tp_user_role_assignment == "yes" && $countPlans > 0}
			<br />
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
    	    <a href="lib/usermanagement/usersassign.php?feature=testplan&amp;featureID={$sessionTestPlanID}">{lang_get s='href_assign_user_roles'}</a>
	{/if}
	{if $testplan_create_build == "yes" and $countPlans > 0}
		<br />
		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
       	<a href="lib/plan/buildView.php">{lang_get s='href_build_new'}</a>
    {/if} {* testplan_create_build *}
		{if $countPlans > 0 and $testplan_planning == "yes"}
	        <br />
	    	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	       	<a href="lib/plan/planMilestones.php">{lang_get s='href_plan_mstones'}</a>
			<br />
 		<!-- KL 20070222 - Priority are currently not supported in 1.7
			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	       	<a href="lib/plan/planPriority.php">{lang_get s='href_plan_define_priority'}</a>
	   	-->
		{/if}
    {$smarty.const.MENU_ITEM_CLOSE}
    {/if}
</div>
