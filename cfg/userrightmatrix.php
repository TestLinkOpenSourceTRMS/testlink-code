<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: userrightmatrix.php,v $
 *
 * @version $Revision: 1.8 $
 * @modified $Date: 2006/01/14 17:47:54 $
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
						"lib/admin/adminusernew.php"  => array (
															"mgt_users",
														),														
						"lib/admin/adminuseredit.php"  => array (
															"mgt_users",
														),														
						"lib/admin/adminusers.php"  => array (
															"mgt_users",
														),														
						//PRODUCTADMINISTRATION
						"lib/admin/adminproductedit.php" => array (
															"mgt_modify_product",
														),
						//TESTEXECUTION														
						"lib/execute/execnavigator.php" => array (
															"tp_execute",
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
															"tp_create_build",
														),
						
						//TESTPLAN
						"lib/plan/planupdatetc.php" => array (
															"tp_planning",
														),
						"lib/plan/planaddtc.php" => array (
															"tp_planning",
														),														
						"lib/plan/planaddtcnavigator.php" => array (
															"tp_planning",
														),														
						"lib/plan/planedit.php" => array (
															"tp_planning",
														),														
						"lib/plan/planmilestones.php" => array (
															"tp_planning",
														),														
						"lib/plan/plannew.php" => array (
															"tp_planning",
														),														
						"lib/plan/planowner.php" => array (
															"tp_planning",
														),														
						"lib/plan/planpriority.php" => array (
															"tp_planning",
														),														
						"lib/plan/plantestersedit.php" => array (
															"tp_planning",
														),														
						"lib/plan/plantestersnavigator.php" => array (
															"tp_planning",
														),														
						"lib/plan/planupdatetc.php" => array (
															"tp_planning",
														),														
						"lib/plan/planmilestoneedit.php" => array (
															"tp_planning",
														),														
						"lib/plan/testsetnavigator.php" => array (
															"tp_planning",
														),														
						"lib/plan/testsetremove.php" => array (
															"tp_planning",
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
																"tp_metrics",
															),														
						"lib/results/resultsbugs.php" => array (
																"tp_metrics",
															),														
						"lib/results/resultsbuild.php" => array (
																"tp_metrics",
															),														
						"lib/results/resultsbystatus.php" => array (
																"tp_metrics",
															),														
						"lib/results/resultsgeneral.php" => array (
																"tp_metrics",
															),														
						"lib/results/resultsnavigator.php" => array (
																"tp_metrics",
															),														
						"lib/results/resultssend.php" => array (
																"tp_metrics",
															),														
						"lib/results/resultstc.php" => array (
																"tp_metrics",
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