<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecPrint.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2006/03/23 20:46:30 $
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
