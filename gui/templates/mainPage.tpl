{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPage.tpl,v 1.18 2006/08/21 13:19:38 franciscom Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :                                                   
       20060819 - franciscom - changed css classes name
                               removed old comments
       
*}
{include file="inc_head.tpl" popup="yes"}

<body>
<h1>{lang_get s='title_testlink_site_map'}</h1>
{if $securityNotes}
    {include file="inc_msg_from_array.tpl" array_of_msg=$securityNotes arg_css_class="warning_message"}
{/if}

{* Right Column                             *}
<div class="vertical_menu" style="float: right">
	{*** Begin Test Project section ***}
	<div class="testproject_title">
		<img alt="Help: Test Plan" style="float: right; vertical-align: top;" 
			src="icons/sym_question.gif" 
			onclick="javascript:open_popup('{$helphref}testPlan.html');" />

 	  <form name="testplanForm" action="lib/general/mainPage.php">
      {if $show_filter_tp_by_product }
      	<input type="hidden" name="filter_tp_by_product_hidden" value="0" /> 
        <input type="checkbox" name="filter_tp_by_product"  value="1" 
      	       {if $filter_tp_by_product}
      	         	checked="checked"
               {/if}  	                     	 
               onclick="this.form.submit();" />
      
			  {lang_get s='filter_tp_by_product'}
			  <br /><br />	
		  {/if}  	                     	 
        
		{lang_get s='title_test_plan'}
    {if $countPlans > 0}
				<select name="testplan" onchange="this.form.submit();">
				{section name=tPlan loop=$arrPlans}
					<option value="{$arrPlans[tPlan].id}" 
					{$arrPlans[tPlan].selected} >{$arrPlans[tPlan].name|escape}</option>
				{/section}
				</select>
				{if $testPlanRole neq null}
					<br />{lang_get s='testplan_role'} {$testPlanRole|escape}
				{/if}
		{else}
			{lang_get s='msg_no_rights_for_tp'}
		{/if}
	 </form>
	</div>

	{if $countPlans > 0}
	    <h2>{lang_get s='title_test_execution'}</h2>
		<p>
		{if $testplan_execute == "yes" }
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	        <a href="{$launcher}?feature=executeTest">{lang_get s='href_execute_test'}</a>
	        <br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	       	<a href="{$launcher}?feature=printTestSet">{lang_get s='href_print_tc_suite'}</a>
		{/if} {* testplan_execute *}


  	{if $testplan_metrics == "yes"}
	        <br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	        <a href="{$launcher}?feature=showMetrics">{lang_get s='href_rep_and_metrics'}</a>
		{/if} {* testplan_metrics *}
	  </p>
	
		{if $testplan_planning == "yes"}
	    <h2>{lang_get s='title_test_case_suite'}</h2>
		<p>
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	        <a href="{$launcher}?feature=testSetAdd">{lang_get s='href_add_test_case'}</a>
	        <br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	   		<a href="{$launcher}?feature=testSetRemove">{lang_get s='href_remove_test_case'}</a>
	        <br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	   		<a href="{$launcher}?feature=priority">{lang_get s='href_assign_risk_own'}</a>
	        <br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	   		<a href="lib/plan/planUpdateTC.php">{lang_get s='href_upd_mod_tc'}</a>
	    </p>
		{/if} {* testplan_planning *}
	{/if}

	{if $testplan_planning == "yes"}
    <h2>{lang_get s='title_test_plan_mgmt'}</h2>
	<p>
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
   		<a href="lib/plan/planNew.php">{lang_get s='href_plan_new'}</a><br />
   		
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	   		<a href="lib/plan/planEdit.php">{lang_get s='href_plan_edit'}</a>
		{if $countPlans > 0}
	        <br />
			<!--
			<img class="arrow" src="icons/arrow_org.gif" />
	   		<a href="{$launcher}?feature=planAssignTesters">{lang_get s='href_plan_assign_users'}</a>
			<br /><br />
			-->
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
    	    <a href="lib/usermanagement/usersassign.php?feature=testplan&amp;featureID={$sessionTestPlanID}">{lang_get s='href_assign_user_roles'}</a>
			<br /><br />
	
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	       	<a href="lib/plan/planMilestones.php">{lang_get s='href_plan_mstones'}</a>
   		{/if}
    </p>
	{/if} {* testplan_planning *}
	{if $testplan_create_build == "yes" and $countPlans > 0}
	<p>
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
       	<a href="lib/plan/buildNew.php">{lang_get s='href_build_new'}</a>
    </p>
	{/if} {* testplan_create_build *}
</div>


{*   left column *}
<div class="vertical_menu" style="float: left">

	{*   tc management   *}
	{if $view_tc_rights == "yes"}
    <h2>{lang_get s='title_test_spec'}</h2>
	<p>
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
		<a href="{$launcher}?feature=editTc">
		{if $modify_tc_rights eq "yes"}
	      {lang_get s='href_edit_tc'}
	  {else}
	      {lang_get s='href_browse_tc'}
	  {/if}
	  </a>
    <br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
        <a href="{$launcher}?feature=searchTc">{lang_get s='href_search_tc'}</a>
		{if $modify_tc_rights eq "yes"}
	        <br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
        	<a href="{$launcher}?feature=printTc">{lang_get s='href_print_tc'}</a>
	        <br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
        	<a href="lib/testcases/tcImport.php">{lang_get s='href_import_tc'}</a>
		{/if}
    </p>
	{/if} {* view_tc_rights *}

	{*   requirements   *}
	{if $opt_requirements == TRUE && $view_req_rights == "yes"}
        <h2>{lang_get s='title_requirements'}</h2>
		<p>
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
   		<a href="lib/req/reqSpecList.php">{lang_get s='href_req_spec'}</a>
		{if $opt_requirements == TRUE && $modify_req_rights == "yes"}
			<br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
       		<a href="lib/general/frmWorkArea.php?feature=assignReqs">{lang_get s='href_req_assign'}</a>
       	{/if}
        </p>
     {/if}

	{*       keywords management                             *}
	
	{if $view_keys_rights == "yes"}
        <h2>{lang_get s='title_keywords'}</h2>
		<p>
		{if $modify_keys_rights == "yes"}
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_manage'}</a>
	        <br />
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
        	<a href="{$launcher}?feature=keywordsAssign">{lang_get s='href_keywords_assign'}</a>
		{else} 		
			<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_view'}</a>
	        <br />
		{/if} {* modify_keys_rights *}
        </p>

	{/if} {* view_keys_rights *}

	{if $modify_product_rights == "yes"}

        <h2>{lang_get s='title_product_mgmt'}</h2>
		<p>
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
		    {* 20060305 - franciscom *}
        <a href="lib/project/projectedit.php?show_create_screen">{lang_get s='href_create_product'}</a>
        <br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/project/projectedit.php">{lang_get s='href_edit_product'}</a>
        <br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/usermanagement/usersassign.php?feature=testproject&amp;featureID={$sessionProductID}">{lang_get s='href_assign_user_roles'}</a>
        </p>
	{/if} {* modify_product_rights *}
	
	{*       user management                             *}
	
	{if $usermanagement_rights == "yes"}
        <h2>{lang_get s='title_user_mgmt'}</h2>
		<p>
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/usermanagement/usersedit.php">{lang_get s='href_user_management'}</a>
        <br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/usermanagement/rolesview.php">{lang_get s='href_roles_management'}</a>
        </p>
	{/if}

	{*       Class API information - added 20060630 by KL - I will localize this and 
	make it optional to display at some point soon *******}
	<h2>Class APIs (for developers)</h2>
	<p>
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
		<a href="lib/functions/database.class.test.php">database.class.php</a>
		<br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
		<a href="lib/functions/testcase.class.test.php">testcase.class.php</a>
		<br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
		<a href="lib/functions/testplan.class.test.php">testplan.class.php</a>
		<br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
		<a href="lib/functions/testproject.class.test.php">testproject.class.php</a>
		<br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
		<a href="lib/functions/testsuite.class.test.php">testsuite.class.php</a>
		<br />
		<img alt="arrow" class="arrow" src="icons/arrow_org.gif" />
		<a href="lib/functions/tree.class.test.php">tree.class.php</a>
		<br />
	</p>

</div>


{*      ** middle table ************}

{if $metricsEnabled == 'TRUE'}
    <div style="width: 45%; padding: 5px">
	    <table class="mainTable" style="width: 100%">
		<tr>
			<td colspan="3"><h2>{lang_get s='title_your_tp_metrics'}</h2></td>
		</tr>
		<tr>
			<th>{lang_get s='th_name'}</th>
			<th>{lang_get s='th_perc_completed'}</th>
			<th>{lang_get s='th_my_perc_completed'}</th>
		</tr>
	       {$myTPdata}
	    </table>
    </div>
{/if}

</body>
</html>
