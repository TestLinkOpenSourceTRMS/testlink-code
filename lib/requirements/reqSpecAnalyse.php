<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecAnalyse.php,v $
 * @version $Revision: 1.9 $
 * @modified $Date: 2009/09/28 08:43:22 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Analyse coverage of a req. specification.
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('requirement_spec_mgr.class.php');
require_once('requirement_mgr.class.php');
testlinkInitPage($db,false,false,"checkRights");

$template_dir = 'requirements/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));
$args = init_args();                                               
$tproject_mgr = new testproject($db);                                                
$req_spec_mgr = new requirement_spec_mgr($db); 

$tcasecfg = config_get('testcase_cfg');
$tcprefix = $tproject_mgr->getTestCasePrefix($args->tprojectID) . $tcasecfg->glue_character;

// get list of ReqSpec
$ns = new stdClass();
// $ns->reqSpec = $tproject_mgr->getOptionReqSpec($args->tprojectID);
$ns->reqSpec = $tproject_mgr->genComboReqSpec($args->tprojectID);



//get first ReqSpec if not defined
if($args->reqSpecID == 0 && count($ns->reqSpec))
{
 	reset($ns->reqSpec);
	$args->reqSpecID = key($ns->reqSpec);
}

// collect REQ data
$ns->coverage = $req_spec_mgr->get_coverage($args->reqSpecID);
$ns->metrics = $req_spec_mgr->get_metrics($args->reqSpecID);

$smarty = new TLSmarty();
foreach($ns as $key => $value)
{
    $smarty->assign($key, $value);
}

$smarty->assign('tcprefix', $tcprefix);
$smarty->assign('selectedReqSpec', $args->reqSpecID);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->display($template_dir . $default_template);


/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
  	$args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args->reqSpecID = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : 0;
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_req');
}
?>