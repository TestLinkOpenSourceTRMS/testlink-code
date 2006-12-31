<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: userrightmatrix.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2006/12/31 16:13:23 $  $Author: franciscom $
 *
 * @author Andreas Morsing
 *
 * This page allows configuration of the accessrights needed for
 * executing pages
 *
 * 20060818 - franciscom - changes due to addition of new rights
 *
 *												 role_management
 *                         user_role_assignment
 *
 *
 *
 * 
**/
//user right matrix, 
//for each file which calls testLinkInitPage it's 
//possible to set the rights needed to execute the script
//
//keys are the filenames (lowercase)
//values are the right(s) needed to execute it, 
//maybe array : multiple rights needed
//		  string : exactly one right need
//		  null : no rights need


// urls
$user_admin_url='lib/usermanagement';
$proj_admin_url='lib/project';
$test_exec_url='lib/execute';
$kword_admin_url='lib/keywords';
$tplan_admin_url='lib/plan';
$req_admin_url='lib/req';
$reports_url='lib/result';
$tc_admin_url='lib/testcases';
$cf_admin_url='lib/cfields';
$print_url='lib/print';



// 
$user_admin=array("$user_admin_url/usersnew.php"  => array("mgt_users",),														
						      "$user_admin_url/usersedit.php" => array("mgt_users",),														
						      "$user_admin_url/usersview.php" => array("mgt_users",),														
						      "$user_admin_url/rolesview.php" => array("role_view",),														
						      "$user_admin_url/rolesedit.php" => array("role_management",),														
						      "$user_admin_url/usersassign.php"  => array ("user_role_assignment",));														
                  
                  
$proj_admin=array("$proj_admin_url/projectedit.php" => array("mgt_modify_product",));

$test_exec=array("$test_exec_url/execnavigator.php" => array("testplan_execute",));

$kword_admin=array("$kword_admin_url/keywordsview.php"   => array("mgt_view_key",),
 						       "$kword_admin_url/keywordsassign.php" => array("mgt_modify_key",));

$build_admin=array("$tplan_admin_url/buildnew.php" => array("testplan_create_build",));

$tplan_admin=array("$tplan_admin_url/planupdatetc.php" => array("testplan_planning",),
						       "$tplan_admin_url/planaddtc.php" => array("testplan_planning",),														
						       "$tplan_admin_url/planaddtcnavigator.php" => array("testplan_planning",),														
						       "$tplan_admin_url/planedit.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/planmilestones.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/plannew.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/planowner.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/planpriority.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/plantestersedit.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/plantestersnavigator.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/planupdatetc.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/planmilestoneedit.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/testsetnavigator.php" => array("testplan_planning",),														
       						 "$tplan_admin_url/testsetremove.php" => array("testplan_planning",));														


$req_admin=array("$req_admin_url/reqspeclist.php" => array("mgt_view_req",),														
						     "$req_admin_url/reqspecanalyse.php" => array("mgt_view_req",),														
						     "$req_admin_url/reqspecprint.php" => array("mgt_view_req",),														
						     "$req_admin_url/reqspecview.php" => array("mgt_view_req",),														
						     "$req_admin_url/reqtcassign.php" => array("mgt_modify_req",));


$reports=array("$reports_url/resultsallbuilds.php" => array("testplan_metrics",),														
						   "$reports_url/resultsbugs.php" => array("testplan_metrics",),														
						   "$reports_url/resultsbuild.php" => array("testplan_metrics",),														
						   "$reports_url/resultsbystatus.php" => array("testplan_metrics",),														
						   "$reports_url/resultsgeneral.php" => array("testplan_metrics",),														
						   "$reports_url/resultsnavigator.php" => array("testplan_metrics",),														
						   "$reports_url/resultssend.php" => array("testplan_metrics",),														
						   "$reports_url/resultstc.php" => array("testplan_metrics",));														


$tc_admin=array("$tc_admin_url/containeredit.php" => array("mgt_modify_tc","mgt_view_tc",),														
   						  "$tc_admin_url/tcedit.php" => array("mgt_modify_tc","mgt_view_tc",),														
       					"$tc_admin_url/tcimport.php" => array("mgt_modify_tc","mgt_view_tc",),														
       					"$tc_admin_url/searchform.php" => null,
       					"$tc_admin_url/searchdata.php" => null,
       					"$tc_admin_url/archivedata.php" => null,
       					"$tc_admin_url/listtestcases.php" => null);


$print_data=array("$print_url/printdata.php" => null,													
						      "$print_url/selectdata.php" => null);



$cf_admin=array("$cf_admin_url/cfields_edit.php" => array("cfield_management",),
                "$cf_admin_url/cfields_view.php" => array("cfield_view",),
                "$cf_admin_url/cfields_tproject_assign.php" => array("cfield_management",));


// build rigth matrix
$g_userRights=$user_admin+$proj_admin+$test_exec+$kword_admin+$print_data+
              $build_admin+$tplan_admin+$req_admin+$reports+$tc_admin+$cf_admin;

?>