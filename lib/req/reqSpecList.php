<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecList.php,v $
 * @version $Revision: 1.17 $
 * @modified $Date: 2007/11/09 21:48:53 $
 * 
 * @author Martin Havlat
 * 
 * View existing and create a new req. specification.
 * 
 * rev : 20071106 - franciscom - custom field management
 * 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('requirement_spec_mgr.class.php');

require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db);

// echo "<pre>debug 20071106 - \ - " . __FUNCTION__ . " --- "; print_r($_REQUEST); echo "</pre>";

// reqSpecList.php?createForm

$sqlResult = null;
$action = null;
$template = 'reqSpecList.tpl';

$title = null;
$scope = null;

$_POST = strings_stripSlashes($_POST);
$title = isset($_POST['title']) ? $_POST['title'] : null;
$scope = isset($_POST['scope']) ? $_POST['scope'] : null;
$countReq = isset($_POST['countReq']) ? $_POST['countReq'] : null;
$idSRS = isset($_GET['idSRS']) ? intval($_GET['idSRS']) : null;
$bCreate = isset($_POST['createSRS']);
$bDelete = isset($_GET['deleteSRS']);
$bCreateForm = isset($_GET['createForm']);

$tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : "";
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;

//$tproject = new testproject($db);
$req_spec_mgr = new requirement_spec_mgr($db);
$smarty = new TLSmarty();

if($bCreate)
{
  // Create Requiremet Specification on DB
	$ret = $req_spec_mgr->create($tprojectID,$title,$scope,$countReq,$userID);
	
	$sqlResult=$ret['msg'];
	if( $ret['status_ok'])
	{
    $cf_map = $req_spec_mgr->get_linked_cfields(null,$tprojectID) ;
    // $req_spec_mgr->cfield_mgr->design_values_to_db($_REQUEST,$ret['id'],$cf_map);
    $req_spec_mgr->values_to_db($_REQUEST,$ret['id'],$cf_map);
	}
	$action = 'do_add';


} 
else if($bDelete)
{
	
	// $sqlResult = deleteReqSpec($db,$idSRS);
	$sqlResult = 'ok';
	$req_spec_mgr->delete($idSRS);
	$action = 'do_delete';
} 
else if($bCreateForm)
{
  // Show create form
	$template = 'reqSpecCreate.tpl';
	
	// get custom fields
	$cf_smarty = $req_spec_mgr->html_table_of_custom_field_inputs(null,$tprojectID);
  $smarty->assign('cf', $cf_smarty);
} 

// $arrSpec = $tproject->getReqSpec($tprojectID);
$arrSpec = $req_spec_mgr->get_all_in_testproject($tprojectID);


$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet=$g_fckeditor_toolbar;;

$of->Value="";
if($scope)
	$of->Value = $scope;
else if ($action && ($action != 'do_add'))
	$of->Value = $arrSpec[0]['scope'];

$smarty->assign('arrSpec', $arrSpec);
$smarty->assign('arrSpecCount', count($arrSpec));
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('action', $action);
$smarty->assign('name',$title);
$smarty->assign('productName', $tprojectName);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->assign('scope',$of->CreateHTML());
$smarty->display($template);
?>
