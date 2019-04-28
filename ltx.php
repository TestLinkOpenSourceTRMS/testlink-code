<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Direct links for external access to testlink items with frames for navigation and tree.
 *
 * IMPORTANT - LIMITATIONS:
 * User has to login before clicking the link!
 * If user is not logged in he is redirected to login page. 
 * After login main page is shown, Clicking the link again then it works!
 *
 * 
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2015,2017 TestLink community
 * @link        http://www.testlink.org/
 *
 */

// use output buffer to prevent headers/data from being sent before 
// cookies are set, else it will fail
ob_start();

// some session and settings stuff from original index.php 
require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');
testlinkInitPage($db, true);

$smarty = new TLSmarty();

// This process seems to have two steps
//
// Step 1
// Display outer frame, and do a new call 
// to display the wished content, in inner frame
//
// Step 2
// Here we will get what we need
// 
// display outer or inner frame?
// why I'm asking this question?
//
if (!isset($_GET['load'])) 
{
  // display outer frame, pass parameters to next script call for inner frame
  // ATTENTION:
  // Because we are going to recreate an URL with paramenters on the URL, we need 
  // to use urlencode() on data we have got.
  //
  $args = init_args($db);
  $args->tproject_id = 0;  
  
  if( $args->status_ok )
  {
    if($args->tplan_id != '')
    {
      $hasRight = checkTestPlan($db,$args->user,$args);
      if( $hasRight )
      {
        $lof = 'launch_outer_' . $args->item;
        $lof($smarty,$args);
      }  
    }   
  } 
  else
  {
    echo lang_get('security_check_ko');
    ob_end_flush();
    exit();    
  } 
} 
else 
{
  // 
  // inner frame, parameters passed
  // figure out what to display 
  //
  // key: item, value: url to tree management page
  $driver = isset($_GET['item']) ? $_GET['item'] : null;
  if(is_null($driver))
  {
    die();
  }  
  
  $lif = 'launch_inner_' . $driver;
  $lif($db,$smarty);
}
ob_end_flush();


/**
 *
 *
 */
function checkTestPlan(&$db,&$user,&$args)
{
  $hasRight = false;
  $tplan_mgr = new testplan($db);
  
  $item_info = $tplan_mgr->get_by_id($args->tplan_id,array( 'output' => 'minimun'));
  if(($op['status_ok'] = !is_null($item_info)))
  {
    $args->tproject_id = intval($item_info['tproject_id']);

    switch($args->item)
    {
      case 'exec':
      case 'xta2m':
        $hasRight = $user->hasRight($db,'testplan_execute',
                                    $args->tproject_id,$args->tplan_id);
      break;


      default:
        // need to fail!!
      break;
    }
  }
  return $hasRight;
}  


/**
 *
 */
function init_args(&$dbHandler)
{
  $args = new stdClass();
  $args->tplan_id = intval(isset($_GET['tplan_id']) ? $_GET['tplan_id'] : null);
  $args->tcversion_id = intval(isset($_GET['tcversion_id']) ? $_GET['tcversion_id'] : null);
  $args->platform_id = intval(isset($_GET['platform_id']) ? $_GET['platform_id'] : null);
  $args->build_id = intval(isset($_GET['build_id']) ? $_GET['build_id'] : null);

  $args->anchor = isset($_GET['anchor']) ? $_GET['anchor'] : null;
  $args->item = isset($_GET['item']) ? $_GET['item'] : null;

  $args->feature_id = isset($_GET['feature_id']) ? $_GET['feature_id'] : null;


  $args->target_user_id = intval(isset($_GET['user_id']) ? $_GET['user_id'] : null);
  $args->user = $_SESSION['currentUser'];
  $args->user_id = $_SESSION['userID']; 

  // status depends on access request
  $cfn = 'check_';
  switch($args->item)
  {
    case 'exec':
      $cfn .= $args->item;
      $args->status_ok = ($args->build_id >0);
    break;

    case 'xta2m':
      $cfn .= $args->item;
      $args->status_ok = ($args->target_user_id >0 && $args->tplan_id >0);
    break;

    default:
      $cfn = '';
      $args->status_ok = false;
    break;
  }

  if($args->status_ok && $cfn != '')
  {
    $cfn($dbHandler,$args);
  }  
  return $args;  
}

/**
 *
 */
function build_link_exec(&$argsObj)
{
  $lk = isset($_GET['item']) ? "item=" . $_GET['item'] : '';
  
  if($argsObj->feature_id >0)
  {
    $lk .= "&feature_id=" . $argsObj->feature_id;
  } 
  else
  {
    $lk .= "&tplan_id=" . $argsObj->tplan_id . "&platform_id=" . $argsObj->platform_id;
           "&tcversion_id=" . $argsObj->tcversion_id;
  } 
  $lk .= "&build_id=" . $argsObj->build_id;
  $lk .= '&load' . (isset($_GET['anchor']) ? '&anchor=' . $_GET['anchor'] : "");
 
  return $lk;
}




/**
 * 
 *
 */
function process_exec(&$dbHandler,$context)
{
  $ret = array();
  $ret['url'] = null;
  $ret['msg'] = 'ko';

  $treeMgr = new tree($dbHandler);
  $info = $treeMgr->get_node_hierarchy_info($context['tcversion_id']);

  $ret['url'] = "lib/execute/execSetResults.php?level=testcase" .
                "&version_id=" . $context['tcversion_id'] . 
                "&id=" . $info['parent_id'] . 
                "&setting_testplan=" . $context['setting_testplan'] .
                "&setting_build=" . $context['setting_build'] .
                "&setting_platform=" . $context['setting_platform'];



  $ret['msg'] = 'ok';
  return $ret;
}

/**
 * xta2m: eXecution Tasks Assigned TO Me
 *
 */
function process_xta2m(&$dbHandler,$context)
{
  $ret = array();
  $ret['url'] = null;
  $ret['msg'] = 'ko';

  $treeMgr = new tree($dbHandler);
  $info = $treeMgr->get_node_hierarchy_info($context['tcversion_id']);

  $ret['url'] = "lib/execute/execSetResults.php?level=testcase" .
                "&version_id=" . $context['tcversion_id'] . 
                "&id=" . $info['parent_id'] . 
                "&setting_testplan=" . $context['setting_testplan'] .
                "&setting_build=" . $context['setting_build'] .
                "&setting_platform=" . $context['setting_platform'];



  $ret['msg'] = 'ok';
  return $ret;
}

/**
 *
 *
 */
function check_exec(&$dbHandler,&$argsObj)
{

  if( $argsObj->feature_id >0 )
  {
    // get missing data
    $tb = DB_TABLE_PREFIX . 'testplan_tcversions';
    $sql = "SELECT testplan_id,platform_id,tcversion_id " .
           "FROM {$tb} WHERE id=" . $argsObj->feature_id;

    $rs = $dbHandler->get_recordset($sql);
    $argsObj->tplan_id = $rs[0]['testplan_id'];
    $argsObj->tcversion_id = $rs[0]['tcversion_id'];
    $argsObj->platform_id = $rs[0]['platform_id'];
  } 
  else
  {
    $argsObj->status_ok = ($argsObj->tplan_id > 0) &&  
                          ($argsObj->tcversion_id >0); 
  } 
}

/**
 *
 *
 */
function check_xta2m(&$dbHandler,&$argsObj)
{
  $argsObj->status_ok = ($argsObj->target_user_id > 0 && 
                         $argsObj->tplan_id >0);

  if($argsObj->target_user_id != $argsObj->user_id)
  {
    $argsObj->status_ok = false;
  }
}


/**
 * 
 *
 */
function launch_inner_exec(&$dbHandler,&$tplMgr)
{
  $itemCode = array('exec' => 'lib/execute/execNavigator.php');
  $op = array('status_ok' => true, 'msg' => '');

  // First check for keys in _GET that MUST EXIST
  // key: key on _GET, value: labelID defined on strings.txt
  $mandatoryKeys = array('item' => 'item_not_set',
                         'build_id' => 'build_id_not_set');

  foreach($mandatoryKeys as $key => $labelID)
  {
    $op['status_ok'] = isset($_GET[$key]);
    if( !$op['status_ok'])
    {
      $op['msg'] = lang_get($labelID);
      break;
    }
  } 

  if( $op['status_ok'] )
  {
    $op['status_ok'] = isset($_GET['feature_id']);
    if( !$op['status_ok'] )
    {
      $keySet = array('tplan_id' => 'testplan_not_set',
                      'tcversion_id' => 'tcversion_id',
                      'platform_id' => 'platform_id_not_set');

      foreach($keySet as $key => $labelID)
      {
        $op['status_ok'] = isset($_GET[$key]);
        if( !$op['status_ok'])
        {
          $op['msg'] = lang_get($labelID);
          break;
        }
      } 
    }  
  }

  $args = init_args($dbHandler);
  if($op['status_ok'])
  {
    // Set Environment    
    $tplan_mgr = new testplan($dbHandler);
    $info = $tplan_mgr->get_by_id($args->tplan_id,array('output' => 'minimun'));
    
    if(is_null($info))
    {
      die('ltx - tplan info does not exist');
    }  

    $tproject_mgr = new testproject($dbHandler);
    $tproject_mgr->setSessionProject($info['tproject_id']);
    $op['status_ok'] = true;
  } 

  if($op['status_ok'])
  {
    // Build  name of function to call for doing the job.
    $pfn = 'process_' . $args->item;

    $ctx = array();
    $ctx['setting_testplan'] = $args->tplan_id;
    $ctx['setting_build'] = $args->build_id;
    $ctx['setting_platform'] = $args->platform_id;
    $ctx['tcversion_id'] = $args->tcversion_id;
    $ctx['tcase_id'] = 0;
    $ctx['user_id'] = $args->user_id;

    $jump_to = $pfn($dbHandler,$ctx);
    $op['status_ok'] = !is_null($jump_to['url']);
    $op['msg'] = $jump_to['msg'];
  }

  if($op['status_ok'])
  {
    $treeframe = $itemCode[$args->item] .
                 '?loadExecDashboard=0' . 
                 '&setting_testplan=' . $args->tplan_id .
                 '&setting_build=' . $args->build_id .
                 '&setting_platform=' . $args->platform_id;

    $tplMgr->assign('title', lang_get('main_page_title'));
    $tplMgr->assign('treewidth', TL_FRMWORKAREA_LEFT_FRAME_WIDTH);
    $tplMgr->assign('workframe', $jump_to['url']);
    $tplMgr->assign('treeframe', $treeframe);
    $tplMgr->display('frmInner.tpl');
  }
  else
  {
    echo $op['msg'];
    ob_end_flush();
    exit();
  }
} // function end

/**
 * xta2m: eXecution Tasks Assigned TO Me
 *
 */
function launch_inner_xta2m(&$dbHandler,&$tplMgr)
{
  $args = init_args($dbHandler);

  //if($args->status_ok == FALSE)
  //{
  //  echo 'NOOO';
  //  ob_end_flush();
  //  exit();
  //}  

  $jt = $_SESSION['basehref'] . '/lib/testcases/' .
        'tcAssignedToUser.php?user_id=' . $args->target_user_id .
  
  $k2c = array('tplan_id','build_id');
  foreach($k2c as $tg)
  {
    if( property_exists($args,$tg) && $args->$tg > 0 )
    {
      $jt .= "&$tg=" . $args->$tg;
    }      
  }  

  $tplMgr->assign('workframe', $jt);
  $tplMgr->display('workframe.tpl');
}

/**
 *
 */
function launch_outer_exec(&$tplMgr,$argsObj)
{
  $gui = new stdClass();
  $gui->titleframe = 'lib/general/navBar.php?caller=linkto';
  $gui->navbar_height = config_get('navbar_height');
  
  if( $argsObj->tproject_id > 0)
  {
    $gui->titleframe .= '&testproject=' . $argsObj->tproject_id;
  } 
  $gui->title = lang_get('main_page_title');
  $gui->mainframe = 'ltx.php?' . build_link_exec($argsObj);

  $tplMgr->assign('gui', $gui);
  $tplMgr->display('main.tpl');
}

/**
 *
 */
function launch_outer_xta2m(&$tplMgr,$argsObj)
{
  $gui = new stdClass();
  $gui->titleframe = 'lib/general/navBar.php?caller=linkto';
  $gui->navbar_height = config_get('navbar_height');
  
  if( $argsObj->tproject_id > 0)
  {
    $gui->titleframe .= '&testproject=' . $argsObj->tproject_id;
  } 
  $gui->title = lang_get('main_page_title');
  $gui->mainframe = 'ltx.php?item=xta2m&load=1' .
                    '&user_id=' . $argsObj->target_user_id .
                    '&tplan_id=' . $argsObj->tplan_id;
  
  $tplMgr->assign('gui', $gui);
  $tplMgr->display('main.tpl');
}

/**
 * 
 *
 */
function buildCookie(&$dbHandler,$itemID,$tprojectID,$cookiePrefix)
{
  $tree_mgr = new tree($dbHandler);
  $path = $tree_mgr->get_path($itemID);
  $parents = array();
  $parents[] = $tprojectID;
  foreach($path as $node) 
  {
    $parents[] = $node['id'];
  }
  array_pop($parents);
  $cookieInfo['path'] = 'a:s%3A/' . implode("/", $parents);
  $cookieInfo['value'] = $cookiePrefix . $tprojectID . '_ext-comp-1001' ;
  return $cookieInfo;
}
