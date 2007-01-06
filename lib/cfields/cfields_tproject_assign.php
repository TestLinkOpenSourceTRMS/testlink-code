<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfields_tproject_assign.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/01/06 15:16:26 $ by $Author: franciscom $
 *
 * 20070105 - franciscom - added reorder feature
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
testlinkInitPage($db);

$testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$testproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';

$cfield_mgr=New cfield_mgr($db);

$do_assign=(isset($_REQUEST['assign']) && isset($_REQUEST['cfield'])) ? 1 : 0;
$do_unassign=(isset($_REQUEST['unassign']) && isset($_REQUEST['cfield'])) ? 1 : 0;
$do_active_mgmt=isset($_REQUEST['active_mgmt']) ? 1 : 0;
$do_reorder=isset($_REQUEST['reorder']) ? 1 : 0;


if($do_assign)
{
  $cfield_ids=array_keys($_REQUEST['cfield']);
  $cfield_mgr->link_to_testproject($testproject_id,$cfield_ids);  
}

if($do_unassign)
{
  $cfield_ids=array_keys($_REQUEST['cfield']);
  $cfield_mgr->unlink_from_testproject($testproject_id,$cfield_ids);  
}

if($do_active_mgmt)
{
  $my_cf=array_keys($_REQUEST['hidden_active_cfield']);
  if(!isset($_REQUEST['active_cfield']) )
  {
    $cfield_mgr->set_active_for_testproject($testproject_id,$my_cf,0);   
  }
  else
  {
    $active=null;
    $inactive=null;
    foreach($my_cf as $cf_id)
    {
      if( isset($_REQUEST['active_cfield'][$cf_id]) )
      {
        $active[]=$cf_id;
      }
      else
      {
        $inactive[]=$cf_id;
      }  
    } // foreach
    if( !is_null($active) )
    {
       $cfield_mgr->set_active_for_testproject($testproject_id,$active,1);   
    }
    if( !is_null($inactive) )
    {
       $cfield_mgr->set_active_for_testproject($testproject_id,$inactive,0);   
    }
  }
}

if($do_reorder)
{
  $cfield_ids=array_keys($_REQUEST['display_order']);
  $cfield_mgr->set_display_order($testproject_id,$_REQUEST['display_order']);
} //if($do_reorder)


// Get all available custom fields
$cfield_map=$cfield_mgr->get_all();

$my_cfield_map=$cfield_mgr->get_linked_to_testproject($testproject_id);
$cf2exclude = is_null($my_cfield_map) ? null :array_keys($my_cfield_map);
$other_cfield_map=$cfield_mgr->get_all($cf2exclude);


$smarty = new TLSmarty();

$smarty->assign('tproject_name',$testproject_name);
$smarty->assign('my_cf',$my_cfield_map);
$smarty->assign('other_cf',$other_cfield_map);

//$smarty->assign('cf_types',$cfield_mgr->get_available_types());
$smarty->display('cfields_tproject_assign.tpl');
?>