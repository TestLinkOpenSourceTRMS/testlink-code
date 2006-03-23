<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecView.php,v $
 * @version $Revision: 1.21 $
 * @modified $Date: 2006/03/23 20:46:30 $ by $Author: schlundus $
 * @author Martin Havlat
 * 
 * Screen to view existing requirements within a req. specification.
 * 
 * 20050930 - MHT - Database schema changed (author, modifier, status, etc.)
 * 20060103 - scs - ADOdb changes
 * 20060110 - fm  - removed onchange event management
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once('attachments.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db);

$sqlResult = null;
$action = null;
$sqlItem = 'Requirement';
$arrReq = array();
$bGetReqs = TRUE; // collect requirements as default
$template = 'reqSpecView.tpl';

$_REQUEST = strings_stripSlashes($_REQUEST);
$reqDocId = isset($_REQUEST['reqDocId']) ? $_REQUEST['reqDocId'] : null;
$idSRS = isset($_REQUEST['idSRS']) ? $_REQUEST['idSRS'] : null;
$idReq = isset($_REQUEST['idReq']) ? $_REQUEST['idReq'] : null;
$title = isset($_REQUEST['title']) ? $_REQUEST['title'] : null;
$scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
$reqStatus = isset($_REQUEST['reqStatus']) ? $_REQUEST['reqStatus'] : null;
$reqType = isset($_REQUEST['reqType']) ? $_REQUEST['reqType'] : null;
$countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;

$tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
$login_name = isset($_SESSION['user']) ? $_SESSION['user'] : null;

$arrCov = null;

$tproject = new testproject($db);
$smarty = new TLSmarty();

$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet=$g_fckeditor_toolbar;;


// create a new spec.
if(isset($_REQUEST['createReq']))
{
	$sqlResult = createRequirement($db,$title, $scope, $idSRS, $userID, 
		                               $reqStatus, $reqType, $reqDocId);
	$action = 'create';
	$scope = '';
	$template = 'reqCreate.tpl';
	$bGetReqs = FALSE;
} 
elseif (isset($_REQUEST['editReq']))
{
	$idReq = intval($_REQUEST['editReq']);
	$arrReq = getReqData($db,$idReq);
	if ($arrReq)
	{
		$arrReq['author'] = getUserName($db,$arrReq['author_id']);
		$arrReq['modifier'] = getUserName($db,$arrReq['modifier_id']);
		$arrReq['coverage'] = getTc4Req($db,$idReq);
		
		$reqDocId = $arrReq['req_doc_id'];
		$scope = $arrReq['scope']; 
	}
	$action = 'editReq';
	$template = 'reqEdit.tpl';
	$smarty->assign('id',$idReq);	
	$smarty->assign('tableName','requirements');	
	$attachmentInfos = getAttachmentInfos($db,$idReq,'requirements');
	$smarty->assign('attachmentInfos',$attachmentInfos);	
	
	$bGetReqs = FALSE;
}
elseif (isset($_REQUEST['updateReq']))
{
	$sqlResult = updateRequirement($db,$idReq, $title, $scope, $userID, $reqStatus, $reqType, $reqDocId);
	$action = 'update';
	$sqlItem = 'Requirement';
}
elseif (isset($_REQUEST['deleteReq']))
{
	$sqlResult = deleteRequirement($db,$idReq);
	$action = 'delete';
}
elseif (isset($_REQUEST['editSRS']))
{
	$template = 'reqSpecEdit.tpl';
	$action = "editSRS";
}
elseif (isset($_REQUEST['updateSRS']))
{
	$sqlResult = updateReqSpec($db,$idSRS,$title,$scope,$countReq,$userID);
	$action = 'update';
}
elseif (isset($_REQUEST['create_tc_from_req']) || isset($_REQUEST['req_select_delete']) )
{
	$arrIdReq = isset($_POST['req_id_cbox']) ? $_POST['req_id_cbox'] : null;
	if (count($arrIdReq) != 0) {
		if (isset($_REQUEST['req_select_delete'])) 
		{
			foreach ($arrIdReq as $idReq) {
				tLog("Delete requirement id=" . $idReq);
				$tmpResult = deleteRequirement($db,$idReq);
				if ($tmpResult != 'ok') {
					$sqlResult .= $tmpResult . '<br />';
				}
			}
			if (empty($sqlResult)) {
				$sqlResult = 'ok';
			}
			$action = 'delete';
		} 
		elseif (isset($_REQUEST['create_tc_from_req'])) 
		{
			$sqlResult = createTcFromRequirement($db,$tproject,$arrIdReq,$tprojectID, $idSRS, $userID);
			$action = 'create';
			$sqlItem = 'test case(s)';
		}
	} else {
		  $sqlResult = lang_get('req_msg_noselect');
	}
}

// collect existing reqs for the SRS
if ($bGetReqs) {
	$arrReq = getRequirements($db,$idSRS);
}
// collect existing document data
$arrSpec = $tproject->getReqSpec($tprojectID,$idSRS);

$arrSpec[0]['author'] = getUserName($db,$arrSpec[0]['author_id']);
$arrSpec[0]['modifier'] = getUserName($db,$arrSpec[0]['modifier_id']);

$smarty->assign('arrSpec', $arrSpec);
$smarty->assign('arrReq', $arrReq);
$smarty->assign('arrCov', $arrCov);
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('sqlItem', $sqlItem);
$smarty->assign('action', $action);
$smarty->assign('name',$title);
$smarty->assign('selectReqStatus', $arrReqStatus);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 

$of->Value="";
if (!is_null($scope))
	$of->Value=$scope;
else if ($action && $action != 'create')
{
	$of->Value=$arrSpec[0]['scope'];
}

$smarty->assign('scope',$of->CreateHTML());
$smarty->display($template);
?>
