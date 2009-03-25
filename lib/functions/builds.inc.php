<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: builds.inc.php,v 1.26 2009/03/25 20:53:13 schlundus Exp $
* 
* @author Martin Havlat
*
* Functions for Test Plan management - build related
*
* 20070120 - franciscom - changes to getBuilds()
*
*/
require_once('../../config.inc.php');
require_once("../functions/common.php");

//@TODO: schlundus, should be moved inside class testplan
function getBuild_by_id(&$db,$buildID)
{
	$sql = "SELECT builds.* FROM builds WHERE builds.id = " . $buildID;
	$result = $db->exec_query($sql);
	$myrow = $db->fetch_array($result);

	return $myrow;
}
?>