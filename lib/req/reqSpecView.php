<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecView.php,v $
 * @version $Revision: 1.10 $
 * @modified $Date: 2005/10/03 07:20:14 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Screen to view existing requirements within a req. specification.
 * 
 * @author Francisco Mancardi - fm - fckeditor
 * 20050930 - MHT - Database schema changed (author, modifier, status, etc.)
 *
 */
////////////////////////////////////////////////////////////////////////////////
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");


// init page 
tLog('POST: ' . implode(',',$_POST));
testlinkInitPage();

//echo "<pre>debug\$_REQUEST"; print_r($_REQUEST); echo "</pre>";

$sqlResult = null;
$action = null;
$sqlItem = 'Requirement';

$arrReq = array();
$bGetReqs = TRUE; // collect requirements as default

$template = 'reqSpecView.tpl'; // main template


$idSRS = isset($_REQUEST['idSRS']) ? strings_stripSlashes($_REQUEST['idSRS']) : null;
$idReq = isset($_REQUEST['idReq']) ? strings_stripSlashes($_REQUEST['idReq']) : null;
$title = isset($_REQUEST['title']) ? strings_stripSlashes($_REQUEST['title']) : null;
$scope = isset($_REQUEST['scope']) ? strings_stripSlashes($_REQUEST['scope']) : null;
$reqStatus = isset($_REQUEST['reqStatus']) ? strings_stripSlashes($_REQUEST['reqStatus']) : null;
$reqType = isset($_REQUEST['reqType']) ? strings_stripSlashes($_REQUEST['reqType']) : null;
$countReq = isset($_REQUEST['countReq']) ? strings_stripSlashes($_REQUEST['countReq']) : null;


// 20050906 - fm
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
$login_name = isset($_SESSION['user']) ? strings_stripSlashes($_SESSION['user']) : null;
$arrCov = null;


// 20050826 - fm
$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet=$g_fckeditor_toolbar;;


// create a new spec.
if(isset($_REQUEST['createReq']))
{
	if (isset($_REQUEST['title'])) {
		
		// 20050906 - fm
		$sqlResult = createRequirement($title, $scope, $idSRS, $userID, $reqStatus); // used default type=n
		$action = 'create';
		$scope='';
	}
	
	$template = 'reqCreate.tpl';
	$bGetReqs = FALSE;
} 

// edit REQ
elseif (isset($_REQUEST['editReq']))
{
	$idReq = strings_stripSlashes($_REQUEST['editReq']);
	$arrReq = getReqData($idReq);
	
	// 20050830 - MHT - Added audit
	$arrReq['author'] = getUserName($arrReq['id_author']);
	$arrReq['modifier'] = getUserName($arrReq['id_modifier']);
	$arrReq['coverage'] = getTc4Req($idReq);

  // 20050826
  $scope = $arrReq['scope']; 
  $action ='editReq';
	$template = 'reqEdit.tpl';
	$bGetReqs = FALSE;
}

// update REQ
elseif (isset($_REQUEST['updateReq']))
{
	$sqlResult = updateRequirement($idReq, $title, $scope, $userID, $reqStatus, $reqStatus);
	$action = 'update';
	$sqlItem = 'Requirement';
}

// delete REQ
elseif (isset($_REQUEST['deleteReq']))
{
	$sqlResult = deleteRequirement($idReq);
	$action = 'delete';
}

// edit spec.
elseif (isset($_REQUEST['editSRS']))
{
	$template = 'reqSpecEdit.tpl';
	$action="editSRS";
}

// update spec.
elseif (isset($_REQUEST['updateSRS']))
{
	// 20050906 - fm
	$sqlResult = updateReqSpec($idSRS,$title,$scope,$countReq,$userID);
	$action = 'update';
}

elseif (isset($_REQUEST['multiAction']))
{
	$arrIdReq = array_keys($_POST); // obtain names(id) of REQs
	array_pop($arrIdReq);	// remove multiAction value
	
	if (count($arrIdReq) != 0) {
		if ($_REQUEST['multiAction'] == lang_get('req_select_delete')) 
		{
			foreach ($arrIdReq as $idReq) {
				tLog("Delete requirement id=" . $idReq);
				$tmpResult = deleteRequirement($idReq);
				if ($tmpResult != 'ok') {
					$sqlResult .= $tmpResult . '<br />';
				}
			}
			if (empty($sqlResult)) {
				$sqlResult = 'ok';
			}
			$action = 'delete';
		} 
		elseif ($_REQUEST['multiAction'] == lang_get('req_select_create_tc')) 
		{
			// 20051002 - fm - interface changes
			// 20050906 - fm
			$sqlResult = createTcFromRequirement($arrIdReq, $prodID, $idSRS, $login_name);
			$action = 'create';
			$sqlItem = 'test case(s)';
		}
	} else {
			$sqlResult = lang_get('req_msg_noselect');
	}
}


// collect existing reqs for the SRS
if ($bGetReqs) {
	$arrReq = getRequirements($idSRS);
}
// collect existing document data
// $arrSpec = getReqSpec($idSRS);
// 20051001 - fm - bug
$arrSpec = getReqSpec($prodID,$idSRS);

//  - MHT - Added audit
$arrSpec[0]['author'] = getUserName($arrSpec[0]['id_author']);
$arrSpec[0]['modifier'] = getUserName($arrSpec[0]['id_modifier']);

// smarty
$smarty = new TLSmarty;
$smarty->assign('arrSpec', $arrSpec);
$smarty->assign('arrReq', $arrReq);
$smarty->assign('arrCov', $arrCov);
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('sqlItem', $sqlItem);
$smarty->assign('action', $action);
$smarty->assign('name',$title); // of updated item
$smarty->assign('selectReqStatus', $arrReqStatus);
$smarty->assign('modify_req_rights', has_rights("mgt_modify_req")); 

// 20050906 - fm
if(!is_null($scope))
{
	$of->Value=$scope;
}
else if ($action && $action != 'create')
{
	$of->Value=$arrSpec[0]['scope'];
}
else
{
	$of->Value="";
}


$smarty->assign('scope',$of->CreateHTML());
$smarty->display($template);
?>
