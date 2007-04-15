<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecView.php,v $
 * @version $Revision: 1.34 $
 * @modified $Date: 2007/04/15 10:59:44 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Screen to view existing requirements within a req. specification.
 * 
 * 20070415 - franciscom - added reorder feature
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once('attachments.inc.php');
require_once("../functions/csv.inc.php");
require_once("../functions/xml.inc.php");

require_once("../../third_party/fckeditor/fckeditor.php");
require_once(dirname("__FILE__") . "/../functions/configCheck.php");
testlinkInitPage($db);

$user_feedback='';
$js_msg = null;
$sqlResult = null;
$action = null;
$sqlItem = 'SRS';
$arrReq = array();
$bGetReqs = TRUE; // collect requirements as default
$template = 'reqSpecView.tpl';

$_REQUEST = strings_stripSlashes($_REQUEST);
$reqDocId = isset($_REQUEST['reqDocId']) ? trim($_REQUEST['reqDocId']) : null;
$title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : null;

$idSRS = isset($_REQUEST['idSRS']) ? $_REQUEST['idSRS'] : null;
$idReq = isset($_REQUEST['idReq']) ? $_REQUEST['idReq'] : null;
$scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
$reqStatus = isset($_REQUEST['reqStatus']) ? $_REQUEST['reqStatus'] : null;
$reqType = isset($_REQUEST['reqType']) ? $_REQUEST['reqType'] : null;
$countReq = isset($_REQUEST['countReq']) ? intval($_REQUEST['countReq']) : 0;
$bCreate = isset($_REQUEST['create']) ? intval($_REQUEST['create']) : 0;

$tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
$login_name = isset($_SESSION['user']) ? $_SESSION['user'] : null;

$do_export = isset($_REQUEST['exportAll']) ? 1 : 0;
$exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;

$do_create_tc_from_req = isset($_REQUEST['create_tc_from_req']) ? 1 : 0;
$do_delete_req = isset($_REQUEST['req_select_delete']) ? 1 : 0;

$reorder = isset($_REQUEST['req_reorder']) ? 1 : 0;
$do_req_reorder = isset($_REQUEST['do_req_reorder']) ? 1 : 0;

$arrCov = null;

$tproject = new testproject($db);
$smarty = new TLSmarty();

$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = $g_fckeditor_toolbar;;

$attach['status_ok']=true;
$attach['msg']='';
 
// create a new spec.
if(isset($_REQUEST['createReq']))
{
	if ($bCreate)
	{
	  
		$status = createRequirement($db,$reqDocId,$title, $scope, $idSRS, $userID, 
			                                 $reqStatus, $reqType);
		$user_feedback = $status['msg'];	                                 
		if( $user_feedback == 'ok' )
		{
		  $user_feedback=sprintf(lang_get('req_created'), $reqDocId);  
		}
	}
	$scope = '';
	$template = 'reqCreate.tpl';
	$bGetReqs = FALSE;
} 
elseif (isset($_REQUEST['editReq']))
{
  $srs = get_srs_by_id($db,$idSRS);
	$smarty->assign('srs_title',$srs[$idSRS]['title']);	

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

  	
  $repository['type']=config_get('repositoryType');
  $repository['path']=config_get('repositoryPath');
  if( $repository['type'] == TL_REPOSITORY_TYPE_FS )
  {
    $attach = checkForRepositoryDir($repository['path']);
  }
  // -----------------------------------------------------------
	$bGetReqs = FALSE;
}
elseif (isset($_REQUEST['updateReq']))
{
	$sqlResult = updateRequirement($db,$idReq,trim($reqDocId),$title, 
	                               $scope, $userID, $reqStatus, $reqType);
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
	$action = 'do_update';
}
elseif ($do_create_tc_from_req || $do_delete_req )
{
	$arrIdReq = isset($_POST['req_id_cbox']) ? $_POST['req_id_cbox'] : null;
	
	if (count($arrIdReq) != 0) {
		if($do_delete_req) 
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
		elseif ($do_create_tc_from_req) 
		{
			  $sqlResult = createTcFromRequirement($db,$tproject,$arrIdReq,$tprojectID, $idSRS, $userID);
			  $action = 'do_add';
			  $sqlItem = 'testcases';
		}
	} 
	else 
	{
	    if($do_create_tc_from_req)
	    {
		  	$js_msg = lang_get('cant_create_tc_from_req_nothing_sel');
	    }
	    if($do_delete_req)
	    {
	  		$js_msg = lang_get('cant_delete_req_nothing_sel');
	    }
	}
}
elseif( $reorder )
{
  $bGetReqs=TRUE;
  $template = 'req_spec_order.tpl';
}
elseif( $do_req_reorder )
{
  $nodes_order = isset($_REQUEST['nodes_order']) ? $_REQUEST['nodes_order'] : null;
  $nodes_in_order=transform_nodes_order($nodes_order);
  
  set_req_order($db,$idSRS,$nodes_in_order);

}


// collect existing reqs for the SRS
if ($bGetReqs)
	$arrReq = getRequirements($db,$idSRS);


// collect existing document data
$arrSpec = $tproject->getReqSpec($tprojectID,$idSRS);
$arrSpec[0]['author'] = getUserName($db,$arrSpec[0]['author_id']);
$arrSpec[0]['modifier'] = getUserName($db,$arrSpec[0]['modifier_id']);
$srs_title = $arrSpec[0]['title'];


$smarty->assign('idSRS', $idSRS);
$smarty->assign('user_feedback', $user_feedback);
$smarty->assign('srs_title', $srs_title);
$smarty->assign('attach', $attach);
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

// 20061008 - franciscom
//export to csv doors is not support
global $g_reqImportTypes;
$exportTypes = $g_reqImportTypes;
unset($exportTypes['csv_doors']);

if($do_export)
{
	$reqData = getRequirements($db,$idSRS);
	$pfn = null;
	switch(strtoupper($exportType))
	{
		case 'CSV':
			$pfn = "exportReqDataToCSV";
			$fileName = 'reqs.csv';
			break;
		case 'XML':
			$pfn = "exportReqDataToXML";
			$fileName = 'reqs.xml';
			break;
	}
	if ($pfn)
	{
		$content = $pfn($reqData);
		downloadContentsToFile($content,$fileName);
		
		// why this exit() ?
		// If we don't use it, we will find in the exported file
		// the contents of the smarty template.
		exit();
	}
}
// ----------------------------------------------------------

$smarty->assign('js_msg',$js_msg);
$smarty->assign('exportTypes',$exportTypes);
$smarty->assign('scope',$of->CreateHTML());
$smarty->display($template);
?>

<?php
// nodes_order format:  NODE_ID-?,NODE_ID-?
// 2-0,10-0,3-0
//                      
function transform_nodes_order($nodes_order)
{
  $fa=explode(',',$nodes_order);
  
  foreach($fa as $key => $value)
  {
    // $value= X-Y
    $fb=explode('-',$value);
    $nodes_id[]=$fb[0];
  }
  
  return $nodes_id;
}	
?>