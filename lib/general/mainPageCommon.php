<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  mainPageCommon.php
 * 
 * Page has two functions: navigation and select Test Plan
 *
 * This file is the first page that the user sees when they log in.
 * Most of the code in it is html but there is some logic that displays
 * based upon the login. 
 * There is also some javascript that handles the form information.
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');

/**
 *
 */
function main(&$db,&$args) {

  ////echo '<br>' . __FILE__ . '> ' . __LINE__;
  $opt = array('forceCreateProj' => $args->newInstallation,
               'caller' => basename(__FILE__));
  list($add2args,$gui) = initUserEnv($db,$args,$opt);

  ////echo '<br>' . __FILE__ . '> ' . __LINE__;

  // 20201022
  if($args->activeMenu != '') {
    $gui->activeMenu[$args->activeMenu] = 'active';
  }
  ////echo '<br>' . __FILE__ . '> ' . __LINE__;

  $k2l = get_object_vars($add2args);
  foreach($k2l as $prop => $pval) {
    $args->$prop = $pval;
  }
  ////echo '<br>' . __FILE__ . '> ' . __LINE__;

  $tprjMgr = new testproject($db);
  $gui->tprjOpt = $tprjMgr->getOptions($gui->tproject_id);
  $gui->testPriorityEnabled = $gui->tprjOpt->testPriorityEnabled;
  $gui->tc_monthly_creation_rate_on_tproj = 
    lang_get('tc_monthly_creation_rate_on_tproj') . ' - ' .
    testproject::getName($db,$gui->tproject_id);

  ////echo '<br>' . __FILE__ . '> ' . __LINE__;

  $gui->showMenu['requirements_design'] =
    $gui->showMenu['requirements_design'] && 
    $gui->tprjOpt->requirementsEnabled;
  ////echo '<br>' . __FILE__ . '> ' . __LINE__;

  $gui->plugins = array();
  foreach(array('EVENT_LEFTMENU_TOP',
                'EVENT_LEFTMENU_BOTTOM',
                'EVENT_RIGHTMENU_TOP',
                'EVENT_RIGHTMENU_BOTTOM') as $menu_item) {
    # to be compatible with PHP 5.4
    $menu_content = event_signal($menu_item);
    if( !empty($menu_content) ) {
      $gui->plugins[$menu_item] = $menu_content;
    }
  }

  //echo '<br> >> ' . __FILE__ . '> ' . __LINE__;
  //echo '<br> // ' . __FILE__ . '> ' . __LINE__;
  //echo '<br> ** ' . __FILE__ . '> ' . __LINE__;
  //die();

  $tplKey = 'mainPage';
  $tpl = $tplKey . '.tpl';
  $tplCfg = config_get('tpl');
  if ( null !== $tplCfg && isset($tplCfg[$tplKey]) ) {
    $tpl = $tplCfg->$tplKey;
  } 

  createDashboard($db,$gui);
  ////echo '<br>' . __FILE__ . '> ' . __LINE__;
  
  //echo " <br><br>((((( $tpl";
  $smarty = new TLSmarty();
  // var_dump($smarty);

  $smarty->assign('gui',$gui);
  $smarty->display($tpl);
}



/**
 *
 */
function initArgs(&$dbH) {
  $iParams = array("testproject" => array(tlInputParameter::INT_N),
                   "tproject_id" => array(tlInputParameter::INT_N),
                   "current_tproject_id" => array(tlInputParameter::INT_N),
                   "tplan_id" => array(tlInputParameter::INT_N),
                   "caller" => array(tlInputParameter::STRING_N,1,6),
                   "viewer" => array(tlInputParameter::STRING_N, 0, 3),
                   "activeMenu" => array(tlInputParameter::STRING_N,6,20),
                   "projectView" => array(tlInputParameter::INT_N));
  $args = new stdClass();
  $pParams = G_PARAMS($iParams,$args);

  // Need to understand @20190302
  if( is_null($args->viewer) || $args->viewer == '' ) {
    $args->viewer = isset($_SESSION['viewer']) ? $_SESSION['viewer'] : null;
  }  

  $args->ssodisable = getSSODisable();
  $args->user = $_SESSION['currentUser'];

  // Check if any project exists to display error
  $args->newInstallation = false;

  if( $args->tproject_id == 0 ) {
    $args->tproject_id = $args->testproject;
  }
  $args->tproject_id = intval($args->tproject_id);


  $sch = tlObject::getDBTables(array('testprojects','nodes_hierarchy'));
  $sql = " SELECT NH.id FROM {$sch['nodes_hierarchy']} NH " .
         " JOIN {$sch['testprojects']} TPRJ " .
         " ON TPRJ.id = NH.id ";
  $rs = (array)$dbH->get_recordset($sql);

  if (count($rs) == 0) {
    $args->newInstallation = true;
  } else if ($args->tproject_id >0){
    $sql = " SELECT NH.id FROM {$sch['nodes_hierarchy']} NH
             JOIN {$sch['testprojects']} TPRJ 
             ON TPRJ.id = NH.id WHERE TPRJ.id = {$args->tproject_id}";
    $rs = (array)$dbH->get_recordset($sql);
    if (count($rs) == 0) {
      throw new Exception("Error Test Project ID does not exist", 1);
    }
  }  

  // 20201022
  $items = getFirstLevelMenuStructure();
  $args->activeMenu = trim($args->activeMenu);
  if (!isset($args->activeMenu) ) {
    $args->activeMenu = '';
  }

  return $args;
}



/**
 * Get User Documentation 
 * based on contribution by Eugenia Drosdezki
 */
function getUserDocumentation() {
  $target_dir = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'docs';
  $documents = null;
    
  if ($handle = opendir($target_dir))  {
    while (false !== ($file = readdir($handle))) {
      clearstatcache();
      if (($file != ".") && ($file != "..")) {
        if (is_file($target_dir . DIRECTORY_SEPARATOR . $file)) {
          $documents[] = $file;
        }    
      }
    }
    closedir($handle);
  }
  return $documents;
}


/**
 *
 */
function createDashboard(&$dbH,&$guiO)
{
  $guiO->dashboard = new stdClass();
  $guiO->dashboard->chart = '';
  $guiO->dashboard->yAxis = '<ul class="y-axis">
                              <li><span>500</span></li>
                              <li><span>400</span></li>
                              <li><span>300</span></li>
                              <li><span>200</span></li>
                              <li><span>100</span></li>
                              <li><span>0</span></li>
                             </ul>';

  if ($guiO->tproject_id != 0) {
    // Generate empty structures
    $thisYear = date('Y');
    $chartValues = array();
    for ($idx=1; $idx <=12; $idx++) {
      $chartValues[$thisYear . '_' . sprintf('%02d',$idx)] = 0;
    }
    $tprojMgr = new testproject($dbH);
    $rs = $tprojMgr->getTCQtyCreatedMonthly($guiO->tproject_id);
  }

  if ($guiO->tproject_id != 0 && null != $rs) {
   
    ksort($rs);
    foreach ($chartValues as $key => $val) {
      if (isset($rs[$key])) {
        $chartValues[$key] = $rs[$key];
      }
    }


    $qty = array_flip($rs);
    krsort($qty);
    $flipper = array_flip($qty);
    $maxQty = reset($flipper);
    $maxY = pow(10,strlen($maxQty));
    $top = $maxY;
    $step = 0.2 * $maxY;
    $guiO->dashboard->yAxis = '<ul class="y-axis"> ';
    if ($top < 10) {
      $top = 10;
      $step = 0.2 * $top;
    } 
    while ($top >0) {
      $guiO->dashboard->yAxis .= "<li><span>{$top}</span></li>";      
      $top -=$step;
    }
    
    $guiO->dashboard->yAxis .= '</ul>';

    $guiO->dashboard->chart = '';    
    foreach ($chartValues as $yyyy_mm => $qty) {
      $pieces = explode('_',$yyyy_mm); 
      $guiO->dashboard->chart .= 
         '<div class="bar">
                <div class="title">' . $pieces[1] . 
                ' </div>
                <div class="value tooltips" 
                     data-original-title="' . 
                     $qty . '"' . 
                     ' data-toggle="tooltip" 
                       data-placement="top">' . (($qty/$maxY) * 100) .
                '%</div></div>';  

    }    
  }

}