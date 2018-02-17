{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 @filesource  mainPageLeft.tpl
 Purpose: smarty template - main page / site map                 
                                                                 
 @internal revisions
 @since 1.9.15
*}
{lang_get var='labels' s='title_product_mgmt,href_tproject_management,href_admin_modules,
                          href_assign_user_roles,href_cfields_management,
                          href_cfields_tproject_assign,href_keywords_manage,
                          title_user_mgmt,href_user_management,
                          href_roles_management,title_requirements,
                          href_req_spec,href_req_assign,link_report_test_cases_created_per_user,
                          title_test_spec,href_edit_tc,href_browse_tc,href_search_tc,
                          href_search_req, href_search_req_spec,
                          href_platform_management, href_inventory_management,
                          href_keywords_assign, href_req_overview,
                          href_print_req,title_plugins,href_issuetracker_management,
                          href_codetracker_management,href_req_monitor_overview'}

{* Show / Hide section logic *}
{$display_left_block_1=false}
{$display_left_block_2=false}
{$display_left_block_3=false}
{$display_left_block_4=false}
{$display_left_block_5=$tlCfg->userDocOnDesktop}
{$display_left_block_top = false}
{$display_left_block_bottom = false}

{* Left Block 1 : Server Management : Projects, Issues Trackers, Code Trackers, Users, Roles, Custom Fields *}
{if $gui->testprojectID &&
   ($gui->grants.project_edit == "yes" || 
    $gui->grants.issuetracker_management == "yes" ||
	$gui->grants.issuetracker_view == "yes" || 
    $gui->grants.codetracker_management == "yes" || 
	$gui->grants.codetracker_view == "yes" ||
    $gui->grants.mgt_users == "yes" ||
    $gui->grants.user_role_assignment == "yes" ||
    $gui->grants.role_management == "yes" ||
    $gui->grants.cfield_management == "yes" || 
    $gui->grants.cfield_view == "yes")}
    {$display_left_block_1=true}
{/if}

{* Left Block 2 : Current Project Management : Assign Customs Fields, Assign Users, Keywords, PlatFroms *}

{if $gui->testprojectID && 
   ($gui->grants.cfield_assignment == "yes" || 
    $gui->grants.tproject_user_role_assignment == "yes" || 
    $gui->grants.platform_view == "yes" || 
    $gui->grants.platform_management == "yes" || 
    $gui->grants.keywords_view == "yes" || 
    $gui->grants.mgt_modify_key == "yes" ||
    $gui->grants.project_inventory_view == "yes" || 
    $gui->grants.project_inventory_management == "yes")}
   {$display_left_block_2=true}
{/if}

{* Left Block 3 : Requirements Management *}
{if $gui->testprojectID && $gui->opt_requirements == TRUE && 
    ($gui->grants.reqs_view == "yes" || $gui->grants.reqs_edit == "yes" || $gui->grants.monitor_req == "yes" || $gui->grants.req_tcase_link_management == "yes")}
    {$display_left_block_3=true}
{/if}

{* Left Block 4 : TestCases Management *}
{if $gui->testprojectID && $gui->grants.view_tc == "yes" || $gui->grants.modify_tc == "yes"}
    {$display_left_block_4=true}
{/if}


{* Left Blocks TOP and BOTTOM : Plugins containers *}

{if isset($gui->plugins.EVENT_LEFTMENU_TOP) &&  $gui->plugins.EVENT_LEFTMENU_TOP}
  {$display_left_block_top=true}
{/if}
{if isset($gui->plugins.EVENT_LEFTMENU_BOTTOM) &&  $gui->plugins.EVENT_LEFTMENU_BOTTOM}
  {$display_left_block_bottom=true}
{/if}





{$divStyle="width:360px;padding: 0px 0px 0px 10px;"}
{$aStyle="text-align:center;padding: 3px 15px;font-size:16px"}


<div class="vertical_menu" style="float: left; margin:40px 10px 10px 50px; width: 380px;">
{if $display_left_block_1}
  <div class="list-group" style="{$divStyle}">
    <div class="list-group-item active" style="{$aStyle}">{$labels.href_admin_modules}</div>
    {if $gui->grants.project_edit == "yes"}
	  {$projectView="lib/project/projectView.php"}
	  <a href="{$projectView}" class="list-group-item" style="{$aStyle}">{$labels.href_tproject_management}</a>
    {/if}
    {if $gui->grants.issuetracker_management || $gui->grants.issuetracker_view}
	  {$issueTrackerView="lib/issuetrackers/issueTrackerView.php"}
      <a href="{$issueTrackerView}" class="list-group-item" style="{$aStyle}">{$labels.href_issuetracker_management}</a>
    {/if}
    {if $gui->grants.codetracker_management || $gui->grants.codetracker_view}
	  {$codeTrackerView="lib/issuetrackers/codeTrackerView.php"}
      <a href=""{$codeTrackerView}" class="list-group-item" style="{$aStyle}">{$labels.href_codetracker_management}</a>
    {/if}
    {if $gui->grants.mgt_users == "yes" || $gui->grants.user_role_assignment == "yes"}
	  {$usersView="lib/usermanagement/usersView.php"}
      <a href="{$usersView}" class="list-group-item" style="{$aStyle}">{$labels.href_user_management}</a>
    {/if}
    {if $gui->grants.role_management == "yes"}
	  {$rolesView="lib/usermanagement/rolesView.php"}
      <a href="{$rolesView}" class="list-group-item" style="{$aStyle}">{$labels.href_roles_management}</a>
    {/if}
    {if $gui->grants.cfield_management == "yes" || $gui->grants.cfield_view == "yes"}
	  {$cfieldsView="lib/cfields/cfieldsView.php"}
      <a href="{$cfieldsView}" class="list-group-item" style="{$aStyle}">{$labels.href_cfields_management}</a>
    {/if}
  </div>
{/if}

{if $display_left_block_2}

  <div class="list-group" style="{$divStyle}">
    <div class="list-group-item active" style="{$aStyle}">{$labels.title_product_mgmt}</div>

    {if $gui->grants.cfield_assignment == "yes"}
	  {$cfAssignment="lib/cfields/cfieldsTprojectAssign.php"}
      <a href="{$cfAssignment}" class="list-group-item" style="{$aStyle}">{$labels.href_cfields_tproject_assign}</a>
    {/if}
    {if $gui->grants.tproject_user_role_assignment == "yes"}
	  {$usersAssign="lib/usermanagement/usersAssign.php?featureType=testproject&featureID="}
      <a href="{$usersAssign}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.href_assign_user_roles}</a>
    {/if}
    {if $gui->grants.keywords_view == "yes" || $gui->grants.mgt_modify_key == "yes"}
	  {$keywordsAssignment="lib/keywords/keywordsView.php?tproject_id="}
      <a href="{$keywordsAssignment}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.href_keywords_manage}</a>
    {/if}
    {if $gui->grants.platform_view == "yes" || $gui->grants.platform_management == "yes"}
	  {$platformsView="lib/platforms/platformsView.php"}
      <a href="{$platformsView}" class="list-group-item" style="{$aStyle}">{$labels.href_platform_management}</a>
    {/if}
	{if $gui->hasInventoryEnabled && ($gui->grants.project_inventory_view == "yes" || $gui->grants.project_inventory_management == "yes")}
	  {$inventoryView="lib/inventory/inventoryView.php"}
      <a href="{$inventoryView}" class="list-group-item" style="{$aStyle}">{$labels.href_inventory_management}</a>
    {/if}
  </div>
{/if}

{if $display_left_block_3}
  <div class="list-group" style="{$divStyle}">
      <div class="list-group-item active" style="{$aStyle}">{$labels.title_requirements}</div>
       {if $gui->grants.reqs_view == "yes" || $gui->grants.reqs_edit == "yes" }
          <a href="{$gui->launcher}?feature=reqSpecMgmt" class="list-group-item" style="{$aStyle}">{$labels.href_req_spec}</a>
         {if $gui->grants.monitor_req == "yes"}
		    {$reqMonOverView="lib/requirements/reqMonitorOverview.php?tproject_id="}
            <a href="{$reqMonOverView}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.href_req_monitor_overview}</a>
         {/if}
         {if $gui->grants.req_tcase_link_management == "yes"}
            <a href="lib/general/frmWorkArea.php?feature=assignReqs" class="list-group-item" style="{$aStyle}">{$labels.href_req_assign}</a>
         {/if}
		  {$reqOverView="lib/requirements/reqOverview.php"}
          <a href="{$reqOverView}" class="list-group-item" style="{$aStyle}">{$labels.href_req_overview}</a>
          <a href="{$gui->launcher}?feature=printReqSpec" class="list-group-item" style="{$aStyle}">{$labels.href_print_req}</a>
          <a href="{$gui->launcher}?feature=searchReq" class="list-group-item" style="{$aStyle}">{$labels.href_search_req}</a>
          <a href="{$gui->launcher}?feature=searchReqSpec" class="list-group-item" style="{$aStyle}">{$labels.href_search_req_spec}</a>
       {/if}
  </div>
{/if}

{if $display_left_block_4}


    <div class="list-group" style="{$divStyle}">
      <div class="list-group-item active" style="{$aStyle}">{$labels.title_test_spec}</div>
      <a href="{$gui->launcher}?feature=editTc" class="list-group-item" style="{$aStyle}">
        {if $gui->grants.modify_tc eq "yes"}
          {lang_get s='href_edit_tc'}
       {else}
          {lang_get s='href_browse_tc'}
       {/if}
      </a>
      {if $gui->hasTestCases}
	    {$tcSearch="lib/testcases/tcSearch.php?doAction=userInput&tproject_id="}
        <a href="{$tcSearch}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.href_search_tc}</a>
      {/if}    
      
    {if $gui->hasKeywords}  
      {if $gui->grants.keyword_assignment == "yes"}
            <a href="{$gui->launcher}?feature=keywordsAssign" class="list-group-item" style="{$aStyle}">{$labels.href_keywords_assign}</a>
      {/if}
    {/if}
      
     {if $gui->grants.modify_tc eq "yes"}
	  {$tcCreatedUser="lib/results/tcCreatedPerUserOnTestProject.php?do_action=uinput&tproject_id="}
       <a href="{$tcCreatedUser}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.link_report_test_cases_created_per_user}</a>
     {/if}
    
    </div>
{/if}
</div>
