<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: reqSpecPrint.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/08/31 08:45:11 $
 * 
 * @author Martin Havlat
 * 
 * print a req. specification.
 * 
 */
////////////////////////////////////////////////////////////////////////////////
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
testlinkInitPage();

$idSRS = isset($_REQUEST['idSRS']) ? strings_stripSlashes($_REQUEST['idSRS']) : null;

// 20050830 - fm
$prodName = isset($_SESSION['productName']) ? strings_stripSlashes($_SESSION['productName']) : null;
$my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

print printSRS($idSRS, $prodName, $my_userID);
?>
