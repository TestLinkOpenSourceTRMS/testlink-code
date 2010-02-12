<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Delete a device in infrastructure list
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: deleteInfrastructure.php,v 1.1 2010/02/12 00:20:12 havlat Exp $
 *
 * @internal Revisions:
 * None
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$data['userfeedback'] = lang_get('infrastructure_msg_no_action');
$data['success'] = FALSE;
$args = init_args();

//if($args->testprojectId)
//if($args->testprojectId && $rightEdit)
//if(checkRights($db,$user))
if ($_SESSION['currentUser']->hasRight($db,"project_infrastructure_edit"))
{
	$tlIs = new tlInfrastructure($args->testprojectId, $db);
	$data['success'] = $tlIs->deleteInfrastructure($args->machineID);
	$data['userfeedback'] = $tlIs->getUserFeedback();
}
else
{
	tLog('User has not rights to set a device!','ERROR');
	$data['userfeedback'] = lang_get('infrastructure_msg_no_rights');
}

echo json_encode($data);

function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
	$iParams = array("machineID" => array(tlInputParameter::INT_N));

	$args = new stdClass();
    R_PARAMS($iParams,$args);
    
    // from session
    $args->testprojectId = $_SESSION['testprojectID'];
    $args->userId = $_SESSION['userID'];

    return $args;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * 
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"project_infrastructure_edit");
}
?>