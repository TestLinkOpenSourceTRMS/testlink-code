<?php
//user right matrix, 
//for each file which calls testLinkInitPage it's 
//possible to set the rights needed to execute the script
//
//keys are the filenames 
//values are the right(s) needed to execute it, maybe array or string
$g_userRights = array(
						"adminusersdelete.php" => array (
															"mgt_users",
														),
					)
?>