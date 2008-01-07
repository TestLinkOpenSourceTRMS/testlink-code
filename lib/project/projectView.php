<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: projectView.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2008/01/07 07:57:52 $ $Author: franciscom $
 *
 * Display list of test projects
 *
*/
require('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$template_dir='project/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));
$tproject_mgr = New testproject($db);

$smarty = new TLSmarty();
$smarty->assign('canManage', has_rights($db,"mgt_modify_product"));

$tprojects=$tproject_mgr->get_all();
$args = init_args();

if(count($tprojects) == 0)
{
    $default_template="projectEdit.tpl"; 
    $smarty->assign('doAction',"create");
}
else
{
    $smarty->assign('tprojects',$tprojects);
}

$smarty->assign('doAction' . $args->doAction);
$smarty->display($template_dir . $default_template);
?>

<?php
function init_args()
{
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $args->tproject_id=isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
    $args->doAction=isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : 'list' ;
    
    return $args;  
}
?>