<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @version $Id: archiveData.php,v 1.7 2006/02/27 07:55:45 franciscom Exp $
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

testlinkInitPage($db);

//echo "<pre>debug"; print_r($_SESSION); echo "</pre>";
//exit();

$feature = isset($_GET['edit']) ? $_GET['edit'] : null;
$id = isset($_GET['data']) ? intval($_GET['data']) : null;
$allow_edit = isset($_GET['allow_edit']) ? intval($_GET['allow_edit']) : 1;

echo "<pre>debug" . __FILE__; print_r($_GET); echo "</pre>";

// load data and show template
switch($feature)
{
	case 'testproject':
	$item_mgr = New testproject($db);
	break;
		
 	case 'testsuite':
	$item_mgr = New testsuite($db);
	//$item_mgr->show($id);
	break;
		
	
	case 'testcase':
  	showTestcase($db,$id,$allow_edit);	
		break;

	case 'testcase_version':
  	showTestcase($db,$id,$allow_edit);	
		break;


	default:
		tLog('$_GET["edit"] has invalid value: ' . $feature , 'ERROR');
		trigger_error($_SESSION['user'].'> $_GET["edit"] has invalid value.', E_USER_ERROR);
}

$item_mgr->show($id);

?>
