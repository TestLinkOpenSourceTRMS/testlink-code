<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Add or modify a device in infrastructure list
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: setInfrastructure.php,v 1.1 2010/02/12 00:20:12 havlat Exp $
 *
 * @internal Revisions:
 * None
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db,false,false,"checkRights");

$data['userfeedback'] = lang_get('infrastructure_msg_no_action');
$data['success'] = FALSE;
$args = init_args();
//$rightEdit = has_rights($db,"project_infrastructure_edit");

//if($args->testprojectId && $rightEdit)
//if($args->testprojectId)
if ($_SESSION['currentUser']->hasRight($db,"project_infrastructure_edit"))
{
	$tlIs = new tlInfrastructure($args->testprojectId, $db);
	$data['success'] = $tlIs->setInfrastructure($args);
	$data['userfeedback'] = $tlIs->getUserFeedback();
	$data['record'] = $tlIs->getCurrentData();
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
	$iParams = array("machineID" => array(tlInputParameter::INT_N),
					"machineOwner" => array(tlInputParameter::INT_N),
			        "machineName" => array(tlInputParameter::STRING_N,1,100),
			        "machineIp" => array(tlInputParameter::STRING_N,0,50),
			        "machineNotes" => array(tlInputParameter::STRING_N,0,1000),
			        "machinePurpose" => array(tlInputParameter::STRING_N,0,1000),
			        "machineHw" => array(tlInputParameter::STRING_N,0,500),
	 				);

	$args = new stdClass();
    R_PARAMS($iParams,$args);
    
//    $args->doCreate = isset($_REQUEST['doCreate']) ? 1 : 0;
//    $args->doDelete = isset($_REQUEST['doDelete']) ? 1 : 0;
        
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