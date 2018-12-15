<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource: keywordsView.php
 *
 * utilities functions 
 */

/**
 */
function getKeywordsEnv(&$dbHandler,&$user,$tproject_id) {
  $kwEnv = new stdClass();

  $tproject = new testproject($dbHandler);
  $kwEnv->keywords = $tproject->getKeywords($tproject_id);

  $kwEnv->kwExecStatus = null;
  $kwEnv->kwFreshStatus = null;
  $kwEnv->kwOnTCV = null;

  if( null != $kwEnv->keywords ) {
    $kws = array();
    foreach( $kwEnv->keywords as $kwo ) {
      $kws[] = $kwo->dbID;
    }

    // Count how many times the keyword has been used
    $kwEnv->kwOnTCV = $tproject->countKeywordUsageInTCVersions($tproject_id);

    $kwCfg = config_get('keywords');

    if( $kwCfg->onDeleteCheckExecutedTCVersions ) {
      $kwEnv->kwExecStatus = 
        $tproject->getKeywordsExecStatus($kws,$tproject_id);        
    }

    if( $kwCfg->onDeleteCheckFrozenTCVersions ) {
      $kwEnv->kwFreshStatus = 
        $tproject->getKeywordsFreezeStatus($kws,$tproject_id);  
    }

  }

  $kwEnv->canManage = $user->hasRight($dbHandler,"mgt_modify_key",$tproject_id);
  $kwEnv->canAssign = $user->hasRight($dbHandler,"keyword_assignment",$tproject_id);

  $kwEnv->editUrl = $_SESSION['basehref'] . "lib/keywords/keywordsEdit.php?" .
                   "tproject_id={$tproject_id}"; 
  return $kwEnv;
}