<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: reqSpecView.php,v $
 * @version $Revision: 1.24 $
 * @modified $Date: 2009/11/19 17:52:13 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * Screen to view existing requirements within a req. specification.
 *
 * rev: 20080924 - franciscom - use requirements count to enable/disable features
 *      20070415 - franciscom - custom field manager
 *      20070415 - franciscom - added reorder feature
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('requirements.inc.php');
require_once('attachments.inc.php');
require_once("configCheck.php");
testlinkInitPage($db,false,false,"checkRights");

$req_spec_mgr = new requirement_spec_mgr($db);
$req_mgr = new requirement_mgr($db);

$templateCfg = templateConfiguration();

$args = init_args();

$gui = new stdClass();
$gui->grants = new stdClass();
$gui->grants->req_mgmt = has_rights($db,"mgt_modify_req");

$gui->req_spec = $req_spec_mgr->get_by_id($args->req_spec_id);
$gui->req_spec_id = $args->req_spec_id;
$gui->tproject_name = $args->tproject_name;
$gui->name = $gui->req_spec['title'];

// 20091119 - francisco.mancardi@gruppotesi.com
$gui->main_descr = lang_get('req_spec_short') . config_get('gui_title_separator_1') . 
                   "[{$gui->req_spec['doc_id']}] :: " .$gui->req_spec['title'];
$gui->refresh_tree = 'no';
$gui->cfields = $req_spec_mgr->html_table_of_custom_field_values($args->req_spec_id,$args->tproject_id);
$gui->attachments = getAttachmentInfosFrom($req_spec_mgr,$args->req_spec_id);
$gui->requirements_count = $req_spec_mgr->get_requirements_count($args->req_spec_id);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);

$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args()
{
	$iParams = array(
			"req_spec_id" => array(tlInputParameter::INT_N),
	);
    $args = new stdClass();
    R_PARAMS($iParams,$args);

    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;
    
    return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_req');
}
?>