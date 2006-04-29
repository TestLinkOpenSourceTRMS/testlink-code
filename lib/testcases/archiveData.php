<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @version $Id: archiveData.php,v 1.12 2006/04/29 19:32:54 schlundus Exp $
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

$user_id = isset($_SESSION['userID']) ? $_GET['userID'] : 0;

$feature = isset($_GET['edit']) ? $_GET['edit'] : null;
$id = isset($_GET['data']) ? intval($_GET['data']) : null;
$allow_edit = isset($_GET['allow_edit']) ? intval($_GET['allow_edit']) : 1;

// load data and show template
switch($feature)
{
	case 'testproject':
		$item_mgr = new testproject($db);
		break;
	case 'testsuite':
		$item_mgr = new testsuite($db);
		break;
	case 'testcase':
		$item_mgr = new testcase($db);
		break;
	default:
		tLog('$_GET["edit"] has invalid value: ' . $feature , 'ERROR');
		trigger_error($_SESSION['user'].'> $_GET["edit"] has invalid value.', E_USER_ERROR);
}
$smarty = new TLSmarty();
$item_mgr->show($smarty,$id,$user_id);
?>
