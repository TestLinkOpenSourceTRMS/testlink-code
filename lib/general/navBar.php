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

$args = init_args($db);
$gui = initializeGui($db,$args);

//var_dump($gui);
//die();

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display('navBar.tpl');


/**
 * 
 */
function getGrants(&$db,&$userObj)
{
  $grants = new stdClass();
  $grants->view_testcase_spec = $userObj->hasRightOnProj($db,"mgt_view_tc");
  return $grants;  
}

/**
 * 
 */
function init_args(&$dbH)
{
	$iParams = array("testproject" => array(tlInputParameter::INT_N),
                   "tproject_id" => array(tlInputParameter::INT_N),
                   "caller" => array(tlInputParameter::STRING_N,1,6),
                   "viewer" => array(tlInputParameter::STRING_N, 0, 3)
                  );
	$args = new stdClass();
	$pParams = G_PARAMS($iParams,$args);

  /*
  if( is_null($args->viewer) || $args->viewer == '' )
  {
    $args->viewer = isset($_SESSION['viewer']) ? $_SESSION['viewer'] : null;
  }  
  */
  $args->ssodisable = getSSODisable();
  $args->user = $_SESSION['currentUser'];

  $args->testproject = intval($args->testproject);
  $args->tproject_id = intval($args->tproject_id);

  // Check if any project exists to display error
  $args->newInstallation = false;
  if($args->testproject <= 0 || $args->tproject_id <= 0) {
    $sch = tlObject::getDBTables(
                       array('testprojects','nodes_hierarchy'));
    $sql = " SELECT NH.id, NH.name 
             FROM {$sch['nodes_hierarchy']} NH 
             JOIN {$sch['testprojects']} TPRJ 
             ON TPRJ.id = NH.id ";
    $rs = (array)$dbH->get_recordset($sql);

    if(count($rs) == 0) {
      $args->newInstallation = true;
    }  
  }  

	return $args;
}

/**
 *
 */
function initializeGui(&$db,&$args) {
  $tproject_mgr = new testproject($db);
  $guiCfg = config_get("gui");

  $gui = new stdClass();  

  $opx = array('output' => 'map_name_with_inactive_mark',
               'field_set' => $guiCfg->tprojects_combo_format,
               'order_by' => $guiCfg->tprojects_combo_order_by);

  $gui->TestProjects = 
    $tproject_mgr->get_accessible_for_user($args->user->dbID,$opx);

  $gui->TestProjectCount = sizeof($gui->TestProjects);
  if($gui->TestProjectCount == 0) {
    $gui->TestProjects = null;
  } 

  if ($args->tproject_id >0) {
    $gui->tprojectID = $args->tproject_id; 
  } else {
    $kiki = 'testprojectID';
    $gui->tprojectID = intval(isset($_SESSION[$kiki]) 
                              ? $_SESSION[$kiki] : 0);
  }
  $gui->tproject_id = $gui->tprojectID;

  if($gui->tproject_id <= 0 ) {
    $ckObj = new stdClass();
    $ckCfg = config_get('cookie');

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
    if( 0 == $gui->TestProjectCount ) {
      throw new Exception("Can't work without Test Project ID", 1);
    }
    $theOne = current(array_keys($gui->TestProjects));
    $gui->tproject_id = $gui->tprojectID = $theOne;
  }  

  $gui->tcasePrefix = '';
  $gui->searchSize = 8;
  $gui->tcasePrefix = 
     $tproject_mgr->getTestCasePrefix($gui->tproject_id) .
     config_get('testcase_cfg')->glue_character;
  $gui->searchSize = tlStringLen($gui->tcasePrefix) + 
                     $guiCfg->dynamic_quick_tcase_search_input_size;

  $gui->TestPlanCount = 0; 

  $tprojectQty = $tproject_mgr->getItemCount();  
  if($gui->TestProjectCount == 0 && $tprojectQty > 0) {
    // User rights configurations does not allow access to ANY test project
    $_SESSION['testprojectTopMenu'] = '';
    $gui->tproject_id = 0;
  }

  if($gui->tproject_id) {
    $testPlanSet = 
      (array)$args->user->getAccessibleTestPlans($db,$gui->tproject_id);
    $gui->TestPlanCount = sizeof($testPlanSet);

    $tplanID = isset($_SESSION['testplanID']) ? intval($_SESSION['testplanID']) : null;
    if( !is_null($tplanID) ) {
      // Need to set this info on session 
      // with first Test Plan from $testPlanSet
      // if this test plan is present on $testPlanSet
      //    OK we will set it on $testPlanSet as selected one.
      // else 
      //    need to set test plan on session
      //
      $index=0;
      $testPlanFound=0;
      $loop2do=count($testPlanSet);
      for($idx=0; $idx < $loop2do; $idx++) {
        if( $testPlanSet[$idx]['id'] == $tplanID ) {
          $testPlanFound = 1;
          $index = $idx;
          break;
        }
      }

      if( $testPlanFound == 0 && is_array($testPlanSet) &&  count($testPlanSet) > 0) {
        $tplanID = $testPlanSet[0]['id'];
        setSessionTestPlan($testPlanSet[0]);      
      } 
      $testPlanSet[$index]['selected']=1;
    }
  } 

  if ($gui->tproject_id && isset($args->user->tprojectRoles[$gui->tproject_id])) {
    // test project specific role applied
    $role = $args->user->tprojectRoles[$gui->tprojectID];
    $testprojectRole = $role->getDisplayName();
  } else {
    // general role applied
    $testprojectRole = $args->user->globalRole->getDisplayName();
  } 
  $gui->whoami = $args->user->getDisplayName() . ' ' . 
                 $guiCfg->role_separator_open . 
                 $testprojectRole . $guiCfg->role_separator_close;
                   

  // only when the user has changed project 
  // using the combo the _GET has this key.
  // Use this clue to launch a refresh of other 
  // frames present on the screen
  // using the onload HTML body attribute
  $gui->updateMainPage = 0;
  // if ($args->testproject > 0 || $args->tproject_id) {
  if ($gui->tproject_id >0) {
    // set test project ID for the next session
    $gui->updateMainPage = is_null($args->caller);

    $ckCfg = config_get('cookie');    
    $ckObj = new stdClass();
    $ckObj->name = $ckCfg->testProjectMemory . $args->user->dbID;
    $ckObj->value = $gui->tproject_id;
    tlSetCookie($ckObj);
  }

  $gui->grants = getGrants($db,$args->user);
  $gui->viewer = $args->viewer;

  $gui->plugins = array();
  foreach(array('EVENT_TITLE_BAR') as $menu_item) {
    $menu_content = event_signal($menu_item);
    $gui->plugins[$menu_item] = !empty($menu_content) ? $menu_content : null;
  }

  $gui->ssodisable = $args->ssodisable;
  $sso = ($args->ssodisable ? '&ssodisable' : '');  
  $gui->logout = 'logout.php?viewer=' . $sso;

  // to do not break logic
  $gui->testprojectID = $gui->tproject_id;
  return $gui;
}
