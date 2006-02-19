<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: userrightmatrix.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2006/02/19 13:03:32 $
 *
 * @author Andreas Morsing
 *
 * This page allows configuration of the accessrights needed for
 * executing pages
 * 
**/
//user right matrix, 
//for each file which calls testLinkInitPage it's 
//possible to set the rights needed to execute the script
//
//keys are the filenames (lowercase)
//values are the right(s) needed to execute it, 
//maybe array : multiple rights needed
//		string : exactly one right need
//		null : no rights need
$g_userRights = array(
						//USERADMINISTRATION
						"lib/usermanagement/usersnew.php"  => array (
															"mgt_users",
														),														
						"lib/usermanagement/usersedit.php"  => array (
															"mgt_users",
														),														
						"lib/usermanagement/usersview.php"  => array (
															"mgt_users",
														),														
						"lib/usermanagement/rolesview.php"  => array (
															"mgt_users",
														),														
						"lib/usermanagement/rolesedit.php"  => array (
															"mgt_users",
														),														
						"lib/usermanagement/usersassign.php"  => array (
															"mgt_users",
														),														
						//PRODUCTADMINISTRATION
						"lib/admin/adminproductedit.php" => array (
															"mgt_modify_product",
														),
						//TESTEXECUTION														
						"lib/execute/execnavigator.php" => array (
															"testplan_execute",
														),
						//KEYWORDS
						"lib/keywords/keywordsview.php" => array (
															"mgt_view_key",
														),
						//KEYWORDS
						"lib/keywords/keywordsassign.php" => array (
															"mgt_modify_key",
														),
					
						//BUILD
						"lib/plan/buildnew.php" => array (
															"testplan_create_build",
														),
						
						//TESTPLAN
						"lib/plan/planupdatetc.php" => array (
															"testplan_planning",
														),
						"lib/plan/planaddtc.php" => array (
															"testplan_planning",
														),														
						"lib/plan/planaddtcnavigator.php" => array (
															"testplan_planning",
														),														
						"lib/plan/planedit.php" => array (
															"testplan_planning",
														),														
						"lib/plan/planmilestones.php" => array (
															"testplan_planning",
														),														
						"lib/plan/plannew.php" => array (
															"testplan_planning",
														),														
						"lib/plan/planowner.php" => array (
															"testplan_planning",
														),														
						"lib/plan/planpriority.php" => array (
															"testplan_planning",
														),														
						"lib/plan/plantestersedit.php" => array (
															"testplan_planning",
														),														
						"lib/plan/plantestersnavigator.php" => array (
															"testplan_planning",
														),														
						"lib/plan/planupdatetc.php" => array (
															"testplan_planning",
														),														
						"lib/plan/planmilestoneedit.php" => array (
															"testplan_planning",
														),														
						"lib/plan/testsetnavigator.php" => array (
															"testplan_planning",
														),														
						"lib/plan/testsetremove.php" => array (
															"testplan_planning",
															),														
						//REQUIREMENTS
						"lib/req/reqspeclist.php" => array (
															"mgt_view_req",
															),														
						"lib/req/reqspecanalyse.php" => array (
															"mgt_view_req",
															),														
						"lib/req/reqspecprint.php" => array (
															"mgt_view_req",
															),														
						"lib/req/reqspecview.php" => array (
															"mgt_view_req",
															),														
						"lib/req/reqtcassign.php" => array (
															"mgt_modify_req",
															),														
						//REPORTS
						"lib/results/resultsallbuilds.php" => array (
																"testplan_metrics",
															),														
						"lib/results/resultsbugs.php" => array (
																"testplan_metrics",
															),														
						"lib/results/resultsbuild.php" => array (
																"testplan_metrics",
															),														
						"lib/results/resultsbystatus.php" => array (
																"testplan_metrics",
															),														
						"lib/results/resultsgeneral.php" => array (
																"testplan_metrics",
															),														
						"lib/results/resultsnavigator.php" => array (
																"testplan_metrics",
															),														
						"lib/results/resultssend.php" => array (
																"testplan_metrics",
															),														
						"lib/results/resultstc.php" => array (
																"testplan_metrics",
															),														
						//TESTCASES						
						"lib/testcases/containeredit.php" => array (
																"mgt_modify_tc",
																"mgt_view_tc",
															),														
						"lib/testcases/tcedit.php" => array (
																"mgt_modify_tc",
																"mgt_view_tc",
															),														
						"lib/testcases/tcimport.php" => array (
																"mgt_modify_tc",
																"mgt_view_tc",
															),														
						"lib/testcases/searchform.php" => null,
						"lib/testcases/searchdata.php" => null,
						"lib/testcases/archivedata.php" => null,
						"lib/testcases/listtestcases.php" => null,													
						
						//PRINT
						"lib/print/printdata.php" => null,													
						"lib/print/selectdata.php" => null,													
					)
?>