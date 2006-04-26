<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @version $Id: archiveData.php,v 1.11 2006/04/26 07:07:56 franciscom Exp $
 * @author Martin Havlat
 *  
 * This page allows you to show data (test cases, categories, and
 * components. This is refered by tree.
 * 
 * 20060225 - franciscom - using testproject class
 * 20050830 - MHT - formal update
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('archive.inc.php');

// 20060225 - franciscom
require_once('testproject.class.php'); 
require_once('testsuite.class.php'); 
require_once('testcase.class.php'); 


testlinkInitPage($db);


$user_id=isset($_SESSION['userID']) ? $_GET['userID'] : 0;

$feature = isset($_GET['edit']) ? $_GET['edit'] : null;
$id = isset($_GET['data']) ? intval($_GET['data']) : null;
$allow_edit = isset($_GET['allow_edit']) ? intval($_GET['allow_edit']) : 1;

// 20060425 - franciscom
$smarty = new TLSmarty();

// load data and show template
switch($feature)
{
	case 'testproject':
	$item_mgr = New testproject($db);
  //$item_mgr->show($id,$user_id);
	break;
		
 	case 'testsuite':
	$item_mgr = New testsuite($db);
	//$item_mgr->show($id,$user_id);
	break;
		
	
	case 'testcase':
	$item_mgr = New testcase($db);
  //$item_mgr->show($id,$user_id);
	break;

	//case 'testcase_version':
  //showTestcase($db,$id,$allow_edit);	
  //break;


	default:
		tLog('$_GET["edit"] has invalid value: ' . $feature , 'ERROR');
		trigger_error($_SESSION['user'].'> $_GET["edit"] has invalid value.', E_USER_ERROR);
}
// 20060425 - franciscom
$item_mgr->show($smarty,$id,$user_id);

?>
