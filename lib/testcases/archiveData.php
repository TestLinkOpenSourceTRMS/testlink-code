<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @version $Id: archiveData.php,v 1.14 2006/05/22 15:45:02 franciscom Exp $
 * @author Martin Havlat
 *  
 * This page allows you to show data (test cases, categories, and
 * components. This is refered by tree.
 * 
 * 20060225 - franciscom - using testproject class
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('archive.inc.php');
testlinkInitPage($db);

// 20060518 - franciscom
$user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;

$feature = isset($_GET['edit']) ? $_GET['edit'] : null;
$id = isset($_GET['data']) ? intval($_GET['data']) : null;
$allow_edit = isset($_GET['allow_edit']) ? intval($_GET['allow_edit']) : 1;

// load data and show template
$smarty = new TLSmarty();

switch($feature)
{
	case 'testproject':
		$item_mgr = new testproject($db);
    $item_mgr->show($smarty,$id);
		break;
	case 'testsuite':
		$item_mgr = new testsuite($db);
    $item_mgr->show($smarty,$id);
		break;
	case 'testcase':
		$item_mgr = new testcase($db);
		$item_mgr->show($smarty,$id,$user_id);
		break;
	default:
		tLog('$_GET["edit"] has invalid value: ' . $feature , 'ERROR');
		trigger_error($_SESSION['user'].'> $_GET["edit"] has invalid value.', E_USER_ERROR);
}


?>
