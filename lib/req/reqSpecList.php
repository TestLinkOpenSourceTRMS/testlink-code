<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecList.php,v $
 * @version $Revision: 1.7 $
 * @modified $Date: 2005/09/06 06:46:13 $
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


testlinkInitPage();

$sqlResult = null;
$action = null;
$template = 'reqSpecList.tpl';

$title = null;
$scope = null;

// 20050906 - fm
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;


// create a new spec.
if(isset($_POST['createSRS']))
{
	$title = isset($_POST['title']) ? strings_stripSlashes($_POST['title']) : null;
	$scope = isset($_POST['scope']) ? strings_stripSlashes($_POST['scope']) : null;
	$countReq = isset($_POST['countReq']) ? strings_stripSlashes($_POST['countReq']) : null;
	
	$sqlResult = createReqSpec($title,$scope,$countReq, $prodID, $userID);
	$action = 'create';
} 
elseif(isset($_GET['deleteSRS']))
{
	$idSRS = isset($_GET['idSRS']) ? strings_stripSlashes($_GET['idSRS']) : null;
	$sqlResult = deleteReqSpec($idSRS);
	$action = 'delete';
	//$title = $_POST['deleteSRS'];
} 
elseif(isset($_GET['createForm']))
{
  $template = 'reqSpecCreate.tpl';
} 

// collect all existing documents for the product
$arrSpec = getReqSpec('product');


// 20050826 - fm
$of = new fckeditor('scope') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet=$g_fckeditor_toolbar;;


if( $scope )
{
	$of->Value=$scope;
}
else if ($action && ($action != 'create'))
{
	$of->Value=$arrSpec[0]['scope'];
}
else
{
	$of->Value="";
}


$smarty = new TLSmarty;
$smarty->assign('arrSpec', $arrSpec);
$smarty->assign('arrSpecCount', count($arrSpec));
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('action', $action);
$smarty->assign('name',$title); // of created doc
$smarty->assign('productName', $_SESSION['productName']);
$smarty->assign('modify_req_rights', has_rights("mgt_modify_req")); 
$smarty->assign('scope',$of->CreateHTML());

$smarty->display($template);
?>
