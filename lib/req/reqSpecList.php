<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecList.php,v $
 * @version $Revision: 1.13 $
 * @modified $Date: 2006/03/23 20:46:30 $
 * 
 * @author Martin Havlat
 * 
 * Screen to view existing and create a new req. specification.
 * 
 * @author Francisco Mancardi - 20050906 - reduce global coupling, fckeditor
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db);

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

$tproject = new testproject($db);

if($bCreate)
{
	$sqlResult = $tproject->createReqSpec($tprojectID,$title,$scope,$countReq,$userID);
	$action = 'create';
} 
else if($bDelete)
{
	$sqlResult = deleteReqSpec($db,$idSRS);
	$action = 'delete';
} 
else if($bCreateForm)
{
	$template = 'reqSpecCreate.tpl';
} 

$arrSpec = $tproject->getReqSpec($tprojectID);

$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet=$g_fckeditor_toolbar;;

$of->Value="";
if($scope)
	$of->Value = $scope;
else if ($action && ($action != 'create'))
	$of->Value = $arrSpec[0]['scope'];

$smarty = new TLSmarty();
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
