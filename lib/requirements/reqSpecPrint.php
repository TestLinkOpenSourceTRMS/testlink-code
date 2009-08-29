<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: reqSpecPrint.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2009/08/29 19:21:42 $
 *
 * @author Martin Havlat
 *
 * print a req. specification.
 *
 * @author Francisco Mancardi 
 *
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$args = init_args();
echo renderSRS($db,$args->req_spec_id, $args->tproject_id, $args->my_userID,$args->basehref);


function init_args()
{
	$args = new stdClass();
	$iParams = array("req_spec_id" => array(tlInputParameter::INT_N));	
	R_PARAMS($iParams,$args);
    
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
    $args->my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;
    $args->basehref = $_SESSION['basehref'];
    
    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_req');
}
?>