{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 @filesource  mainPageLeft.tpl
 Purpose: smarty template - main page / site map                 
                                                                 
 @internal revisions
 @since 1.9.15
*}
{lang_get var='labels' s='title_product_mgmt,href_tproject_management,href_admin_modules,
                          href_assign_user_roles,href_cfields_management,system_config,
                          href_cfields_tproject_assign,href_keywords_manage,
                          title_user_mgmt,href_user_management,
                          href_roles_management,title_requirements,
                          href_req_spec,href_req_assign,link_report_test_cases_created_per_user,
                          title_test_spec,href_edit_tc,href_browse_tc,href_search_tc,
                          href_search_req, href_search_req_spec,href_inventory,
                          href_platform_management, href_inventory_management,
                          href_print_tc,href_keywords_assign, href_req_overview,
                          href_print_req,title_plugins,title_documentation,href_issuetracker_management,
                          href_reqmgrsystem_management,href_req_monitor_overview'}

{* Show / Hide section logic *}
{$display_left_block_1=false}
{$display_left_block_2=false}
{$display_left_block_3=false}
{$display_left_block_4=false}
{$display_left_block_5=$tlCfg->userDocOnDesktop}
{$display_left_block_top = false}
{$display_left_block_bottom = false}

{if $gui->testprojectID && 
   ($gui->grants.project_edit == "yes" || 
    $gui->grants.tproject_user_role_assignment == "yes" ||
    $gui->grants.cfield_management == "yes" || 
    $gui->grants.platform_management == "yes" || 
    $gui->grants.keywords_view == "yes")}
    
    {$display_left_block_1=true}
{/if}

{if $gui->testprojectID && 
   ($gui->grants.cfield_management == "yes" || $gui->grants.issuetracker_management || $gui->grants.issuetracker_view)}
   {$display_left_block_2=true}
{/if}

{if $gui->testprojectID && $gui->opt_requirements == TRUE && 
    ($gui->grants.reqs_view == "yes" || $gui->grants.reqs_edit == "yes")}
    {$display_left_block_3=true}
{/if}

{if $gui->testprojectID && $gui->grants.view_tc == "yes"}
    {$display_left_block_4=true}
{/if}

{if isset($gui->plugins.EVENT_LEFTMENU_TOP) &&  $gui->plugins.EVENT_LEFTMENU_TOP}
  {$display_left_block_top=true}
{/if}
{if isset($gui->plugins.EVENT_LEFTMENU_BOTTOM) &&  $gui->plugins.EVENT_LEFTMENU_BOTTOM}
  {$display_left_block_bottom=true}
{/if}





{$divStyle="width:300px;padding: 0px 0px 0px 10px;"}
{$aStyle="padding: 3px 15px;font-size:16px"}

{$projectView="lib/project/projectView.php"}
{$usersAssign="lib/usermanagement/usersAssign.php?featureType=testproject&featureID="}
{$cfAssignment="lib/cfields/cfieldsTprojectAssign.php"}
{$keywordsAssignment="lib/keywords/keywordsView.php?tproject_id="}
{$platformsView="lib/platforms/platformsView.php"}

<div class="vertical_menu" style="float: left; margin:0px 10px 10px 0px; width: 320px;">
{if $display_left_block_2}
  {$cfieldsView="lib/cfields/cfieldsView.php"}
  {$issueTrackerView="lib/issuetrackers/issueTrackerView.php"}
  <div class="list-group" style="{$divStyle}">
    {if $gui->grants.cfield_management == "yes"}
      <a href="{$cfieldsView}" class="list-group-item" style="{$aStyle}">{$labels.href_cfields_management}</a>
    {/if}

    {if $gui->grants.issuetracker_management || $gui->grants.issuetracker_view}
      <a href="{$issueTrackerView}" class="list-group-item" style="{$aStyle}">{$labels.href_issuetracker_management}</a>
    {/if}
  </div>
{/if}

{if $display_left_block_1}
  <div class="list-group" style="{$divStyle}">
    {if $gui->grants.project_edit == "yes"}
      <a href="{$projectView}" class="list-group-item" style="{$aStyle}">
        {$labels.href_tproject_management}</a>
    {/if}

    {if $gui->grants.tproject_user_role_assignment == "yes"}
      <a href="{$usersAssign}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.href_assign_user_roles}</a>
    {/if}
    
    {if $gui->grants.cfield_management == "yes"}
      <a href="{$cfAssignment}" class="list-group-item" style="{$aStyle}">{$labels.href_cfields_tproject_assign}</a>
    {/if}
    
    {if $gui->grants.keywords_view == "yes"}
      <a href="{$keywordsAssignment}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.href_keywords_manage}</a>
    {/if}

    {if $gui->grants.platform_management == "yes"}
      <a href="{$platformsView}" class="list-group-item" style="{$aStyle}">{$labels.href_platform_management}</a>
    {/if}
  </div>
{/if}

{if $display_left_block_3}
  {$reqOverView="lib/requirements/reqOverview.php"}
  {$reqMonOverView="lib/requirements/reqMonitorOverview.php?tproject_id="}
  <div class="list-group" style="{$divStyle}">
        {if $gui->grants.reqs_view == "yes"}
          <a href="{$gui->launcher}?feature=reqSpecMgmt" class="list-group-item" style="{$aStyle}">{$labels.href_req_spec}</a>
          <a href="{$reqOverView}" class="list-group-item" style="{$aStyle}">{$labels.href_req_overview}</a>
          <a href="{$gui->launcher}?feature=searchReq" class="list-group-item" style="{$aStyle}">{$labels.href_search_req}</a>
          <a href="{$gui->launcher}?feature=searchReqSpec" class="list-group-item" style="{$aStyle}">{$labels.href_search_req_spec}</a>
       {/if}
         
      {if $gui->grants.reqs_edit == "yes"}
        <a href="lib/general/frmWorkArea.php?feature=assignReqs" class="list-group-item" style="{$aStyle}">{$labels.href_req_assign}</a>
        <a href="{$reqMonOverView}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.href_req_monitor_overview}</a>
        <a href="{$gui->launcher}?feature=printReqSpec" class="list-group-item" style="{$aStyle}">{$labels.href_print_req}</a>
      {/if}
  </div>
{/if}

{if $display_left_block_4}
  {$tcSearch="lib/testcases/tcSearch.php?doAction=userInput&tproject_id="}
  {$tcCreatedUser="lib/results/tcCreatedPerUserOnTestProject.php?do_action=uinput&tproject_id="}

    <div class="list-group" style="{$divStyle}">
      <a href="{$gui->launcher}?feature=editTc" class="list-group-item" style="{$aStyle}">
        {if $gui->grants.modify_tc eq "yes"}
          {lang_get s='href_edit_tc'}
       {else}
          {lang_get s='href_browse_tc'}
       {/if}
      </a>
      {if $gui->hasTestCases}
        <a href="{$tcSearch}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.href_search_tc}</a>
      {/if}    
      
    {if $gui->hasKeywords}  
      {if $gui->grants.keywords_view == "yes" &&
          $gui->grants.keywords_edit == "yes"}
            <a href="{$gui->launcher}?feature=keywordsAssign" class="list-group-item" style="{$aStyle}">{$labels.href_keywords_assign}</a>
      {/if}
    {/if}
      
     {if $gui->grants.modify_tc eq "yes"}
       <a href="{$tcCreatedUser}{$gui->testprojectID}" class="list-group-item" style="{$aStyle}">{$labels.link_report_test_cases_created_per_user}</a>
     {/if}
    
    </div>
{/if}
</div>
