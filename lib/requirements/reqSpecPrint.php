<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecPrint.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2007/11/19 21:02:56 $
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
testlinkInitPage($db);

$idSRS = isset($_REQUEST['idSRS']) ? $_REQUEST['idSRS'] : null;
$prodName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
$prodID = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
$my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

$tproject = new testproject($db);
print printSRS($db,$tproject,$idSRS, $prodName, $prodID, $my_userID,$_SESSION['basehref']);
?>
