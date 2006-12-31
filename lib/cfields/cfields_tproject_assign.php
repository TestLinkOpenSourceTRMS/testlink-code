<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfields_tproject_assign.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/12/31 16:16:20 $ by $Author: franciscom $
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

// Get all available custom fields
$cfield_map=$cfield_mgr->get_all();

$my_cfield_map=$cfield_mgr->get_linked_to_testproject($testproject_id);
$cf2exclude = is_null($my_cfield_map) ? null :array_keys($my_cfield_map);
$other_cfield_map=$cfield_mgr->get_all($cf2exclude);

/*
echo "<pre>debug 20061227 \$exclude" . __FUNCTION__ . " --- "; print_r($exclude); echo "</pre>";
echo "<pre>debug 20061227 \$my_cfield_map" . __FUNCTION__ . " --- "; print_r($my_cfield_map); echo "</pre>";

echo "<pre>debug 20061227 \$cfield_map" . __FUNCTION__ . " --- "; print_r($cfield_map); echo "</pre>";
echo "<pre>debug 20061227 \$my_cfield_map" . __FUNCTION__ . " --- "; print_r($my_cfield_map); echo "</pre>";
echo "<pre>debug 20061227 \$other_cfield_map" . __FUNCTION__ . " --- "; print_r($other_cfield_map); echo "</pre>";
*/



$smarty = new TLSmarty();

$smarty->assign('tproject_name',$testproject_name);
$smarty->assign('my_cf',$my_cfield_map);
$smarty->assign('other_cf',$other_cfield_map);

//$smarty->assign('cf_types',$cfield_mgr->get_available_types());
$smarty->display('cfields_tproject_assign.tpl');
?>