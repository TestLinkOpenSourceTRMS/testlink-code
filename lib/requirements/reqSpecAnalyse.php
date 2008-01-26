<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: reqSpecAnalyse.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/01/26 08:31:44 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Analyse coverage of a req. specification.
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('requirements.inc.php');
require_once('requirement_spec_mgr.class.php');
require_once('requirement_mgr.class.php');
testlinkInitPage($db);

$template_dir = 'requirements/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$args=init_args();                                               
$tproject_mgr = new testproject($db);                                                
$req_spec_mgr = new requirement_spec_mgr($db); 

//get list of ReqSpec
$arrReqSpec = $tproject_mgr->getOptionReqSpec($args->tprojectID);

//get first ReqSpec if not defined
if (!$args->reqSpecID && count($arrReqSpec))
{
	reset($arrReqSpec);
	$args->reqSpecID = key($arrReqSpec);
	tLog('Set a first available SRS ID: ' . $args->reqSpecID);
}

// collect REQ data
$arrCoverage = $req_spec_mgr->get_coverage($args->reqSpecID);
$arrMetrics = $req_spec_mgr->get_metrics($args->reqSpecID);

$smarty = new TLSmarty();
$smarty->assign('arrMetrics', $arrMetrics);
$smarty->assign('arrCoverage', $arrCoverage);
$smarty->assign('arrReqSpec', $arrReqSpec);
$smarty->assign('selectedReqSpec', $args->reqSpecID);
$smarty->assign('modify_req_rights', has_rights($db,"mgt_modify_req")); 
$smarty->display($template_dir . $default_template);
?>

<?php
function init_args()
{
    $_REQUEST=strings_stripSlashes($_REQUEST);
    $args->reqSpecID = isset($_REQUEST['idSRS']) ? strings_stripSlashes($_REQUETS['idSRS']) : null;
    $args->tprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    return $args;
}
?>