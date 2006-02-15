<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecPrint.php,v $
 * @version $Revision: 1.7 $
 * @modified $Date: 2006/02/15 08:50:19 $
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

$idSRS = isset($_REQUEST['idSRS']) ? strings_stripSlashes($_REQUEST['idSRS']) : null;
$prodName = isset($_SESSION['testprojectName']) ? strings_stripSlashes($_SESSION['testprojectName']) : null;
$prodID = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
$my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

// 20050905 - fm reduce global coupling
print printSRS($idSRS, $prodName, $prodID, $my_userID,$_SESSION['basehref']);
?>
