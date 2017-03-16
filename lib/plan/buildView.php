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
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();



$gui = initEnv($db);

function initEnv(&$dbHandler)
{
  $gui = new StdClass();

  $_REQUEST = strings_stripSlashes($_REQUEST);

  

  $gui->tplan_id = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;
  if( $gui->tplan_id == 0 )
  {
    throw new Exception("Abort Test Plan ID == 0", 1);
  }  

  $tplan_mgr = new testplan($dbHandler);
  $info = $tplan_mgr->tree_manager->
            get_node_hierarchy_info($gui->tplan_id,null,array('nodeType' => 'testplan'));

  if( !is_null($info) )
  {
    $gui->tplan_name = $info['name'];
  }  
  else
  {
    throw new Exception("Invalid Test Plan ID", 1);
  }  
 
  $gui->buildSet = $tplan_mgr->get_builds($gui->tplan_id);
  $gui->user_feedback = null;

  $cfg = getWebEditorCfg('build');
  $gui->editorType = $cfg['type'];
  
  return $gui;  
}

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_create_build');
}
