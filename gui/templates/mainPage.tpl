{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPage.tpl,v 1.10 2006/02/19 13:03:32 schlundus Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
                                                                 
 rev :                                                   
      20051002 - fm - changes to filter tp by product      
      20050929 - fm - new checkbox - filter tp by product
      20050809 - fm - I18N - missing string                                      
	
20051118 - scs - added escaping of testplan names                                                                 
20051112 - scs - changed keyword related menu items
20060106 - scs - changes because of changing the newProduct functionality
*}
{include file="inc_head.tpl" popup="yes"}

<body>

<h1>{lang_get s='title_testlink_site_map'}</h1>

{****** TEST PLAN - Right Column ***************************}
<div class="columnList" style="float: right">

	{*** Begin product section ***}
	<div class="productTitle">
		<img alt="Help: Test Plan" style="float: right; vertical-align: top;" 
			src="icons/sym_question.gif" 
			onclick="javascript:open_popup('{$helphref}testPlan.html');" />

 	  <form name="testplanForm" action="lib/general/mainPage.php">

    	{* 20051002 - fm *}
      {if $show_filter_tp_by_product }
      
      	{* 20050928 - fm *}
      	<input type="hidden" name="filter_tp_by_product_hidden" value="0"> 
        <input type="checkbox" name="filter_tp_by_product"  value="1" 
      	       {if $filter_tp_by_product}
      	         	checked="checked"
               {/if}  	                     	 
               onclick="this.form.submit();" />
      
			  {lang_get s='filter_tp_by_product'}
			  <br><br>	
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
					- {lang_get s='testplan_role'}{$testPlanRole|escape}
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
			<img class="arrow" src="icons/arrow_org.gif" />
	        <a href="{$launcher}?feature=executeTest">{lang_get s='href_execute_test'}</a>
	        <br />
			<img class="arrow" src="icons/arrow_org.gif" />
	       	<a href="{$launcher}?feature=printTestSet">{lang_get s='href_print_tc_suite'}</a>
		{/if} {* testplan_execute *}


    {* change date"2005-04-16" author="fm" feature=metrics -> feature=showMetrics*} 
		{if $testplan_metrics == "yes"}
	        <br />
			<img class="arrow" src="icons/arrow_org.gif" />
	        <a href="{$launcher}?feature=showMetrics">{lang_get s='href_rep_and_metrics'}</a>
		{/if} {* testplan_metrics *}
	    </p>
	
		{if $testplan_planning == "yes"}
	    <h2>{lang_get s='title_test_case_suite'}</h2>
		<p>
			<img class="arrow" src="icons/arrow_org.gif" />
	        <a href="{$launcher}?feature=testSetAdd">{lang_get s='href_add_test_case'}</a>
	        <br />
			<img class="arrow" src="icons/arrow_org.gif" />
	   		<a href="{$launcher}?feature=testSetRemove">{lang_get s='href_remove_test_case'}</a>
	        <br />
			<img class="arrow" src="icons/arrow_org.gif" />
	   		<a href="{$launcher}?feature=priority">{lang_get s='href_assign_risk_own'}</a>
	        <br />
			<img class="arrow" src="icons/arrow_org.gif" />
	   		<a href="lib/plan/planUpdateTC.php">{lang_get s='href_upd_mod_tc'}</a>
	    </p>
		{/if} {* testplan_planning *}
	{/if}

	{if $testplan_planning == "yes"}
    <h2>{lang_get s='title_test_plan_mgmt'}</h2>
	<p>
		<img class="arrow" src="icons/arrow_org.gif" />
   		<a href="lib/plan/planNew.php">{lang_get s='href_plan_new'}</a><br />
   		
			<img class="arrow" src="icons/arrow_org.gif" />
	   		<a href="lib/plan/planEdit.php">{lang_get s='href_plan_edit'}</a>
		{if $countPlans > 0}
	        <br />
			<!--
			<img class="arrow" src="icons/arrow_org.gif" />
	   		<a href="{$launcher}?feature=planAssignTesters">{lang_get s='href_plan_assign_users'}</a>
			<br /><br />
			-->
			<img class="arrow" src="icons/arrow_org.gif" />
    	    <a href="lib/usermanagement/usersassign.php?feature=testplan&featureID={$sessionTestPlanID}">{lang_get s='href_assign_user_roles'}</a>
			<br /><br />
	
			<img class="arrow" src="icons/arrow_org.gif" />
	       	<a href="lib/plan/planMilestones.php">{lang_get s='href_plan_mstones'}</a>
   		{/if}
    </p>
	{/if} {* testplan_planning *}
	{if $testplan_create_build == "yes" and $countPlans > 0}
	<p>
		<img class="arrow" src="icons/arrow_org.gif" />
       	<a href="lib/plan/buildNew.php">{lang_get s='href_build_new'}</a>
    </p>
	{/if} {* testplan_create_build *}
</div>


{******** product section - left column ***********************}
<div class="columnList" style="float: left">

	{*** tc management ***}
	{if $view_tc_rights == "yes"}
    <h2>{lang_get s='title_test_spec'}</h2>
	<p>
		<img class="arrow" src="icons/arrow_org.gif" />
		{if $modify_tc_rights eq "yes"}
	        <a href="{$launcher}?feature=editTc">{lang_get s='href_edit_tc'}</a>
	    {else}
	        <a href="{$launcher}?feature=editTc">{lang_get s='href_browse_tc'}</a>
	    {/if}
        <br />
		<img class="arrow" src="icons/arrow_org.gif" />
        <a href="{$launcher}?feature=searchTc">{lang_get s='href_search_tc'}</a>
		{if $modify_tc_rights eq "yes"}
	        <br />
			<img class="arrow" src="icons/arrow_org.gif" />
        	<a href="{$launcher}?feature=printTc">{lang_get s='href_print_tc'}</a>
	        <br />
			<img class="arrow" src="icons/arrow_org.gif" />
        	<a href="lib/testcases/tcImport.php">{lang_get s='href_import_tc'}</a>
		{/if}
    </p>
	{/if} {* view_tc_rights *}

	{****** requirements *****************************}
	
	{if $opt_requirements == TRUE && $view_req_rights == "yes"}
        <h2>{lang_get s='title_requirements'}</h2>
		<p>
		<img class="arrow" src="icons/arrow_org.gif" />
   		<a href="lib/req/reqSpecList.php">{lang_get s='href_req_spec'}</a>
		{if $opt_requirements == TRUE && $modify_req_rights == "yes"}
			<br />
			<img class="arrow" src="icons/arrow_org.gif" />
       		<a href="lib/general/frmWorkArea.php?feature=assignReqs">{lang_get s='href_req_assign'}</a>
       	{/if}
        </p>
     {/if}

	{****** keywords management ***********************}
	
	{if $view_keys_rights == "yes"}
        <h2>{lang_get s='title_keywords'}</h2>
		<p>
		{if $modify_keys_rights == "yes"}
			<img class="arrow" src="icons/arrow_org.gif" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_manage'}</a>
	        <br />
			<img class="arrow" src="icons/arrow_org.gif" />
        	<a href="{$launcher}?feature=keywordsAssign">{lang_get s='href_keywords_assign'}</a>
		{else} 		
			<img class="arrow" src="icons/arrow_org.gif" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_view'}</a>
	        <br />
		{/if} {* modify_keys_rights *}
        </p>

	{/if} {* view_keys_rights *}

	{if $modify_product_rights == "yes"}

        <h2>{lang_get s='title_product_mgmt'}</h2>
		<p>
		<img class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/admin/adminProductEdit.php?createProduct=1">{lang_get s='href_create_product'}</a>
        <br />
		<img class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/admin/adminProductEdit.php">{lang_get s='href_edit_product'}</a>
        <br />
		<img class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/usermanagement/usersassign.php?feature=product&featureID={$sessionProductID}">{lang_get s='href_assign_user_roles'}</a>
        </p>
	{/if} {* modify_product_rights *}
	
	{****** user management ***********************}
	
	{if $usermanagement_rights == "yes"}
        <h2>{lang_get s='title_user_mgmt'}</h2>
		<p>
		<img class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/usermanagement/usersedit.php">{lang_get s='href_user_management'}</a>
        <br />
		<img class="arrow" src="icons/arrow_org.gif" />
        <a href="lib/usermanagement/rolesview.php">{lang_get s='href_roles_management'}</a>
        </p>
	{/if}
</div>


{******** middle table ************}

{if $metricsEnabled == 'TRUE'}
    <div style="width: 45%; padding: 5px">
    <h2>{lang_get s='title_your_tp_metrics'}</h2>

    <table class="mainTable" style="width: 100%">
       <tr>
          <th>{lang_get s='th_name'}</th>
        	<th>{lang_get s='th_perc_completed'}</th>
        	<th>{lang_get s='th_my_perc_completed'}</th>
       </tr>
       {$myTPdata}
    </table>
    </div>
{/if}

	{if $securityNotes}
	
	<div class="bold" style="color:red">
		{foreach from=$securityNotes item=secNote}
			<br/>{lang_get s='sec_note_prefix'} : {$secNote|escape}
		{/foreach}
	</div>
	{/if}

</body>
</html>
