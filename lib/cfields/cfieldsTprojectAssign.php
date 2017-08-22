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

$cfield_mgr = new cfield_mgr($db);
$args = init_args($db);
$checkedIDSet = array_keys($args->checkedCF);

switch($args->doAction)
{
  case 'doAssign':
    $cfield_mgr->link_to_testproject($args->tproject_id,$checkedIDSet);
  break;

  case 'doUnassign':
    $cfield_mgr->unlink_from_testproject($args->tproject_id,$checkedIDSet);
  break;

  case 'doReorder':
    // To make user's life simpler, we work on all linked CF 
    // and not only on selected. 
    $cfield_mgr->set_display_order($args->tproject_id,$args->display_order);
    if( !is_null($args->location) )
    {
      $cfield_mgr->setDisplayLocation($args->tproject_id,$args->location);
    }
  break;

  case 'doBooleanMgmt':
    // To make user's life simpler, we work on all linked CF 
    // and not only on selected. 
    $args->attrBefore = $cfield_mgr->getBooleanAttributes($args->tproject_id);
    doActiveMgmt($cfield_mgr,$args);
    doRequiredMgmt($cfield_mgr,$args);
    doMonitorableMgmt($cfield_mgr,$args);
  break;

}

// Get all available custom fields
$cfield_map = $cfield_mgr->get_all();

// It's better to get AGAIN CF info AFTER user operations has been applied
// in order to display UPDATED Situation
$gui = initializeGui($args,$cfield_mgr);

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

  $key2search = array('doAction','checkedCF','display_order','location',
                      'hidden_active_cfield','active_cfield',
                      'hidden_required_cfield','required_cfield',
                      'hidden_monitorable_cfield','monitorable_cfield');
    
  foreach($key2search as $key)
  {
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
  }

  if( is_null($args->checkedCF) )
  {
    $args->checkedCF = array();
  }  

  getTproj($dbHandler,$args);

  return $args;
}


/**
 *
 */
function getTproj(&$dbH,&$args)
{  
  $args->tproject_name = '';
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? 
                         intval($_REQUEST['tproject_id']) : 0;
  
  if( $args->tproject_id == 0 )
  {
    $args->tproject_id = isset($_SESSION['testprojectID']) ? 
                           intval($_SESSION['testprojectID']) : 0;
  }  

  if( $args->tproject_id > 0 )
  {
    $mgr = new tree($dbH);
    $dummy = $mgr->get_node_hierarchy_info($args->tproject_id,null,
                array('nodeType' => 'testproject'));
    if(is_null($dummy))
    {
      throw new Exception("Unable to get Test Project ID");
    }  
    $args->tproject_name = $dummy['name'];
  }
}

/**
 *
 */
function initializeGui(&$args,&$cfield_mgr)
{
  $gui = new stdClass();

  $gui->locations=createLocationsMenu($cfield_mgr->getLocations());
  $gui->tproject_name = $args->tproject_name;
  
  $gui->linkedCF = $cfield_mgr->get_linked_to_testproject($args->tproject_id);
  $cf2exclude = is_null($gui->linkedCF) ? null :array_keys($gui->linkedCF);
  $gui->other_cf = $cfield_mgr->get_all($cf2exclude);

  $gui->cf_available_types = $cfield_mgr->get_available_types();
  $gui->cf_allowed_nodes = array();
  $allowed_nodes = $cfield_mgr->get_allowed_nodes();

  foreach($allowed_nodes as $verbose_type => $type_id)
  {
    $gui->cf_allowed_nodes[$type_id] = lang_get($verbose_type);
  }  

  return $gui;
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
  $cfg = array();
  $cfg['attrKey'] = 'required';
  $cfg['dbField'] = 'required';
  $cfg['attr'] = "{$cfg['attrKey']}_cfield";
  $cfg['m2c'] = 'set' . ucfirst($cfg['attrKey']);
  $cfg['ha'] = "hidden_{$cfg['attr']}";

  doSimpleBooleanMgmt($cfieldMgr,$argsObj,$cfg);
}


/**
 *
 */
function doActiveMgmt(&$cfieldMgr,$argsObj)
{
  $cfg = array();
  $cfg['attrKey'] = 'active';
  $cfg['dbField'] = 'active';
  $cfg['attr'] = "{$cfg['attrKey']}_cfield";
  $cfg['m2c'] = 'set_active_for_testproject';
  $cfg['ha'] = "hidden_{$cfg['attr']}";

  doSimpleBooleanMgmt($cfieldMgr,$argsObj,$cfg);
}

/**
 *
 */
function doMonitorableMgmt(&$cfieldMgr,$argsObj)
{  
  $cfg = array();
  $cfg['attrKey'] = 'monitorable';
  $cfg['dbField'] = 'monitorable';
  $cfg['attr'] = "{$cfg['attrKey']}_cfield";
  $cfg['m2c'] = 'set' . ucfirst($cfg['attrKey']);
  $cfg['ha'] = "hidden_{$cfg['attr']}";

  doSimpleBooleanMgmt($cfieldMgr,$argsObj,$cfg);
}

/**
 *
 *
 */
function doSimpleBooleanMgmt(&$cfieldMgr,$argsObj,$cfg)
{ 

  // This way user does not need to check cf for this operations
  // Think makes life easier   
  $serviceInput = $cfg['ha'];
  $cfSet = array_keys($argsObj->$serviceInput);

  $m2c = $cfg['m2c'];
  $operativeInput = $cfg['attr'];
  
  // we are working with checkboxes, and as we know if is not checked
  // nothing will arrive on $_REQUEST
  if( is_null($argsObj->$operativeInput) )
  {
    $cfieldMgr->$m2c($argsObj->tproject_id,$cfSet,0);
  }
  else
  {
    $on = null;
    $off = null;
    foreach($cfSet as $id)
    {
      if( isset($argsObj->$operativeInput[$id]) )
      {
        if($argsObj->attrBefore[$id][$cfg['dbField']] == 0)
        {
          $on[] = $id;
        }  
      }
      else
      {
        if($argsObj->attrBefore[$id][$cfg['dbField']] == 1)
        {
          $off[] = $id;
        }  
      } 
    }

    if(!is_null($on))
    {
      $cfieldMgr->$m2c($argsObj->tproject_id,$on,1);
    }
    
    if(!is_null($off))
    {
      $cfieldMgr->$m2c($argsObj->tproject_id,$off,0);
    } 
  }

} 
 