<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: tcAssignedToUser.php,v $
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/01/31 19:54:17 $  $Author: franciscom $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 *
*/
require_once("../../config.inc.php");
require_once("common.php");

testlinkInitPage($db);
$gui=new stdClass();
$gui->glueChar = config_get('testcase_cfg')->glue_character;

$templateCfg = templateConfiguration();
$args=init_args();

$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);
$tproject_info=$tproject_mgr->get_by_id($args->tproject_id);
$gui->tproject_name=$tproject_info['name'];

$gui->pageTitle=sprintf(lang_get('testcases_assigned_to_user'),$gui->tproject_name,$args->login_name);

// Get all test cases assigned to user without filtering by execution status
$options=new stdClass();
$options->mode='full_path';
$gui->resultSet=$tcase_mgr->get_assigned_to_user($args->user_id,$args->tproject_id,testcase::ALL_TESTPLANS,$options);

if( !is_null($gui->resultSet) )
{
    $tplanSet=array_keys($gui->resultSet);
    $sql="SELECT name,id FROM nodes_hierarchy WHERE id IN (" . implode(',',$tplanSet) . ")";
    $gui->tplanNames=$db->fetchRowsIntoMap($sql,'id');
}
$gui->warning_msg='';

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * init_args()
 * Get in an object all data that has arrived to page through _REQUEST or _SESSION.
 * If you think this page as a function, you can consider this data arguments (args)
 * to a function call.
 * Using all this data as one object property will help developer to understand
 * if data is received or produced on page.
 *
 * @author franciscom - francisco.mancardi@gmail.com
 * @args - used global coupling accessing $_REQUEST and $_SESSION
 * 
 * @return object of stdClass
 *
 * @since 20090131 - franciscom
 */
function init_args()
{
    $_REQUEST=strings_stripSlashes($_REQUEST);
    $args = new stdClass();
    
    $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : 0;
    if( $args->tproject_id == 0)
    {
        $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    }

    $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : 0;
    if( $args->tplan_id == 0)
    {
        $args->tplan_id = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
    }
    
    $args->user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
    if( $args->user_id == 0)
    {
        $args->user_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;
    }
    $args->login_name = $_SESSION['currentUser']->login;
 
    return $args;
}
?>