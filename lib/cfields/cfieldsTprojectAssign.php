<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: cfieldsTprojectAssign.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2009/04/09 08:15:52 $ by $Author: franciscom $
 *
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$template_dir = 'cfields/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$args = init_args();
$cfield_mgr = new cfield_mgr($db);

switch($args->doAction)
{
    case 'doAssign':
	    $cfield_ids = array_keys($args->cfield);
	    $cfield_mgr->link_to_testproject($args->testproject_id,$cfield_ids);
	    break;

    case 'doUnassign':
	    $cfield_ids = array_keys($args->cfield);
	    $cfield_mgr->unlink_from_testproject($args->testproject_id,$cfield_ids);
	    break;

    case 'doReorder':
	    $cfield_ids = array_keys($args->display_order);
	    $cfield_mgr->set_display_order($args->testproject_id,$args->display_order);
	    break;

    case 'doActiveMgmt':
		$my_cf = array_keys($args->hidden_active_cfield);
		if(!isset($args->active_cfield))
		{
			$cfield_mgr->set_active_for_testproject($args->testproject_id,$my_cf,0);
		}
		else
		{
			$active = null;
			$inactive = null;
			foreach($my_cf as $cf_id)
			{
				if(isset($args->active_cfield[$cf_id]))
				{
					$active[] = $cf_id;
				}
				else
				{
					$inactive[] = $cf_id;
				}	
			}

			if(!is_null($active))
			{
				$cfield_mgr->set_active_for_testproject($args->testproject_id,$active,1);
			}
			if(!is_null($inactive))
			{
				$cfield_mgr->set_active_for_testproject($args->testproject_id,$inactive,0);
			}	
		}
		break;
}

// Get all available custom fields
$cfield_map = $cfield_mgr->get_all();

$my_cfield_map = $cfield_mgr->get_linked_to_testproject($args->testproject_id);
$cf2exclude = is_null($my_cfield_map) ? null :array_keys($my_cfield_map);
$other_cfield_map = $cfield_mgr->get_all($cf2exclude);

$smarty = new TLSmarty();
$smarty->assign('tproject_name',$args->testproject_name);
$smarty->assign('my_cf',$my_cfield_map);
$smarty->assign('other_cf',$other_cfield_map);
$smarty->display($template_dir . $default_template);

function init_args()
{
  	$_REQUEST = strings_stripSlashes($_REQUEST);
    $args = new stdClass();
     
    $my_keys = array('doAction','cfield','display_order','hidden_active_cfield','active_cfield');
    foreach($my_keys as $key)
    {
        $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
    }
	  if (!$args->cfield)
		  $args->cfield = array();
	  $args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	  $args->testproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;

	  return $args;
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,"cfield_management");
}
?>