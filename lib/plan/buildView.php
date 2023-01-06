<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource buildView.php
 *
 *       
 *
 */
require('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false);

$tplCfg = templateConfiguration();


$gui = initEnv($db);

$context = new stdClass();
$context->tproject_id = $gui->tproject_id;
$context->tplan_id = $gui->tplan_id;

checkRights($db,$_SESSION['currentUser'],$context);

/**
 *
 */
function initEnv(&$dbHandler)
{
  $gui = new StdClass();

  $_REQUEST = strings_stripSlashes($_REQUEST);
  $gui->tplan_id = isset($_REQUEST['tplan_id']) 
                   ? intval($_REQUEST['tplan_id']) : 0;
  if( $gui->tplan_id == 0 ) {
    throw new Exception("Abort Test Plan ID == 0", 1);
  }  

  $tplan_mgr = new testplan($dbHandler);
  $build_mgr = new build_mgr($dbHandler);
  $info = $tplan_mgr->tree_manager->
            get_node_hierarchy_info($gui->tplan_id,null,array('nodeType' => 'testplan'));

  if( !is_null($info) ) {
    $gui->tplan_name = $info['name'];
  } else {
    throw new Exception("Invalid Test Plan ID", 1);
  }  
 
  $gui->tproject_id = intval($info['parent_id']);

  $gui->buildSet = $tplan_mgr->get_builds($gui->tplan_id);
  $gui->user_feedback = null;

  // To create the CF columns we need to get the linked CF
  $availableCF = [];
  if (!is_null($gui->buildSet)) {
    $availableCF = (array)$build_mgr->get_linked_cfields_at_design(current($gui->buildSet),$gui->tproject_id);
  }
  $hasCF = count($availableCF);
  $gui->cfieldsColumns = null; 
  $gui->cfieldsType = null;
  $initCFCol = true;

  // get CF used to configure HIDE COLS
  // We want different configurations for different test projects
  // then will do two steps algorithm
  // 1. get test project prefix PPFX
  // 2. look for TL_BUILDVIEW_HIDECOL_PPFX
  // 3. if found proceed
  // 4. else look for TL_BUILDVIEW_HIDECOL
  //  
  $ppfx = $tplan_mgr->tproject_mgr->getTestCasePrefix($gui->tproject_id);
  $suffixSet = ['_' . $ppfx, ''];     
  foreach($suffixSet as $suf) {
    $gopt['name'] = 'TL_BUILDVIEW_HIDECOL' . $suf;
    $col2hideCF = $tplan_mgr->cfield_mgr->get_linked_to_testproject($gui->tproject_id,null,$gopt);
   
    if ($col2hideCF != null) {
      $col2hideCF = current($col2hideCF);
      $col2hide = array_flip(explode('|',$col2hideCF['possible_values']));
      $col2hide[$gopt['name']] = '';
      break; 
    }
  }
  $localeDateFormat = config_get('locales_date_format');
  $localeDateFormat = $localeDateFormat[$_SESSION['currentUser']->locale];

  foreach($gui->buildSet as $elemBuild) {
    // ---------------------------------------------------------------------------------------------  
    $idk = current($elemBuild);
    if ($hasCF) {
      $cfields = (array)$build_mgr->getCustomFieldsValues($idk,$gui->tproject_id);        
      foreach ($cfields as $cfd) {
        if ($initCFCol) {
          if (!isset($col2hide[$cfd['name']])) {
            $gui->cfieldsColumns[] = $cfd['label'];
            $gui->cfieldsType[] = $cfd['type'];
          }
        }
        $gui->buildSet[$idk][$cfd['label']] = ['value' => $cfd['value'], 'data-order' => $cfd['value']];
        if ($cfd['type'] == 'date') {
          $gui->buildSet[$idk][$cfd['label']]['data-order'] = locateDateToISO($cfd['value'], $localeDateFormat);
        }          
      }  
      $initCFCol = false;
    }
    // ---------------------------------------------------------------------------------------------  
  }




  $cfg = getWebEditorCfg('build');
  $gui->editorType = $cfg['type'];
  
  return $gui;  
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($tplCfg->template_dir . $tplCfg->default_template);


/**
 *
 */
function checkRights(&$db,&$user,&$context)
{
  $context->rightsOr = [];
  $context->rightsAnd = ["testplan_create_build"];
  pageAccessCheck($db, $user, $context);
}