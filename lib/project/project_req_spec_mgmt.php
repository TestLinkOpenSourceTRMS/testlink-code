<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @version $Id: project_req_spec_mgmt.php,v 1.2 2008/01/06 20:33:54 schlundus Exp $
 * @author Martin Havlat
 *  
 * Allows you to show test suites, test cases.
 * Normally launched from tree navigator.
 *
 * rev :
 *      20070930 - franciscom - REQ - BUGID 1078
 * 
 */
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'undefined';

// load data and show template
$smarty = new TLSmarty();
$smarty->assign('name', $tproject_name);
$smarty->display('requirements/project_req_spec_mgmt.tpl');
?>
