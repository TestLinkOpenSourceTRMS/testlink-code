<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  cfieldsTprojectAssign.php
 *
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args = init_args($db);
$cfield_mgr = new cfield_mgr($db);

switch($args->doAction)
{
  case 'doAssign':
    $cfield_ids = array_keys($args->cfield);
    $cfield_mgr->link_to_testproject($args->tproject_id,$cfield_ids);
  break;

  case 'doUnassign':
    $cfield_ids = array_keys($args->cfield);
    $cfield_mgr->unlink_from_testproject($args->tproject_id,$cfield_ids);
  break;

  case 'doReorder':
    $cfield_ids = array_keys($args->display_order);
    $cfield_mgr->set_display_order($args->tproject_id,$args->display_order);
    if( !is_null($args->location) )
    {
      $cfield_mgr->setDisplayLocation($args->tproject_id,$args->location);
    }
  break;

  case 'doBooleanMgmt':
    doActiveMgmt($cfield_mgr,$args);
    doRequiredMgmt($cfield_mgr,$args);
  break;

}

// Get all available custom fields
$cfield_map = $cfield_mgr->get_all();

$gui = new stdClass();

$gui->locations=createLocationsMenu($cfield_mgr->getLocations());
$gui->tproject_name = $args->tproject_name;
$gui->my_cf = $cfield_mgr->get_linked_to_testproject($args->tproject_id);
$cf2exclude = is_null($gui->my_cf) ? null :array_keys($gui->my_cf);
$gui->other_cf = $cfield_mgr->get_all($cf2exclude);
$gui->cf_available_types = $cfield_mgr->get_available_types();
$gui->cf_allowed_nodes = array();
$allowed_nodes = $cfield_mgr->get_allowed_nodes();
foreach($allowed_nodes as $verbose_type => $type_id)
{
  $gui->cf_allowed_nodes[$type_id] = lang_get($verbose_type);
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * create object with all user inputs
 *
 */
function init_args(&$dbHandler)
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  $args = new stdClass();
     
  $key2search = array('doAction','cfield','display_order','location',
                      'hidden_active_cfield','active_cfield',
                      'hidden_required_cfield','required_cfield');
    
  foreach($key2search as $key)
  {
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
  }
    
  // Need comments
  if (!$args->cfield)
  {
    $args->cfield = array();
  }
  
  $args->tproject_name = '';
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
  
  if( $args->tproject_id == 0 )
  {
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }  

  if( $args->tproject_id > 0 )
  {
    $mgr = new tree($dbHandler);
    $dummy = $mgr->get_node_hierarchy_info($args->tproject_id,null,array('nodeType' => 'testproject'));
    if(is_null($dummy))
    {
      throw new Exception("Unable to get Test Project ID");
    }  
    $args->tproject_name = $dummy['name'];
  }
  return $args;
}

/**
 *
 *
 */
function checkRights(&$db,&$user)
{
  return $user->hasRight($db,"cfield_management");
}

/**
 * @parame map of maps with locations of CF
 *         key: item type: 'testcase','testsuite', etc
 *
 */
function createLocationsMenu($locations)
{
  $menuContents=null;
  $items = $locations['testcase'];
  
  // loop over status for user interface, because these are the statuses
  // user can assign while executing test cases
  
  foreach($items as $code => $key4label)
  {
    $menuContents[$code] = lang_get($key4label); 
  }
  
  return $menuContents;
}


/**
 *
 */
 function doRequiredMgmt(&$cfieldMgr,$argsObj)
 {
    $cfSet = array_keys($argsObj->hidden_required_cfield);
    if(!isset($argsObj->required_cfield))
    {
      $cfieldMgr->setRequired($argsObj->tproject_id,$cfSet,0);
    }
    else
    {
      $on = null;
      $off = null;
      foreach($cfSet as $id)
      {
        if(isset($argsObj->required_cfield[$id]))
        {
          $on[] = $id;
        }
        else
        {
          $off[] = $id;
        } 
      }

      if(!is_null($on))
      {
        $cfieldMgr->setRequired($argsObj->tproject_id,$on,1);
      }
      if(!is_null($off))
      {
        $cfieldMgr->setRequired($argsObj->tproject_id,$off,0);
      } 
    }
 } 

 /**
 *
 */
 function doActiveMgmt(&$cfieldMgr,$argsObj)
 {
    $cfSet = array_keys($argsObj->hidden_required_cfield);
    if(!isset($argsObj->active_cfield))
    {
      $cfieldMgr->set_active_for_testproject($argsObj->tproject_id,$cfSet,0);
    }
    else
    {
      $on = null;
      $off = null;
      foreach($cfSet as $id)
      {
        if(isset($argsObj->active_cfield[$id]))
        {
          $on[] = $id;
        }
        else
        {
          $off[] = $id;
        } 
      }

      if(!is_null($on))
      {
        $cfieldMgr->set_active_for_testproject($argsObj->tproject_id,$on,1);
      }
      if(!is_null($off))
      {
        $cfieldMgr->set_active_for_testproject($argsObj->tproject_id,$off,0);
      } 
    }
 } 