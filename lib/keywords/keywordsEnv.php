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
function getKeywordsEnv(&$dbHandler,&$user,$tproject_id,$opt=null) {
  $kwEnv = new stdClass();

  $options = array('usage' => null);
  $options = array_merge($options,(array)$opt);

  $tproject = new testproject($dbHandler);
  $kwEnv->keywords = $tproject->getKeywords($tproject_id);

  $kwEnv->kwExecStatus = null;
  $kwEnv->kwFreshStatus = null;
  $kwEnv->kwOnTCV = null;

  if( null != $kwEnv->keywords ) {
    $kws = array();
    $kwNames = array();
    $kwNotes = array();
    $more = ($options['usage'] == 'csvExport');

    foreach( $kwEnv->keywords as $kwo ) {
      $kws[] = $kwo->dbID;
      if( $more ) {
        $kwNames[$kwo->dbID] = $kwo->name;
        $kwNotes[$kwo->dbID] = $kwo->notes;        
      }
    }

    // Count how many times the keyword has been used
    $kwEnv->kwOnTCV = (array)$tproject->countKeywordUsageInTCVersions($tproject_id);
    if( $more && count($kwEnv->kwOnTCV) > 0) {
      foreach($kwEnv->kwOnTCV as $kk => $dummy) {
        $kwEnv->kwOnTCV[$kk]['keyword'] = $kwNames[$kk];
        $kwEnv->kwOnTCV[$kk]['notes'] = $kwNotes[$kk];        
      }
    }

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

  $kwEnv->editUrl = $_SESSION['basehref'] . 
    "lib/keywords/keywordsEdit.php?" . "tproject_id={$tproject_id}"; 

  return $kwEnv;  
}


/**
 */
function setOpenByAnotherEnv(&$argsObj) {

  $argsObj->dialogName = '';
  $argsObj->bodyOnLoad = $argsObj->bodyOnUnload = '';       
  if(isset($_REQUEST['openByKWInc'])) {
    $argsObj->openByOther = 1;
  } else {
    // Probably useless
    $argsObj->openByOther = 
      isset($_REQUEST['openByOther']) ? intval($_REQUEST['openByOther']) : 0;
    if( $argsObj->openByOther ) {
      $argsObj->dialogName = 'kw_dialog';
      $argsObj->bodyOnLoad = "dialog_onLoad($argsObj->dialogName)";
      $argsObj->bodyOnUnload = "dialog_onUnload($args->dialogName)";  
    }    
  }
}

