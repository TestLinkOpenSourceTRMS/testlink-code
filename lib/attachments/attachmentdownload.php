<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Downloads the attachment by a given id
 *
 * @filesource attachmentdownload.php
 *
 */
@ob_end_clean();
require_once('../../config.inc.php');
#require_once('../functions/common.php');
#require_once('../functions/attachments.inc.php');
require_once('common.php');
require_once('attachments.inc.php');


// This way can be called without _SESSION, 
// this is useful for reports when using access without login
testlinkInitPage($db,false,true);

$args = init_args($db);
if ($args->id) {
  $docRepo = tlAttachmentRepository::create($db);
  $docInfo = $docRepo->getAttachmentInfo($args->id);

  //file_put_contents("/var/testlink/log.log", __LINE__ . ' ' . $args->id . PHP_EOL,FILE_APPEND);
  //file_put_contents("/var/testlink/log.log", __LINE__ . ' ' . json_encode($docInfo). PHP_EOL,FILE_APPEND);
 

  if ($docInfo) {
    switch ($args->opmode)  {
      case 'API':
        // want to check if apikey provided is right for attachment context
        // - test project api key:
        //   is needed to get attachments for:
        //   test specifications
        //
        // - test plan api key:
        //   is needed to get attacments for:
        //   test case executions
        //   test specifications  ( access to parent data - OK!)
        //   
        // What kind of attachments I've got ?
        $doIt = false;
        $attContext = $docInfo['fk_table'];
        switch ($attContext) {
          case 'executions':
            // check apikey
            // 1. has to be a test plan key
            // 2. execution must belong to the test plan.
            $item = getEntityByAPIKey($db,$args->apikey,'testplan');
            if (!is_null($item)) {
              $tables = tlObjectWithDB::getDBTables(array('executions'));
              $sql = "SELECT testplan_id FROM {$tables['executions']} " .
                     "WHERE id = " . intval($docInfo['fk_id']);

              $rs = $db->get_recordset($sql);
              if (!is_null($rs)) {
                if($rs['0']['testplan_id'] == $item['id']) {
                  // GOOD !
                  $doIt = true;
                }  
              }       
            }  
          break;
        }
      break;
      
      case 'GUI':
      default:   
        // Create _SESSION  to be able to check access
        // 
        $doIt = false;
        doSessionStart();
        checkSessionValid($db);
        
        $user = $_SESSION['currentUser'];
        $fk_table = $docInfo['fk_table'];
        $attContext = $fk_table;
        $attParent = intval($docInfo['fk_id']);
        if (DB_TABLE_PREFIX != '') {
          $attContext = str_replace(DB_TABLE_PREFIX, '', $attContext);
        }

        $tbl = tlObject::getDBTables('testplans','tesuites','builds');

        // file_put_contents("/var/testlink/log.log", __LINE__ . ' ' . $fk_table. PHP_EOL,FILE_APPEND);
        // file_put_contents("/var/testlink/log.log", __LINE__ . ' ' . $attContext . PHP_EOL,FILE_APPEND);
        
         switch ($attContext) {
          case 'executions':
            $sql = "SELECT E.testplan_id, TPL.testproject_id
                    FROM executions E 
                    JOIN {$tbl['testplans']} TPL 
                    ON TPL.id = E.testplan_id
                    WHERE E.id = {$attParent}"; 
            $rs = $db->get_recordset($sql);
          break;

          case 'tcversions':
            $tree = new tree($db);
            $ctx = array('tproject_id' => null,'tplan_id' => null,
                         'checkPublicPrivateAttr' => true);
            $ctx['tproject_id'] = $tree->getTreeRoot($attParent);
            $ck = array('mgt_view_tc','mgt_modify_tc');
          break;

          case 'execution_steps':
            $sql = "SELECT E.testplan_id, TPL.testproject_id
                    FROM executions E 
                    JOIN execution_steps ES
                    ON E.id = ES.execution_id
                    JOIN {$tbl['testplans']} TPL 
                    ON TPL.id = E.testplan_id
                    WHERE E.id = {$attParent}"; 
            $rs = $db->get_recordset($sql);
            $rs = $rs[0];
            $ctx = array('tproject_id' => $rs[0]['testproject_id'],
                         'tplan_id' => $rs[0]['testplan_id'],
                         'checkPublicPrivateAttr' => true);
            $ck = array('testplan_execute','exec_ro_access',
                        'exec_testcases_assigned_to_me');
          break;

          case 'testplans':
            list($ctx,$ck) = 
              getContextForTestPlan($db,$tbl,$attParent);
          break;


          case 'nodes_hierarchy':
            list($ctx,$ck) = 
              getContextForNodesHierarchy($db,$tbl,$attParent);
          break;
        }

        // Execute the checks
        foreach ($ck as $grantToCheck) {
          $doIt = $user->hasRightWrap($db,$grantToCheck,$ctx);
          if ($doIt) {
             break;
          }
        }

        //file_put_contents("/var/testlink/log.log", json_encode($user->dbID),FILE_APPEND);

      break;
    }


    if( $doIt ) {
      $content = '';
      $getContent = true;
      if( $args->opmode !== 'API' && $args->skipCheck !== 0 
          && $args->skipCheck !== false) {
        if( $args->skipCheck != hash('sha256',$docInfo['file_name']) ) {
          $getContent = false;
        }  
      }  

      if ($getContent) {
        $content = $docRepo->getAttachmentContent($args->id,$docInfo);
      }  

      if ($content != "" ) {
        @ob_end_clean();
        header('Pragma: public');
        header("Cache-Control: ");
        if (!(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" && preg_match("/MSIE/",$_SERVER["HTTP_USER_AGENT"]))) { 
          header('Pragma: no-cache');
        }
        header('Content-Type: '.$docInfo['file_type']);
        header('Content-Length: '.$docInfo['file_size']);
        header("Content-Disposition: inline; filename=\"{$docInfo['file_name']}\"");
        header("Content-Description: Download Data");
        echo $content;
        exit();
      }      
    }  
  }
}

$smarty = new TLSmarty();
$smarty->assign('gui',$args);
$smarty->display('attachment404.tpl');

/**
 * 
 */
function init_args(&$dbHandler)
{
  // id (attachments.id) of the attachment to be downloaded
  $iParams = array('id' => array(tlInputParameter::INT_N),
                   'apikey' => array(tlInputParameter::STRING_N,64),  
                   'skipCheck' => array(tlInputParameter::STRING_N,1,64));
  
  $args = new stdClass();
  G_PARAMS($iParams,$args);

  $args->light = 'green';
  $args->opmode = 'GUI';
  if( is_null($args->skipCheck) || $args->skipCheck === 0 ) {
    $args->skipCheck = false;
  }  

  // using apikey lenght to understand apikey type
  // 32 => user api key
  // other => test project or test plan
  $args->apikey = trim($args->apikey);
  $apikeyLenght = strlen($args->apikey);
  if ($apikeyLenght > 0) {
    $args->opmode = 'API';
    $args->skipCheck = true;
  } 
  return $args;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
  return (config_get("attachments")->enabled);
}

/**
 *
 */
function console_log($output, $with_script_tags = true) {
  $js_code = 'console.log(' . 
             json_encode($output, JSON_HEX_TAG) . ');';

  if ($with_script_tags) {
    $js_code = '<script>' . $js_code . '</script>';
  }
  echo $js_code;
}


/**
 *
 */
function getContextForTestPlan(&$dbH,&$tbl,$id) 
{
  $sql = "SELECT TPL.id AS testplan_id, 
          TPL.testproject_id
          FROM {$tbl['testplans']} TPL 
          WHERE TPL.id = $id"; 

  $rs = $dbH->get_recordset($sql);
  $rs = $rs[0];
  $ctx = array('tproject_id' => $rs['testproject_id'],
               'tplan_id' => $rs['testplan_id'],
               'checkPublicPrivateAttr' => true);
  $ck = array('testplan_execute','exec_ro_access',
              'exec_testcases_assigned_to_me');

  return array($ctx,$ck);
}

/**
 *
 */
function getContextForNodesHierarchy(&$dbH,&$tbl,$id) 
{
  $tree = new tree($dbH);
  $ni = $tree->get_node_hierarchy_info($id);
  $nt = array_flip($tree->get_available_node_types());
  $nv = $nt[$ni['node_type_id']]; 

  // echo $nv; die();

  $ck = [
    'testplan_execute',
    'exec_ro_access',
    'exec_testcases_assigned_to_me'
  ];

  
  switch ($nv) {
    case 'testproject':
      $ctx = [
        'tproject_id' => $id,
        'tplan_id' => null,
        'checkPublicPrivateAttr' => true
      ];
      $ck = [
        'mgt_view_tc',
        'mgt_modify_tc'
      ];
    break;

    case 'testsuite':
      $ctx = [
        'tproject_id' => $tree->getTreeRoot($id),
        'tplan_id' => null,
        'checkPublicPrivateAttr' => true
      ];
      $ck = [
        'mgt_view_tc',
        'mgt_modify_tc'
      ];
    break;

    case 'build':
      $sql = "SELECT B.testplan_id, TPL.testproject_id
              FROM {$tlb['builds']} B 
              JOIN {$tbl['testplans']} TPL 
              ON TPL.id = B.testplan_id
              WHERE B.id = $id"; 
      $rs = $dbH->get_recordset($sql);
      $rs = $rs[0];
      $ctx = [
        'tproject_id' => $rs[0]['testproject_id'],
        'tplan_id' => $rs[0]['testplan_id'],
        'checkPublicPrivateAttr' => true
      ];
      $ck = [
        'testplan_execute',
        'exec_ro_access',
        'exec_testcases_assigned_to_me'
      ];
    break;

    case 'testplan':
      list($ctx,$ck) = getContextForTestPlan($db,$tbl,$id);
    break;
  } 

  return array($ctx,$ck);
}

