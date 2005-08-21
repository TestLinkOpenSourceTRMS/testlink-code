<?php
//user right matrix, 
//for each file which calls testLinkInitPage it's 
//possible to set the rights needed to execute the script
//
//keys are the filenames 
//values are the right(s) needed to execute it, maybe array or string
$g_userRights = array(
						//USERADMINISTRATION
						"lib/admin/adminusersdelete.php" => array (
															"mgt_users",
														),
						"lib/admin/adminusernew.php"  => array (
															"mgt_users",
														),														
						"lib/admin/adminusers.php"  => array (
															"mgt_users",
														),														
						//PRODUCTADMINISTRATION
						"lib/admin/adminproductedit.php" => array (
															"mgt_modify_product",
														),
						"lib/admin/adminproductnew.php" => array (
															"mgt_modify_product",
														),
						//TESTEXECUTION														
						"lib/execute/execnavigator.php" => array (
															"tp_execute",
														),
						"lib/execute/execsetresults.php" => array (
															"tp_execute",
														),
														
					)
?>