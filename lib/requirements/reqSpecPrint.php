<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: reqSpecPrint.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2009/03/03 07:48:12 $
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
$tproject = new testproject($db);
print renderSRS($db,$tproject,$args->idSRS, $args->tproject_name, 
                $args->tproject_id, $args->my_userID,$_SESSION['basehref']);


/**
 * init_args
 *
 */
function init_args()
{
    $args = new stdClass();
    
    $args->idSRS = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : null;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
    $args->my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;
    return $args;
}

/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_req');
}
?>
