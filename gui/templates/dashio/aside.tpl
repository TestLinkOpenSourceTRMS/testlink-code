{*
Testlink Open Source Project - http://testlink.sourceforge.net/
Left side menu
@filesource aside.tpl
*}
{$whoami = $smarty.template}
{include file="./labels/labels.$whoami"} 
    <aside>
      <div id="sidebar" class="nav-collapse ">
        <!-- sidebar menu start-->
        <ul class="sidebar-menu" id="nav-accordion">
          <p class="centered"><img src="{$gui->logo}"></p>
          <h4 class="centered"><a href="{$gui->userInfo}">{$gui->whoami}</a></h4>
          {if $gui->showMenu.dashboard == true}
          <li class="mt">
            <a class="{$gui->activeMenu.dashboard}" href="../project/projectView.php">
              <i class="fa fa-dashboard"></i>
              <span>Dashboard</span>
              </a>
          </li>
          {/if}
          {if $gui->showMenu.search == true}
            <li class="sub-menu">
              <a id="a_search" href="javascript:;" class="{$gui->activeMenu.search}">
                <i class="fa fa-search"></i>
                <span>{$labels.search}</span>
                </a>
              <ul class="sub">
                <li>
                  <a id="quick_search"
                     href="{$gui->uri->tcSearch}">{$labels.quick_search}</a>
                </li>
                <li>
                  <a id="href_search_tc"
                     href="{$gui->uri->tcSearch}">{$labels.href_search_tc}</a>
                </li>
                <li>
                  <a id="advanced_search"
                     href="{$gui->uri->fullTextSearch}">{$labels.advanced_search}</a>
                </li>
              </ul>
            </li>
          {/if}          
          {if $gui->showMenu.system == true}
            <li class="sub-menu">
              <a id="system" href="javascript:;" class="{$gui->activeMenu.system}">
                <i class="fa fa-desktop"></i>
                <span>{$labels.system}</span>
                </a>
              <ul class="sub">
                {if $gui->grants->event_viewer == "yes"}
                  <li>
                   <a id="events"
                      href="{$gui->uri->events}">{$labels.event_viewer}</a>
                  </li>
                {/if}
                {if $gui->grants->user_mgmt == "yes"}
                  <li>
                    <a id="userMgmt" href="{$gui->uri->userMgmt}">{$labels.title_user_mgmt}</a>
                  </li>
                {/if}

                {if $gui->grants->cfield_management == "yes"}
                  <li><a id="cfieldsView" href="{$gui->uri->cfieldsView}">{$labels.href_cfields_management}</a>
                  </li>
                {/if}
                {if $gui->access.issuetracker == 'yes'}
                  <li><a id="issueTrackerView" href="{$gui->uri->issueTrackerView}">{$labels.href_issuetracker_management}</a></li>
                {/if}
                {if $gui->access.codetracker == 'yes'}
                  <li><a id="codeTrackerView" href="{$gui->uri->codeTrackerView}">{$labels.href_codetracker_management}</a></li>
                {/if}
              </ul>
            </li>
          {/if}
          {if $gui->showMenu.projects == true}
            <li class="sub-menu">
              <a id="projects" href="javascript:;" class="{$gui->activeMenu.projects}">
                <i class="fa fa-flask"></i>
                <span>{$labels.projects}</span>
                </a>
              <ul class="sub">
                {if $gui->grants->project_edit == "yes"}
                  <li><a id="projectView" href="{$gui->uri->projectView}">{$labels.href_tproject_management}</a></li>
                {/if}
                {if $gui->grants->tproject_user_role_assignment == "yes"}
                  <li><a href="{$gui->uri->usersAssign}">{$labels.href_assign_user_roles}</a></li>
                {/if}
                {if $gui->grants->cfield_management == "yes"}
                  <li><a href="{$gui->uri->cfAssignment}">{$labels.href_cfields_tproject_assign}</a></li>
                {/if}
                {if $gui->grants->keywords_view == "yes"}
                  <li><a href="{$gui->uri->keywordsView}">{$labels.href_keywords_manage}</a></li>
                {/if}
                {if $gui->access.platform == 'yes'}
                  <li><a href="{$gui->uri->platformsView}">{$labels.href_platform_management}</a></li>
                {/if}

                {if $gui->countPlans > 0}
                <li><a href="{$gui->uri->metrics_dashboard}">{$labels.href_metrics_dashboard}</a>
                </li>  
                {/if}    
              </ul>
            </li>
          {/if}
          {if $gui->showMenu.requirements_design == true}
            <li class="sub-menu">
              <a href="javascript:;" class="{$gui->activeMenu.requirements_design}">
                <i class="fas fa-prescription-bottle"></i>
                <span>{$labels.requirements_design}</span>
                </a>
              <ul class="sub">
                <li><a href="{$gui->uri->reqSpecMgmt}">
                 {$labels.href_req_spec}</a></li>
                <li><a href="{$gui->uri->reqOverView}">{$labels.href_req_overview}</a></li>
                <li><a href="{$gui->uri->printReqSpec}">{$labels.href_print_req}</a></li>
                <li><a href="{$gui->uri->searchReq}">{$labels.href_search_req}</a></li>  
                <li><a href="{$gui->uri->searchReqSpec}">{$labels.href_search_req_spec}</a></li>  
                {if $gui->grants->req_tcase_link_management == "yes"}
                  <li><a href="{$gui->uri->assignReq}">{$labels.href_req_assign}</a></li>
                {/if}
                {if $gui->grants->monitor_req == "yes"}
                  <li><a href="{$gui->uri->reqMonOverView}">{$labels.href_req_monitor_overview}</a></li>
                {/if}
              </ul>
            </li>
          {/if}
          {if $gui->showMenu.tests_design == true}
            <li class="sub-menu">
              <a href="javascript:;" class="{$gui->activeMenu.tests_design}">
                <i class="fas fa-drafting-compass"></i>
                <span>{$labels.tests_design}</span>
                </a>
              <ul class="sub">
                <li><a href="{$gui->uri->testSpec}">
                 {if $gui->grants->modify_tc eq "yes"}
                   {lang_get s='href_edit_tc'}
                 {else}
                   {lang_get s='href_browse_tc'}
                 {/if}</a></li>
                
                {if $gui->grants->view_tc == "yes"}
                <li><a href="{$gui->uri->tcSearch}">{$labels.href_search_tc}</a></li>
                {/if}

                {if $gui->hasKeywords && 
                    $gui->grants->keyword_assignment == "yes"}
                  <li><a href="{$gui->uri->keywordsAssign}">{$labels.href_keywords_assign}</a></li>
                {/if}
                {if $gui->grants->modify_tc == 'yes'}
                  <li><a href="{$gui->uri->tcCreatedUser}">{$labels.link_report_test_cases_created_per_user}</a></li>
                {/if}
              </ul>
            </li>
          {/if}
          {if $gui->showMenu.plans == true}
            <li class="sub-menu">
              <a href="javascript:;" class="{$gui->activeMenu.plans}">
                <i class="fas fa-swatchbook"></i>
                <span>{$labels.title_test_plan_mgmt}</span>
                </a>
              <ul class="sub">
                {if $gui->grants->mgt_testplan_create == "yes"}
                <li><a href="{$gui->uri->planView}">
                 {$labels.href_plan_management}</a>
                </li>
                {/if}

                {if $gui->uri->buildView != null 
                    && $gui->grants->testplan_create_build == "yes" 
                    && $gui->countPlans > 0}
                  <li><a href="{$gui->uri->buildView}">{$labels.href_build_new}</a>
                  </li>  
                {/if}    

                {if $gui->uri->planAddTC != null}
                  <li><a href="{$gui->uri->planAddTC}">{$labels.href_add_remove_test_cases}</a>
                  </li>  
                {/if}

                {if $gui->uri->platformAssign != null
                    && $gui->grants->testplan_add_remove_platforms == "yes" 
                    && $gui->countPlans > 0}
                  <li><a href="{$gui->uri->platformAssign}">{$labels.href_platform_assign}</a>
                  </li>  
                {/if}    

                {if $gui->uri->setTestUrgency != null
                    && $gui->grants->testplan_set_urgent_testcases == "yes"}
                   <li><a href="{$gui->uri->setTestUrgency}">{$labels.href_plan_assign_urgency}</a>
                {/if}

                {if $gui->uri->planUpdateTC != null
                    && $gui->grants->testplan_update_linked_testcase_versions == "yes"}
                  <a href="{$gui->uri->planUpdateTC}">
                  {$labels.href_update_tplan}</a>
                {/if} 

                {if $gui->uri->showNewestTCV != null
                    && $gui->grants->testplan_show_testcases_newest_versions == "yes"}
                  <a href="{$gui->uri->showNewestTCV}">{$labels.href_newest_tcversions}</a>
                {/if} 
              </ul>
            </li>
          {/if}
          {if $gui->showMenu.execution == true}
            <li class="sub-menu">
              <a href="javascript:;" class="{$gui->activeMenu.execution}">
                <i class="fas fa-gamepad"></i>
                <span>{$labels.testcase_execution}</span>
                </a>
              <ul class="sub">
                {if $gui->grants->testplan_execute == "yes"}
                  {$lbx = $labels.href_execute_test}
                {/if}
                {if $gui->grants->exec_ro_access == "yes"}  
                  {$lbx = $labels.href_exec_ro_access}
                {/if}

                {if $gui->uri->executeTest != null}
                  <li><a href="{$gui->uri->executeTest}">
                   {$lbx}</a></li>
                {/if}

                {if $gui->uri->assignTCVExecution != null}
                  <li><a href="{$gui->uri->assignTCVExecution}">
                  {$labels.href_tc_exec_assignment}</a></li>
                {/if}
                  
                {if $gui->uri->testcase_assignments != null
                    && $gui->grants->exec_testcases_assigned_to_me == "yes"}
                  <li><a href="{$gui->uri->testcase_assignments}">{$labels.href_my_testcase_assignments}</a>
                {/if} 

                {if $gui->uri->milestonesView != null
                    && $gui->grants->testplan_milestone_overview == "yes" 
                    && $gui->countPlans > 0}
                  <li><a href="{$gui->uri->milestonesView}" >{$labels.href_plan_mstones}</a></li>
                {/if}
              </ul>
            </li>
          {/if}

          {if $gui->showMenu.reports == true}
            <li>
              <a class="{$gui->activeMenu.reports}" 
                 href="{$gui->uri->showMetrics}">
                <i class="fas fa-chart-line"></i>
                <span>{$labels.reports}</span>
              </a>
            </li>
          {/if}
        </ul>
        <!-- sidebar menu end-->
      </div>
    </aside>
