{*
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource labels.aside.tpl
*}
{lang_get 
  var="l10n" 
  s='title_product_mgmt,href_tproject_management,href_admin_modules,
   href_assign_user_roles,href_cfields_management,system_config,
   href_cfields_tproject_assign,href_keywords_manage,
   title_user_mgmt,href_user_management,
   href_roles_management,title_requirements,
   href_req_spec,href_req_assign,link_report_test_cases_created_per_user,
   title_test_spec,href_edit_tc,href_browse_tc,href_search_tc,
   href_search_req, href_search_req_spec,href_inventory,
   href_platform_management, href_inventory_management,
   href_print_tc,href_keywords_assign, href_req_overview,
   href_print_req,title_plugins,title_documentation,href_issuetracker_management,projects,system,tests_design,requirements_design,
   href_codetracker_management,href_reqmgrsystem_management,href_req_monitor_overview,current_test_plan,ok,testplan_role,msg_no_rights_for_tp,
   title_test_execution,href_execute_test,href_rep_and_metrics,
   href_update_tplan,href_newest_tcversions,title_plugins,
   href_my_testcase_assignments,href_platform_assign,
   href_tc_exec_assignment,href_plan_assign_urgency,
   href_upd_mod_tc,title_test_plan_mgmt,title_test_case_suite,
   href_plan_management,href_assign_user_roles,
   href_build_new,href_plan_mstones,href_plan_define_priority,
   href_metrics_dashboard,href_add_remove_test_cases,
   href_exec_ro_access,testcase_execution,event_viewer,reports,
   search,quick_search,advanced_search'}

{* make labels available to parent/includer template *}
{assign var="labels" value=$l10n scope=parent}