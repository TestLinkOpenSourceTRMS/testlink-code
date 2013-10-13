<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: control test specification import
 *
 * @filesource  tcCreateFromIssueFromMantisXML.php
 * @package     TestLink
 * @copyright   2007-2013, TestLink community 
 * @link        http://www.teamst.org/index.php
 * 
 *
 *
 * <?xml version="1.0" encoding="UTF-8"?>
 *  <mantis version="1.2.14" urlbase="http://localhost/development/closet/mantisbt-1.2.14/" issuelink="#" notelink="~" format="1">
 *   <issue>
 *       <id>21</id>
 *       <project id="1">testlink-test</project>
 *       <reporter id="1">administrator</reporter>
 *       <priority id="30">normal</priority>
 *       <severity id="50">minor</severity>
 *       <reproducibility id="70">have not tried</reproducibility>
 *       <status id="10">new</status>
 *       <resolution id="10">open</resolution>
 *       <projection id="10">none</projection>
 *       <category id="2">FromTestLink</category>
 *       <date_submitted>1365184242</date_submitted>
 *       <last_updated>1365184242</last_updated>
 *       <eta id="10">none</eta>
 *       <target_version>V1</target_version>
 *       <view_state id="10">public</view_state>
 *       <summary>ISSUE-V1</summary>
 *       <due_date>1</due_date>
 *       <description>ISSUE-V1</description>
 *   </issue>
 *   <issue>
 *       <id>20</id>
 *       <project id="1">testlink-test</project>
 *
 * @internal revisions
 * @since 1.9.7
 *
 */
require('../../config.inc.php');
require_once('common.php');
require_once('xml.inc.php');


testlinkInitPage($db);
$templateCfg = templateConfiguration();
$pcheck_fn=null;
$args = init_args();
$gui = initializeGui($db,$args);

if ($args->do_upload)
{
  $source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
  tLog('Uploaded file: '.$source);
  $doIt = false;
  $gui->file_check = null;
  if (($source != 'none') && ($source != ''))
  { 
    // ATTENTION:
    // MAX_FILE_SIZE hidden input is defined on form, but anyway we do not get error at least using
    // Firefox and Chrome.
    if( !($doIt = $_FILES['uploadedFile']['size'] <= $gui->importLimitBytes) )
    {
      $gui->file_check['status_ok'] = 0;
      $gui->file_check['msg'] = sprintf(lang_get('file_size_exceeded'),$_FILES['uploadedFile']['size'],$gui->importLimitBytes);
    }
  }
  if($doIt)
  { 
    $gui->file_check['status_ok'] = 1;
    if (move_uploaded_file($source, $gui->dest))
    {
      tLog('Renamed uploaded file: ' . $source);
    }
    tLog('Check is Ok.');
    $opt = array();
    $gui->resultMap = importIssueFromXML($db,$gui->dest,intval($args->container_id),
                                         intval($args->tproject_id),intval($args->userID),$opt);
  }
  else if(is_null($gui->file_check))
  {
    
    tLog('Missing upload file','WARNING');
    $gui->file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
    $args->importType = null;
  }
}


$gui->testprojectName = $args->tproject_name;
$gui->importTypes = array('XML' => 'Mantis XML');

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


// --------------------------------------------------------------------------------------
/*
  function: 
  args :
  returns: 
*/
function importIssueFromXML(&$db,$fileName,$parentID,$tproject_id,$userID,$options=null)
{
  $xmlTCs = null;
  $resultMap  = null;
  $my = array();
  $my['options'] = array('useRecursion' => false, 'importIntoProject' => 0,
                         'duplicateLogic' => array('hitCriteria' => 'name', 'actionOnHit' => null)); 
  $my['options'] = array_merge($my['options'], (array)$options);
  foreach($my['options'] as $varname => $value)
  {
    $$varname = $value;
  }
  
  if (file_exists($fileName))
  {
    $xml = @simplexml_load_file_wrapper($fileName);
    if($xml !== FALSE)
    {
      $resultMap = importTestCasesFromIssueSimpleXML($db,$xml,$parentID,$tproject_id,$userID,null,$duplicateLogic);
    }
  }
  return $resultMap;
}


// --------------------------------------------------------------------------------------
/*
  function: saveImportedTCData
  args :
  returns: 
*/
function saveImportedTCData(&$db,$tcData,$tproject_id,$container_id,
                            $userID,$kwMap,$duplicatedLogic = array('hitCriteria' => 'name', 'actionOnHit' => null))
{
  static $messages;
  static $fieldSizeCfg;
  static $feedbackMsg;
  static $tcase_mgr;
  static $tproject_mgr;
  static $req_spec_mgr;
  static $req_mgr;
  static $safeSizeCfg;
  static $linkedCustomFields;
  static $tprojectHas;
  static $reqSpecSet;
  static $getVersionOpt;
  static $userObj;
  
  if (!$tcData)
  {
    return;
  }
  
  // $tprojectHas = array('customFields' => false, 'reqSpec' => false);
  $hasCustomFieldsInfo = false;
  $hasRequirements = false;
    
  if(is_null($messages))
  {
    $feedbackMsg = array();
    $messages = array();
    $fieldSizeCfg = config_get('field_size');

    $tcase_mgr = new testcase($db);
    $tproject_mgr = new testproject($db);
    $req_spec_mgr = new requirement_spec_mgr($db);
    $req_mgr = new requirement_mgr($db);
    $userObj = new tlUser();

    $k2l = array('already_exists_updated','original_name','testcase_name_too_long',
                 'start_warning','end_warning','testlink_warning','hit_with_same_external_ID');
    foreach($k2l as $k)
    {
      $messages[$k] = lang_get($k);
    }

    $messages['start_feedback'] = $messages['start_warning'] . "\n" . $messages['testlink_warning'] . "\n";
    $messages['cf_warning'] = lang_get('no_cf_defined_can_not_import');
    $messages['reqspec_warning'] = lang_get('no_reqspec_defined_can_not_import');
    
    
    
    $feedbackMsg['cfield']=lang_get('cf_value_not_imported_missing_cf_on_testproject');
    $feedbackMsg['tcase'] = lang_get('testcase');
    $feedbackMsg['req'] = lang_get('req_not_in_req_spec_on_tcimport');
    $feedbackMsg['req_spec'] = lang_get('req_spec_ko_on_tcimport');


    // because name can be changed automatically during item creation
    // to avoid name conflict adding a suffix automatically generated,
    // is better to use a max size < max allowed size 
    $safeSizeCfg = new stdClass();
    $safeSizeCfg->testcase_name=($fieldSizeCfg->testcase_name) * 0.8;


    // Get CF with scope design time and allowed for test cases linked to this test project
    $linkedCustomFields = $tcase_mgr->cfield_mgr->get_linked_cfields_at_design($tproject_id,1,null,'testcase',null,'name');
    $tprojectHas['customFields']=!is_null($linkedCustomFields);                   

    $reqSpecSet = $tproject_mgr->getReqSpec($tproject_id,null,array('RSPEC.id','NH.name AS title','RSPEC.doc_id as rspec_doc_id', 'REQ.req_doc_id'),'req_doc_id');
    $tprojectHas['reqSpec'] = (!is_null($reqSpecSet) && count($reqSpecSet) > 0);

    $getVersionOpt = array('output' => 'minimun');
    $tcasePrefix = $tproject_mgr->getTestCasePrefix($tproject_id);
  }
  
  $resultMap = array();
  $tc_qty = sizeof($tcData);
  $userIDCache = array();
  
  for($idx = 0; $idx <$tc_qty ; $idx++)
  {
    $tc = $tcData[$idx];
    $name = $tc['name'];
    $summary = $tc['summary'];
    $steps = $tc['steps'];

    // I've changed value to use when order has not been provided 
    // from testcase:DEFAULT_ORDER to a counter, because with original solution
    // an issue arise with 'save execution and go next'
    // if use has not provided order I think is OK TestLink make any choice.
    $node_order = isset($tc['node_order']) ? intval($tc['node_order']) : ($idx+1);
    $internalid = $tc['internalid'];
    $preconditions = $tc['preconditions'];
    $exec_type = isset($tc['execution_type']) ? $tc['execution_type'] : TESTCASE_EXECUTION_TYPE_MANUAL;
    $importance = isset($tc['importance']) ? $tc['importance'] : MEDIUM;    

    $externalid = $tc['externalid'];
    if( intval($externalid) <= 0 )
    {
      $externalid = null;
    }
    
    $personID = $userID;
    if( !is_null($tc['author_login']) )
    {
      if( isset($userIDCache[$tc['author_login']]) )
      {
        $personID = $userIDCache[$tc['author_login']];
      }
      else
      {
        $userObj->login = $tc['author_login'];
        if( $userObj->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN) == tl::OK )
        {
          $personID = $userObj->dbID;
        }
        
        // I will put always a valid userID on this cache,
        // this way if author_login does not exit, and is used multiple times
        // i will do check for existence JUST ONCE.
        $userIDCache[$tc['author_login']] = $personID;
      }
    }
      
    $name_len = tlStringLen($name);  
    if($name_len > $fieldSizeCfg->testcase_name)
    {
      // Will put original name inside summary
      $xx = $messages['start_feedback'];
      $xx .= sprintf($messages['testcase_name_too_long'],$name_len, $fieldSizeCfg->testcase_name) . "\n";
      $xx .= $messages['original_name'] . "\n" . $name. "\n" . $messages['end_warning'] . "\n";
      $summary = nl2br($xx) . $summary;
      $name = tlSubStr($name, 0, $safeSizeCfg->testcase_name);      
    }
        
    
    $kwIDs = null;
    if (isset($tc['keywords']) && $tc['keywords'])
    {
      $kwIDs = implode(",",buildKeywordList($kwMap,$tc['keywords']));
    }  
    
    $doCreate=true;
    if( $duplicatedLogic['actionOnHit'] == 'update_last_version' )
    {
      switch($duplicatedLogic['hitCriteria'])
      {
        case 'name':
          $info = $tcase_mgr->getDuplicatesByName($name,$container_id);
        break;
        
        case 'internalID':
          $dummy = $tcase_mgr->tree_manager->get_node_hierarchy_info($internalid,$container_id);
          if( !is_null($dummy) )
          {
            $info = null;
            $info[$internalid] = $dummy;
          }
        break;
    
        case 'externalID':
          $info = $tcase_mgr->get_by_external($externalid,$container_id);
        break;
    
        
      }

      if( !is_null($info) )
      {
        $tcase_qty = count($info);
       switch($tcase_qty)
       {
           case 1:
             $doCreate=false;
             $tcase_id = key($info); 
             $last_version = $tcase_mgr->get_last_version_info($tcase_id,$getVersionOpt);
             $tcversion_id = $last_version['id'];
             $ret = $tcase_mgr->update($tcase_id,$tcversion_id,$name,$summary,
                                       $preconditions,$steps,$personID,$kwIDs,
                                       $node_order,$exec_type,$importance);

             $ret['id'] = $tcase_id;
             $ret['tcversion_id'] = $tcversion_id;
             $resultMap[] = array($name,$messages['already_exists_updated']);
           break;
           
           case 0:
             $doCreate=true; 
           break;
           
           default:
               $doCreate=false; 
           break;
       }
     }
   }
    
    if( $doCreate )
    {           
      // Want to block creation of with existent EXTERNAL ID, if containers ARE DIFFERENT.
      $item_id = intval($tcase_mgr->getInternalID($externalid, array('tproject_id' => $tproject_id)));   
      if( $item_id > 0)
      {
        // who is his parent ?
        $owner = $tcase_mgr->getTestSuite($item_id);  
        if( $owner != $container_id)
        { 
          // Get full path of existent Test Cases
          $stain = $tcase_mgr->tree_manager->get_path($item_id,null, 'name');
          $n = count($stain);         
          $stain[$n-1] = $tcasePrefix . config_get('testcase_cfg')->glue_character . $externalid . ':' . $stain[$n-1];
          $stain = implode('/',$stain);
          
          $resultMap[] = array($name,$messages['hit_with_same_external_ID'] . $stain);
          $doCreate = false;
        }
      }        
    }
    if( $doCreate )
    {     
        $createOptions = array('check_duplicate_name' => testcase::CHECK_DUPLICATE_NAME, 
                               'action_on_duplicate_name' => $duplicatedLogic['actionOnHit'],
                               'external_id' => $externalid);

        if ($ret = $tcase_mgr->create($container_id,$name,$summary,$preconditions,$steps,
                                      $personID,$kwIDs,$node_order,testcase::AUTOMATIC_ID,
                                      $exec_type,$importance,$createOptions))
        {
            $resultMap[] = array($name,$ret['msg']);
        }  
    }
      
  }
  return $resultMap;
}



/* contribution by mirosvad - 
   Convert new line characters from XLS to HTML 
*/
function nl2p($str)  
{
  return str_replace('<p></p>', '', '<p>' . preg_replace('#\n|\r#', '</p>$0<p>', $str) . '</p>'); //MS
}


/*
  function: 
  
  args :
  
  returns: 
  
*/
function init_args()
{
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $args->importType = isset($_REQUEST['importType']) ? $_REQUEST['importType'] : null;
    $args->location = isset($_REQUEST['location']) ? $_REQUEST['location'] : null; 
    $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
    $args->containerType = isset($_REQUEST['containerType']) ? intval($_REQUEST['containerType']) : 0;
    $args->do_upload = isset($_REQUEST['UploadFile']) ? 1 : 0;
    
    $args->userID = intval($_SESSION['userID']);
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : null;

    
    return $args;
}



/**
 * 
 *
 */
function importTestCasesFromIssueSimpleXML(&$db,&$simpleXMLObj,$parentID,$tproject_id,$userID,$kwMap,$duplicateLogic)
{
  $resultMap = null;
  $tcData = getTestCaseSetFromIssueSimpleXMLObj($simpleXMLObj);
  if ($tcData)
  {
    $resultMap = saveImportedTCData($db,$tcData,$tproject_id,$parentID,$userID,$kwMap,$duplicateLogic);
  }  
  return $resultMap;
}

/**
 * 
 *
 * @internal revisions
 * 20100317 - added internalid - BUGID 3236
 */
function getTestCaseSetFromIssueSimpleXMLObj($xmlObj)
{
  $itemSet = null;
  if (!$xmlObj)
  {
    return $itemSet;
  }
    

  $l18n = init_labels( array('issue_issue' => null, 'issue_steps_to_reproduce' => null, 'issue_summary' => null,
                             'issue_target_version' => null,'issue_description' => null, 
                             'issue_additional_information' => null));

  $jdx = 0;
  $xmlIssue = $xmlObj->issue;
  $loops2do=sizeof($xmlIssue);
 
  $XMLDef['elements'] = array('string' => array('summary' => null,'description' => null,
                                                'additional_information' => null,
                                                'steps_to_reproduce' => null, 
                                                'target_version' => null, 'id' => null));
  $itemSet = array();
  $nl = "<p>";
  for($idx = 0; $idx < $loops2do; $idx++)
  {
    $dummy = getItemsFromSimpleXMLObj(array($xmlIssue[$idx]),$XMLDef);
    $dummy = $dummy[0];

    $isum = $l18n['issue_description'] . $nl . $dummy['description'];
    if(!is_null($dummy['steps_to_reproduce']))
    {
      $isum .= $nl . $l18n['issue_steps_to_reproduce'] . $nl . $dummy['steps_to_reproduce'];
    }  
    if(!is_null($dummy['additional_information']))
    {
      $isum .= $nl . $l18n['issue_additional_information'] . $nl . $dummy['additional_information'];
    }  

    $itemSet[$jdx++] = array('name' => ($l18n['issue_issue'] . ':' . $dummy['id'] . ' - ' . $dummy['summary']), 
                             'summary' => $isum, 'steps' => null, 'internalid' => null, 'externalid' => null,
                             'author_login' => null, 'preconditions' => null);    
  }
  return $itemSet;
}




/**
 * 
 *
 * 
 **/
function initializeGui(&$dbHandler,&$argsObj)
{
  $guiObj = new stdClass();
  $guiObj->importLimitBytes = config_get('import_file_max_size_bytes');
  $guiObj->importLimitKB = ($guiObj->importLimitBytes / 1024);
  $guiObj->containerID = $argsObj->container_id;
  $guiObj->refreshTree = $guiObj->doImport = tlStringLen($argsObj->importType);
  $guiObj->resultMap = null;
  $guiObj->container_name = '';
  $guiObj->file_check = array('status_ok' => 1, 'msg' => 'ok');
  $guiObj->import_title = lang_get('title_tc_import_to');
  $guiObj->container_description = lang_get('test_case');

  $dest_common = TL_TEMP_PATH . session_id(). "-importtcs";
  $dest_files = array('XML' => $dest_common . ".xml");
  $guiObj->dest = $dest_files['XML'];
  if(!is_null($argsObj->importType))
  {
    $guiObj->dest = $dest_files[$argsObj->importType];
  }

  if($argsObj->container_id)
  {
    $tree_mgr = new tree($dbHandler);
    $node_info = $tree_mgr->get_node_hierarchy_info($argsObj->container_id);
    unset($tree_mgr);    
    $guiObj->container_name = $node_info['name'];
    if($argsObj->container_id == $argsObj->tproject_id)
    {
      $guiObj->container_description = lang_get('testproject');
    }  
  }

  return $guiObj;
} 

?>