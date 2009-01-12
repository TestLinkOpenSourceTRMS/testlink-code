<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: reqSpecPrint.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2009/01/12 21:53:43 $
 *
 * @author Martin Havlat
 *
 * print a req. specification.
 *
 * @author Francisco Mancardi - 20050906 - reduce global coupling
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$idSRS = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : null;
$prodName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
$prodID = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
$my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

$tproject = new testproject($db);
print printSRS($db,$tproject,$idSRS, $prodName, $prodID, $my_userID,$_SESSION['basehref']);

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_req');
}
?>
