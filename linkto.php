<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Direct links for external access to testlink items with frames for navigation and tree.
 *
 * IMPORTANT - LIMITATIONS:
 * User has to login before clicking the link!
 * If user is not logged in he is redirected to login page. 
 * After login main page is shown, Clicking the link again then it works!
 *
 * How this feature works:
 * 
 * - direct link to testcase KAOS-4 in test project KAOS:
 * http://<testlink_home>/linkto.php?tprojectPrefix=KAOS&item=testcase&id=KAOS-4
 * 
 * - direct link to requirement REQ-002 in test project KAOS:
 * http://<testlink_home>/linkto.php?tprojectPrefix=KAOS&item=req&id=REQ-002
 *
 * - direct link to requirement specification REQ-SPEC-AK89 in test project KAOS:
 * http://<testlink_home>/linkto.php?tprojectPrefix=KAOS&item=reqspec&id=REQ-SPEC-AK89
 * 
 * Anchors:
 * If anchors are set (in scope, etc.) in the linked document, you can specify these
 * by using &anchor=anchorname, e.g.
 * http://<testlink_home>/linkto.php?tprojectPrefix=KAOS&item=testcase&id=KAOS-4&anchor=importantpart
 * 
 * Specials:
 * - tree for requirement specification or test specification are expanded to the level of the item you created the link to
 * - if a user has no right to view item he is redirected to main page
 * - if item does not exist an errormessage shows
 * 
 * @package 	TestLink
 * @author 		asimon
 * @copyright 	2007-2011, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *  20110601 - asimon - add a new (valid) direct link to output if a nonexistent version of a valid req is requested
 *  20110530 - asimon - BUGID 4298: added functionality to open specific requirement versions
 *  20100223 - asimon - added anchor functionality
 *  20091215 - asimon - refactored process_req() with new method in requirement_mgr class
 *	20091215 - franciscom - refactored
 *	20091214 - asimon83 - refactoring like requested in issue comments
 */

// use output buffer to prevent headers/data from being sent before 
// cookies are set, else it will fail
ob_start();

// some session and settings stuff from original index.php 
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
require_once('attachments.inc.php');
require_once('requirements.inc.php');
require_once('testcase.class.php');
require_once('testproject.class.php');
require_once('users.inc.php');
testlinkInitPage($db, true);

$smarty = new TLSmarty();
$smarty->assign('title', lang_get('main_page_title'));

// display outer or inner frame?
if (!isset($_GET['load'])) {
	// display outer frame, pass parameters to next script call for inner frame
	$item = isset($_GET['item']) ? "item=" . $_GET['item'] : '';
	$id = isset($_GET['id']) ? "&id=" . $_GET['id'] : '';
	$tprojectPrefix = isset($_GET['tprojectPrefix']) ? "&tprojectPrefix=" . $_GET['tprojectPrefix'] : '';
	$anchor = isset($_GET['anchor']) ? '&anchor=' . $_GET['anchor'] : "";
	$version = isset($_GET['version']) ? "&version=" . $_GET['version'] : '';
	$smarty->assign('titleframe', 'lib/general/navBar.php');
	$smarty->assign('mainframe', 'linkto.php?' . $item . $id . $version . $tprojectPrefix . '&load' . $anchor);
	$smarty->display('main.tpl');
} else {
	// inner frame, parameters passed

	// figure out what to display 
	//
	// key: item, value: url to tree management page
	$itemCode = array('req' => 'lib/requirements/reqSpecListTree.php', 
					  'reqspec' => 'lib/requirements/reqSpecListTree.php',
					  'testcase' => 'lib/testcases/listTestCases.php?feature=edit_tc' );

	$op = array('status_ok' => true, 'msg' => '');

	// First check for keys in _GET that MUST EXIST
	// key: key on _GET, value: labelID defined on strings.txt
	$mustKeys = array('tprojectPrefix' => 'testproject_not_set',
					  'item' => 'item_not_set', 'id' => 'id_not_set');

	foreach($mustKeys as $key => $labelID)
	{
		$op['status_ok'] = isset($_GET[$key]);
		if( !$op['status_ok'])
		{
			$op['msg'] = lang_get($labelID);
			break;
		}
	} 

	$anchor = isset($_GET['anchor']) ? $_GET['anchor'] : null;
	$version = isset($_GET['version']) ? $_GET['version'] : null;
	$tprojectPrefix = isset($_GET['tprojectPrefix']) ? $_GET['tprojectPrefix'] : null;
	$item = isset($_GET['item']) ? $_GET['item'] : null;
	$id = isset($_GET['id']) ? $_GET['id'] : null;

	if($op['status_ok'])
	{
		$tproject = new testproject($db);
		$tproject_data = $tproject->get_by_prefix($tprojectPrefix);
		if(($op['status_ok'] = !is_null($tproject_data))) 
		{
			$tproject->setSessionProject($tproject_data['id']);
			$op['status_ok'] = isset($itemCode[$item]);
			$op['msg'] = sprintf(lang_get('invalid_item'),$item);
		}
		else 
		{
			$op['msg'] = sprintf(lang_get('testproject_not_found'),$tprojectPrefix);
		}
	} 


	if($op['status_ok'])
	{
		// Build  name of function to call for doing the job.
		$pfn = 'process_' . $item;
		$jump_to = $pfn($db, $id, $tproject_data['id'], $tprojectPrefix, $version);

		$op['status_ok'] = !is_null($jump_to['url']);
		$op['msg'] = $jump_to['msg'];
	}

	if($op['status_ok'])
	{
		// add anchor to URL
		$url = $jump_to['url'] . $anchor;

		$smarty->assign('treewidth', TL_FRMWORKAREA_LEFT_FRAME_WIDTH);
		$smarty->assign('workframe', $url);
		$smarty->assign('treeframe', $itemCode[$item]);

		$smarty->display('frmInner.tpl');
	}
	else
	{
		echo $op['msg'];
		ob_end_flush();
		exit();
	}
}
ob_end_flush();



/**
 * process_testcase
 *
 */
function process_testcase(&$dbHandler,$externalID, $tprojectID, $tprojectPrefix, $version)
{
	$ret = array();
	$ret['url'] = null;
	$ret['msg'] = sprintf(lang_get('testcase_not_found'), $externalID, $tprojectPrefix);

	$tcase_mgr = new testcase($dbHandler);
	$tcaseID = $tcase_mgr->getInternalID($externalID);
	if($tcaseID > 0)
	{
		$ret['url'] = "lib/testcases/archiveData.php?edit=testcase&id={$tcaseID}";
		$cookie = buildCookie($dbHandler,$tcaseID,$tprojectID,'ys-tproject_');
		setcookie($cookie['value'], $cookie['path'], TL_COOKIE_KEEPTIME, '/');
	}
	return $ret;
}


/**
 * process_req
 *
 * @internal revisions:
 * 20110601 - asimon - add a new (valid) direct link to output if a nonexistent version of a valid req is requested
 * 20110530 - asimon - BUGID 4298: refactored this function to be able to operate with specific versions
 */
function process_req(&$dbHandler, $docID, $tprojectID, $tprojectPrefix, $version)
{
	$ret = array('url' => null, 'msg' => null);

	// First step: get this requirement's database ID by its Doc-ID (only if this Doc-ID exists).
	$req_mgr = new requirement_mgr($dbHandler);
	$req = $req_mgr->getByDocID($docID, $tprojectID);
	$req = is_null($req) ? null : current($req);
	$req_id = is_null($req) ? null : $req['id'];
	$version_id = null;

	if (is_null($req_id)) {
		$ret['msg'] = sprintf(lang_get('req_not_found'), $docID, $tprojectPrefix);
	}

	// Second step: If the requirement exists and a version was given, we have to check here if this specific version exists, too.
	if(!is_null($req_id) && !is_null($version) && is_numeric($version)) {
		$req = $req_mgr->get_by_id($req_id, null, $version);
		$req = is_null($req) ? null : current($req);

		// does this requirement really have the correct version number?
		$version_id = !is_null($req) && ($req['version'] == $version) ? $req['version_id'] : null;

		if (is_null($version_id)) {
			// add direct link to current version to output
			$req_url = $_SESSION['basehref'] . 'linkto.php?load&tprojectPrefix=' .
			           urlencode($tprojectPrefix) . '&item=req&id=' . urlencode($docID);
			$ret['msg'] = sprintf(lang_get('req_version_not_found'), $version, $docID, $tprojectPrefix);
			$ret['msg'] .= sprintf(" <a href=\"$req_url\">%s</a>", lang_get('direct_link_on_wrong_version'));
			$req_id = null;
		}
	}

	// Third and last step: set cookie and build the link (only if the requested item really was found).
	if(!is_null($req_id)) {
		if (!is_null($version_id)) {
			// link to open in requirement frame must include version
			$ret['url'] = "lib/requirements/reqView.php?item=requirement&requirement_id=$req_id&req_version_id=$version_id";
		} else {
			// link to open in requirement frame does not include a version, it was not requested
			$ret['url'] = "lib/requirements/reqView.php?item=requirement&requirement_id=$req_id";
		}

		$cookie = buildCookie($dbHandler, $req_id, $tprojectID,'ys-requirement_spec');
		setcookie($cookie['value'], $cookie['path'], TL_COOKIE_KEEPTIME, '/');
	}

	return $ret;
}



/**
 * process_reqspec
 *
 */
function process_reqspec(&$dbHandler, $docID, $tprojectID, $tprojectPrefix, $version)
{
	$ret = array();
	$ret['url'] = null;
	$ret['msg'] = sprintf(lang_get('req_spec_not_found'), $docID,$tprojectPrefix);

	$reqspec_mgr = new requirement_spec_mgr($dbHandler);
	$reqSpec = $reqspec_mgr->getByDocID($docID,$tprojectID);

	if( !is_null($reqSpec) )
	{
		$reqSpec = current($reqSpec);
		$id = $reqSpec['id'];
		$ret['url'] = "lib/requirements/reqSpecView.php?req_spec_id={$id}";

		$cookie = buildCookie($dbHandler,$id,$tprojectID,'ys-requirement_spec');
		setcookie($cookie['value'], $cookie['path'], TL_COOKIE_KEEPTIME, '/');
	}
	return $ret;
}



/**
 * 
 *
 */
function buildCookie(&$dbHandler,$itemID,$tprojectID,$cookiePrefix)
{
	$tree_mgr = new tree($dbHandler);
	$path = $tree_mgr->get_path($itemID);
	$parents = array();
	$parents[] = $tprojectID;
	foreach($path as $node) {
		$parents[] = $node['id'];
	}
	array_pop($parents);
	$cookieInfo['path'] = 'a:s%3A/' . implode("/", $parents);
	$cookieInfo['value'] = $cookiePrefix . $tprojectID . '_ext-comp-1001' ;
	return $cookieInfo;
}
?>