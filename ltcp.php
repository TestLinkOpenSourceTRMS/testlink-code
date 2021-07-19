<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 *
 *
 * 
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2021 TestLink community
 * @link        http://www.testlink.org/
 *
 */

require_once('lib/functions/configCheck.php');
checkConfiguration();
require_once('config.inc.php');
require_once('common.php');

doDBConnect($db);  // Because we do not use testlink init page
process($db);


/**
 *
 */
function process(&$dbHandler) 
{

  $_REQUEST = strings_stripSlashes($_REQUEST);
  $args = new stdClass();

  try {
    $userAPIkeyLen = 32;
    $iParams = ["apikey" => array(tlInputParameter::STRING_N,$userAPIkeyLen,$userAPIkeyLen),
                "testcase" => array(tlInputParameter::STRING_N,0,64)
               ];  
  } catch (Exception $e) {  
    echo $e->getMessage();
    exit();
  }
                  
  R_PARAMS($iParams,$args);
  $opt = array('setPaths' => true,'clearSession' => true);
  
  // validate apikey to avoid SQL injection
  $args->apikey = trim($args->apikey);
  $akl = strlen($args->apikey);  
  switch($akl) {
    case $userAPIkeyLen:
      $args->debug = 'USER-APIKEY';
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,null,$opt);
  
      // returns array element are arrays NOT USER OBJECT!!!
      $userSearch = tlUser::getByAPIKey($dbHandler,$args->apikey);
      $args->light = 'red';
      if (count($userSearch) == 1) {
        $args->light = 'green';
        $userData = current($userSearch);
        $user = new tlUser($userData['id']);
        $user->readFromDB($dbHandler);
      }  
    break;

    default:
     throw new Exception("Aborting - Bad API Key lenght", 1);
    break;  
  }

  $commonText = " - The call signature does not pass the system Checks - operation can not be fullfilled";
  if ($args->light == 'red') {
    echo "LTCP-01" . $commonText;
    die();    
  }


  // c94048220527a3d038db5c19e1156c08

  // need to extract testcase information
  // PREFIX-NUMBER-VERSION
  // example: PPT-8989-2
  //
  // Frome prefix we will get testproject info
  // in order to check user rights
  //
  // Trying to mitigate SQL injection I will get prefix of
  // all test projects then check array
  $tbl = DB_TABLE_PREFIX . 'testprojects';
  $sql = "SELECT prefix,id FROM $tbl ";
  $rs = $dbHandler->fetchRowsIntoMap($sql,'prefix');
  $testCasePieces = explode('-',$args->testcase);

  if (count($testCasePieces) != 3) {
    echo "LTCP-02" . $commonText;
    die();
  }

  $prjPrefix = trim($testCasePieces[0]);
  if (!isset($rs[$prjPrefix])) {
    echo "LTCP-03" . $commonText;
    die();
  }

  $tproject_id = intval($rs[$prjPrefix]['id']); 

  // Check rights on test project
  $canRead =  $user->hasRight($dbHandler,"mgt_view_tc",$tproject_id,null,("getAccess"=="getAccess"));
  if ($canRead == false) {
    echo "LTCP-04 - System Checks do not allow operation requested";
    die();    
  }  

  // ---------------------------------------------------------------------------------------------------- 
  // everything is OK, now need to launch
  // https://<your install>/lib/testcases/tcPrint.php?show_mode=&testcase_id=72510&tcversion_id=72511
  //
  $externalID = $testCasePieces[0] . '-' . $testCasePieces[1]; 
  $tcaseMgr = new testcase($dbHandler);
  $testcase_id = $tcaseMgr->getInternalID($externalID); 
  $allTCVID = $tcaseMgr->getAllVersionsID($testcase_id);
  $idSet = implode(',', $allTCVID);
  $tcaseVersionNumber = intval($testCasePieces[2]);
  $tbl = DB_TABLE_PREFIX . 'tcversions';
  $sql = " SELECT version,id FROM $tbl
           WHERE id IN ($idSet) 
           AND version = $tcaseVersionNumber"; 
  $rs = (array)$dbHandler->fetchRowsIntoMap($sql,'version');
  if (count($rs) != 1) {
    die();
  }
  $tcversion_id = intval($rs[$tcaseVersionNumber]['id']);
  
  $url2call = "testcase_id=%TC%&tcversion_id=%TCV%";
  $url2call = str_replace(["%TC%","%TCV%"],[$testcase_id,$tcversion_id],$url2call);
  // ---------------------------------------------------------------------------------------------------- 
  $what2launch = "/lib/testcases/tcPrint.php?$url2call";
  header('Location:' . TL_BASE_HREF . $what2launch);
  exit();
}