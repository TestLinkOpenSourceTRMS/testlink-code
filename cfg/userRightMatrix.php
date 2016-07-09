<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	userRightMatrix.php
 * @author Andreas Morsing
 *
 * Configuration of the access rights needed for executing pages
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
$user_admin=array("$user_admin_url/usersassign.php"  => array ("user_role_assignment",));														
$proj_admin=array("$proj_admin_url/projectEdit.php" => array("mgt_modify_product",));
$test_exec=array("$test_exec_url/execnavigator.php" => array("testplan_execute",));


$tplan_admin= array("$tplan_admin_url/planupdatetc.php" => array("testplan_planning",),
					"$tplan_admin_url/planaddtc.php" => array("testplan_planning",),														
				    "$tplan_admin_url/planaddtcnavigator.php" => array("testplan_planning",),														
				    "$tplan_admin_url/planedit.php" => array("testplan_planning",),														
       				"$tplan_admin_url/plannew.php" => array("testplan_planning",),														
       				"$tplan_admin_url/planpriority.php" => array("testplan_planning",),														
       				"$tplan_admin_url/planupdatetc.php" => array("testplan_planning",),														
       				"$tplan_admin_url/planmilestoneedit.php" => array("testplan_planning",),														
       				"$tplan_admin_url/plantcnavigator.php" => array("testplan_planning",),														
       				"$tplan_admin_url/plantcremove.php" => array("testplan_planning",));														

$reports=array(	"$reports_url/resultsallbuilds.php" => array("testplan_metrics",),														
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
       			"$tc_admin_url/listtestcases.php" => null);


$print_data=array("$print_url/printdata.php" => null,													
						      "$print_url/selectdata.php" => null);



$cf_admin=array("$cf_admin_url/cfieldsEdit.php" => array("cfield_management",),
                "$cf_admin_url/cfieldsView.php" => array("cfield_view",),
                "$cf_admin_url/cfieldsTProjectAssign.php" => array("cfield_management",));


// build rigth matrix
$g_userRights=$user_admin+$proj_admin+$test_exec+$print_data+
              $tplan_admin+$reports+$tc_admin+$cf_admin;

?>