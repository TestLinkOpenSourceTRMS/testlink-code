<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource  navBar.php
 *
 * Manages the navigation bar. 
 *
 *
**/
require_once('../../config.inc.php');
require_once("common.php");

testlinkInitPage($db,('initProject' == 'initProject'));
$context = null;
$args = init_args($db,$context);
$gui = initializeGui($db,$args,$context);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display('navBar.tpl');

/**
 * 
 */
function init_args(&$dbH,$context) {
	$iParams = array("testproject" => array(tlInputParameter::INT_N),
                   "tproject_id" => array(tlInputParameter::INT_N),
                   "caller" => array(tlInputParameter::STRING_N,1,6),
                   "viewer" => array(tlInputParameter::STRING_N, 0, 3),
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "updateMainPage" => array(tlInputParameter::INT_N)
                  );
	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);

  $args->ssodisable = getSSODisable();
  $args->user = $_SESSION['currentUser'];

  // Check if any project exists to display error
  $args->newInstallation = false;
  $args->testProject = intval($args->tproject_id);

  if($args->testProject <= 0) {
    $sch = tlObject::getDBTables(array('testprojects','nodes_hierarchy'));
    $sql = " SELECT NH.id, NH.name FROM {$sch['nodes_hierarchy']} NH " .
           " JOIN {$sch['testprojects']} TPRJ " .
           " ON TPRJ.id = NH.id ";
    $rs = (array)$dbH->get_recordset($sql);

    if(count($rs) == 0) {
      $args->newInstallation = true;
    } 
  }  

  // CRITICAL PIECE
  if (null != $context) {
    $key = array('tproject_id', 'tplan_id');
    foreach ($key as $ctx_id) {
      if ($args->$ctx_id <= 0) {
        $args->$ctx_id = $context->$ctx_id;      
      }
    }
  }
  $args->testProject = intval($args->tproject_id);

  // Check if we can try to get tproject_id from memory
  /*
  if ( $args->tproject_id == 0 && $args->newInstallation == false) {
    $ckObj = new stdClass();
    $ckCfg = config_get('cookie');

    // Try to get from Cookie
    $ckObj->name = $ckCfg->testProjectMemory . 
                   intval($_SESSION['userID']);

    if( isset($_COOKIE[$ckObj->name]) ) {
      $args->testProject = $args->tproject_id = intval($_COOKIE[$ckObj->name]);
    }  
  }
  */

	return $args;
}

/**
 *
 */
function initializeGui(&$db,&$args,$context) {

  $guiCfg = config_get("gui");
  $gui = new stdClass();

  $opt = array('forceCreateProj' => false,
               'skip' => array('tplanForInit' => true),
               'caller' => 'navBar',
               'initNavBarMenu' => true);

  list($add2args,$gui,$tproject_mgr) = initUserEnv($db, $context, $opt); 
  if ($gui->tproject_id > 0) {
     if ($args->tproject_id > 0) {
       $gui->tproject_id = $gui->tprojectID = $args->tproject_id;
     } else {
       $args->tproject_id = $gui->tprojectID = $gui->tproject_id;
     }  
  }  

  $gui->testProjects = $gui->prjSet;  
  $gui->testProjectCount = sizeof($gui->testProjects);
  if($gui->testProjectCount == 0) {
    $gui->testProjects = null;
    $gui->tproject_id = $gui->tprojectID = 0;
  } 

  $ckCfg = config_get('cookie');

  if( $gui->tproject_id <= 0 ) {
    $ckObj = new stdClass();

    // Try to get from Cookie
    $ckObj->name = $ckCfg->testProjectMemory . 
                   intval($_SESSION['userID']);

    if( isset($_COOKIE[$ckObj->name]) ) {
      $gui->tproject_id = $gui->tprojectID = intval($_COOKIE[$ckObj->name]);
    }  
  }

  if($gui->tproject_id <= 0 && !$args->newInstallation) {
    // Well instead of this, try to get the firts test project 
    // user is enabled to.
    if( 0 == $gui->testProjectCount ) {
      throw new Exception("Can't work without Test Project ID", 1);
    }
    $theOne = key($gui->testProjects);
    $gui->tproject_id = $gui->tprojectID = $theOne;
  }  

  $gui->tcasePrefix = '';
  $gui->searchSize = 8;
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($gui->tproject_id) . config_get('testcase_cfg')->glue_character;

  $gui->searchSize = tlStringLen($gui->tcasePrefix) + 
                     $guiCfg->dynamic_quick_tcase_search_input_size;

  if ($gui->testProjectCount == 0 && (false == $gui->zeroTestProjects)) {
    // User rights configurations does not allow 
    // access to ANY test project
    $gui->topMenu = '';
    $gui->tproject_id = 0;
  }

  if($gui->tproject_id > 0) {
    $gui->testPlans = (array)$args->user->getAccessibleTestPlans($db,$gui->tproject_id);
    $gui->testPlanCount = sizeof($gui->testPlans);

    $gui->tplan_id = $args->tplan_id;
    if( !is_null($gui->tplan_id) &&  $gui->tplan_id >0 ) {
      $index=0;
      $testPlanFound=0;
      $loop2do=count($gui->testPlans);
      for($idx=0; $idx < $loop2do; $idx++) {
        if( $gui->testPlans[$idx]['id'] == $gui->tplan_id ) {
          $testPlanFound = 1;
          $index = $idx;
          break;
        }
      }

      if( $testPlanFound == 0 && is_array($gui->testPlans) ) {
        $gui->tplan_id = $gui->testPlans[0]['id'];
      } 
      $gui->testPlans[$index]['selected']=1;
    }  
  }

  // only when the user has changed project using 
  // the combo the _GET has this key.
  // Use this clue to launch a refresh of other 
  // frames present on the screen
  // using the onload HTML body attribute
  // 20201022 $gui->updateMainPage = 0;


  $gui->updateMainPage = $args->updateMainPage;
  if ($gui->updateMainPage == 0) {
    if( $gui->tproject_id > 0) {
      // set test project ID for the next session
      $gui->updateMainPage = is_null($args->caller);

      $ckObj = new stdClass();
      $ckObj->name = $ckCfg->testProjectMemory . $args->user->dbID;
      $ckObj->value = $args->testProject;
      tlSetCookie($ckObj);
    }
  }
  $gui->updateNavBar = $gui->updateMainPage;

  $gui->viewer = $args->viewer;

  $gui->plugins = array();
  foreach(array('EVENT_TITLE_BAR') as $menu_item) {
    $menu_content = event_signal($menu_item);
    $gui->plugins[$menu_item] = !empty($menu_content) ? $menu_content : null;
  }

  $gui->ssodisable = $args->ssodisable;
  $sso = ($args->ssodisable ? '&ssodisable' : '');  
  $gui->logout = 'logout.php?viewer=' . $sso;

  // to do not break logic, it will be better to remove this
  $gui->testProjectID = $gui->tproject_id;  
  return $gui;
}
