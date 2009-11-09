<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: gettestcasesummary.php,v 1.1 2009/11/09 07:22:15 franciscom Exp $
* 	@author 	Francisco Mancardi
* 
*   Used on Add/Remove test case to test plan feature, to display summary via ExtJS tooltip
*
*	@internal Revisions:
*	20091109 - franciscom - BUGID  0002937: add/remove test case hover over test case 
*                                           tooltip replacement with summary 
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$tcase_mgr = new testcase($db);
$tcase_id = isset($_REQUEST['tcase_id']) ? $_REQUEST['tcase_id']: null;
$tcversion_id = isset($_REQUEST['tcversion_id']) ? $_REQUEST['tcversion_id']: 0;
$info = '';
if( !is_null($tcase_id) )
{
	if($tcversion_id > 0 )
	{ 
		$tcase = $tcase_mgr->get_by_id($tcase_id,$tcversion_id);
		if(!is_null($tcase))
		{
			$tcase = $tcase[0];
		} 
	}
	else
	{
		$tcase = $tcase_mgr->get_last_version_info($tcase_id);
	}	
    $info = $tcase['summary'];
}
echo $info;