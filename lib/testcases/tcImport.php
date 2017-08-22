<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: control test specification import
 *
 * @filesource  tcImport.php
 * @package     TestLink
 * @copyright   2007-2014, TestLink community 
 * @link        http://testlink.sourceforge.net/ 
 * 
 * @internal revisions
 * @since 1.9.10
 *
 */
require('../../config.inc.php');
require_once('common.php');
require_once('csv.inc.php');
require_once('xml.inc.php');

testlinkInitPage($db);


$templateCfg = templateConfiguration();
$pcheck_fn=null;
$args = init_args();
$gui = initializeGui($db,$args);
if ($args->do_upload)
{
  
  // check the uploaded file
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
      switch($args->importType)
      {
        case 'XML':
          $pcheck_fn = "check_xml_tc_tsuite";
          $pimport_fn = "importTestCaseDataFromXML";
          break;
      }
      if(!is_null($pcheck_fn))
      {
        $gui->file_check = $pcheck_fn($gui->dest,$args->useRecursion);
      }
    }
    if($gui->file_check['status_ok'] && $pimport_fn)
    {
      tLog('Check is Ok.');
      $opt = array();
      $opt['useRecursion'] = $args->useRecursion;
      $opt['importIntoProject'] = $args->bIntoProject;
      $opt['duplicateLogic'] = array('hitCriteria' => $args->hit_criteria,
                                     'actionOnHit' => $args->action_on_duplicated_name);
      $gui->resultMap = $pimport_fn($db,$gui->dest,intval($args->container_id),
                                    intval($args->tproject_id),intval($args->userID),$opt);
    }
  }
  else if(is_null($gui->file_check))
  {
    
    tLog('Missing upload file','WARNING');
    $gui->file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
    $args->importType = null;
  }
}

if($args->useRecursion)
{
  $obj_mgr = new testsuite($db);
  $gui->actionOptions=array('update_last_version' => lang_get('update_last_testcase_version'),
                            'generate_new' => lang_get('generate_new_testcase'),
                            'create_new_version' => lang_get('create_new_testcase_version'));
  
  $gui->hitOptions=array('name' => lang_get('same_name'),
                         'internalID' => lang_get('same_internalID'),
                         'externalID' => lang_get('same_externalID'));
}
else
{
  $obj_mgr = new testcase($db);
  $gui->actionOptions=array('update_last_version' => lang_get('update_last_testcase_version'),
                            'generate_new' => lang_get('generate_new_testcase'),
                            'create_new_version' => lang_get('create_new_testcase_version'));

  $gui->hitOptions=array('name' => lang_get('same_name'),
                         'internalID' => lang_get('same_internalID'),
                         'externalID' => lang_get('same_externalID'));

}

$gui->testprojectName = $_SESSION['testprojectName'];
$gui->importTypes = $obj_mgr->get_import_file_types();
$gui->action_on_duplicated_name=$args->action_on_duplicated_name;


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


// --------------------------------------------------------------------------------------
/*
  function: importTestCaseDataFromXML
  args :
  returns: 
*/
function importTestCaseDataFromXML(&$db,$fileName,$parentID,$tproject_id,$userID,$options=null)
{
  tLog('importTestCaseDataFromXML called for file: '. $fileName);
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
      $xmlKeywords = $xml->xpath('//keywords');
      $kwMap = null;
      if ($xmlKeywords)
      {
        $tproject = new testproject($db);
        $loop2do = sizeof($xmlKeywords);
        for($idx = 0; $idx < $loop2do ;$idx++)
        {
          $tproject->importKeywordsFromSimpleXML($tproject_id,$xmlKeywords[$idx]);
        }
        $kwMap = $tproject->get_keywords_map($tproject_id);
        $kwMap = is_null($kwMap) ? null : array_flip($kwMap);
      }

      if (!$useRecursion &&  ($xml->getName() == 'testcases') )
      {
        $resultMap = importTestCasesFromSimpleXML($db,$xml,$parentID,$tproject_id,$userID,$kwMap,$duplicateLogic);
      }
      
      if ($useRecursion && ($xml->getName() == 'testsuite'))
      {
        $resultMap = importTestSuitesFromSimpleXML($db,$xml,intval($parentID),intval($tproject_id),$userID,
                                                   $kwMap,$importIntoProject,$duplicateLogic);
      }

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
  static $tcasePrefix;
  static $glueChar;
  static $userRights;

  $ret = null;
  
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
    $userObj = new tlUser($userID);
    $userObj->readFromDB($db,tlUser::TLOBJ_O_SEARCH_BY_ID);
    $userRights['can_edit_executed'] = 
      $userObj->hasRight($db,'testproject_edit_executed_testcases',$tproject_id);

    $k2l = array('already_exists_updated','original_name','testcase_name_too_long','already_exists_not_updated',
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
    $feedbackMsg['reqNotInDB'] = lang_get('req_not_in_DB_on_tcimport');


    // because name can be changed automatically during item creation
    // to avoid name conflict adding a suffix automatically generated,
    // is better to use a max size < max allowed size 
    $safeSizeCfg = new stdClass();
    $safeSizeCfg->testcase_name=($fieldSizeCfg->testcase_name) * 0.8;


    // Get CF with scope design time and allowed for test cases linked to this test project
    $linkedCustomFields = $tcase_mgr->cfield_mgr->get_linked_cfields_at_design($tproject_id,1,null,'testcase',null,'name');
    $tprojectHas['customFields']=!is_null($linkedCustomFields);                   

    $reqSpecSet = getReqSpecSet($db,$tproject_id);

    $tprojectHas['reqSpec'] = (!is_null($reqSpecSet) && count($reqSpecSet) > 0);

    $getVersionOpt = array('output' => 'minimun');
    $tcasePrefix = $tproject_mgr->getTestCasePrefix($tproject_id);
    $glueChar = config_get('testcase_cfg')->glue_character;
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

    $attr = null;
    if(isset($tc['estimated_exec_duration']) && !is_null($tc['estimated_exec_duration']))
    {
      $attr['estimatedExecDuration'] = trim($tc['estimated_exec_duration']);
      $attr['estimatedExecDuration'] = $attr['estimatedExecDuration']=='' ? null : floatval($attr['estimatedExecDuration']);
    }  

    if(isset($tc['is_open']))
    {
      $attr['is_open'] = trim($tc['is_open']);
    }  
	
	if(isset($tc['active']))
    {
      $attr['active'] = trim($tc['active']);
    }  
	
    if(isset($tc['status']))
    {
      $attr['status'] = trim($tc['status']);
    }  

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
	  $tcCfg = getWebEditorCfg('design');
	  $tcType = $tcCfg['type'];
	  if ($tcType == 'none'){
		$summary = $xx . $summary ;
      }
	  else{
		$summary = nl2br($xx) . $summary ;
	  }
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
      $updOpt['blockIfExecuted'] = !$userRights['can_edit_executed'];
      switch($duplicatedLogic['hitCriteria'])
      {
        case 'name':
          $info = $tcase_mgr->getDuplicatesByName($name,$container_id);
        break;
        
        case 'internalID':
          $dummy = $tcase_mgr->tree_manager->get_node_hierarchy_info($internalid,$container_id);
          if( !is_null($dummy) )
          {
            $info = null;  // TICKET 4925
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
                                       $node_order,$exec_type,$importance,$attr,$updOpt);

             $ret['id'] = $tcase_id;
             $ret['tcversion_id'] = $tcversion_id;
             if( $ret['status_ok'] )
             { 
               $resultMap[] = array($name,$messages['already_exists_updated']);
             }
             else
             {
               if($ret['reason'] == '')
               {
                 $resultMap[] = array($name, sprintf($messages['already_exists_not_updated'], 
                                                     $tcasePrefix . $glueChar . $externalid,
                                                     $tcasePrefix . $glueChar . $ret['hit_on']['tc_external_id']));
               }
               else
               {
                 $resultMap[] = array($name,$ret['msg']);
               } 
            } 
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
                               'external_id' => $externalid, 'importLogic' => $duplicatedLogic);

        if(!is_null($attr) )
        {
          $createOptions += $attr;
        }  

        if ($ret = $tcase_mgr->create($container_id,$name,$summary,$preconditions,$steps,
                                      $personID,$kwIDs,$node_order,testcase::AUTOMATIC_ID,
                                      $exec_type,$importance,$createOptions))
        {
          $resultMap[] = array($name,$ret['msg']);
        }  
    }
      
    // Custom Fields Management
    // Check if CF with this name and that can be used on Test Cases is defined in current Test Project.
    // If Check fails => give message to user.
    // Else Import CF data
    //   
    $hasCustomFieldsInfo = (isset($tc['customfields']) && !is_null($tc['customfields']));
    if($hasCustomFieldsInfo &&  !is_null($ret))
    {                
      if($tprojectHas['customFields'])
      {                         
        $msg = processCustomFields($tcase_mgr,$name,$ret['id'],$ret['tcversion_id'],$tc['customfields'],
                                   $linkedCustomFields,$feedbackMsg);
        if( !is_null($msg) )
        {
            $resultMap = array_merge($resultMap,$msg);
        }
      }
      else
      {
        // Can not import Custom Fields Values, give feedback
        $msg[]=array($name,$messages['cf_warning']);
        $resultMap = array_merge($resultMap,$msg);          
      }
    }
    
    $hasRequirements=(isset($tc['requirements']) && !is_null($tc['requirements']));
    if($hasRequirements)
    {
      if( $tprojectHas['reqSpec'] )
      {
        $msg = processRequirements($db,$req_mgr,$name,$ret['id'],$tc['requirements'],
                                   $reqSpecSet,$feedbackMsg,$userID);
        if( !is_null($msg) )
        {
          $resultMap = array_merge($resultMap,$msg);
        }
      }
      else
      {
        $msg[]=array($name,$messages['reqspec_warning']);
        $resultMap = array_merge($resultMap,$msg);          
      }
    }
  }
  return $resultMap;
}


// --------------------------------------------------------------------------------------
/*
  function: buildKeywordList
  args :
  returns: 
*/
function buildKeywordList($kwMap,$keywords)
{
  $items = array();
  $loop2do = sizeof($keywords);
  for($jdx = 0; $jdx <$loop2do ; $jdx++)
  {
    $items[] = $kwMap[trim($keywords[$jdx]['name'])]; 
  }
  return $items;
}


// --------------------------------------------------------------------------------------

// --------------------------------------------------------------------------------------

/*
  function: Check if at least the file starts seems OK
*/
function check_xml_tc_tsuite($fileName,$recursiveMode)
{
  $xml = @simplexml_load_file_wrapper($fileName);
  $file_check = array('status_ok' => 0, 'msg' => 'xml_load_ko');          
  if($xml !== FALSE)
  {
    $file_check = array('status_ok' => 1, 'msg' => 'ok');          
    $elementName = $xml->getName();
    if($recursiveMode)
    {
      if($elementName != 'testsuite')
      {
        $file_check=array('status_ok' => 0, 'msg' => lang_get('wrong_xml_tsuite_file'));
      }  
    }
    else
    {
      if($elementName != 'testcases' && $elementName != 'testcase')
        {
        $file_check=array('status_ok' => 0, 'msg' => lang_get('wrong_xml_tcase_file'));
      }  
    }
  }
  return $file_check;
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

  $key='action_on_duplicated_name';
  $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : 'generate_new';

  $key='hit_criteria';
  $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : 'name';
       
        
  $args->importType = isset($_REQUEST['importType']) ? $_REQUEST['importType'] : null;
  $args->useRecursion = isset($_REQUEST['useRecursion']) ? $_REQUEST['useRecursion'] : 0;
  $args->location = isset($_REQUEST['location']) ? $_REQUEST['location'] : null; 
  $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
  $args->bIntoProject = isset($_REQUEST['bIntoProject']) ? intval($_REQUEST['bIntoProject']) : 0;
    
  $args->containerType = isset($_REQUEST['containerType']) ? intval($_REQUEST['containerType']) : 0;
  $args->do_upload = isset($_REQUEST['UploadFile']) ? 1 : 0;
    
  $args->userID = $_SESSION['userID'];
  $args->tproject_id = $_SESSION['testprojectID'];
  
  return $args;
}


/**
 * processCustomFields
 *
 * Analise custom field info related to test case being imported.
 * If everything OK, assign to test case.
 * Else return an array of messages.
 *
 *
 * @internal revisions
 * 20100905 - franciscom - BUGID 3431 - Custom Field values at Test Case VERSION Level
 */
function processCustomFields(&$tcaseMgr,$tcaseName,$tcaseId,$tcversionId,$cfValues,$cfDefinition,$messages)
{
    static $missingCfMsg;
    $cf2insert=null;
    $resultMsg=null;
      
    foreach($cfValues as $value)
    {
       if( isset($cfDefinition[$value['name']]) )
       {
           $cf2insert[$cfDefinition[$value['name']]['id']]=array('type_id' => $cfDefinition[$value['name']]['type'],
                                                                 'cf_value' => $value['value']);         
       }
       else
       {
           if( !isset($missingCfMsg[$value['name']]) )
           {
               $missingCfMsg[$value['name']] = sprintf($messages['cfield'],$value['name'],$messages['tcase']);
           }
           $resultMsg[] = array($tcaseName,$missingCfMsg[$value['name']]); 
       }
    }  
    
    $tcaseMgr->cfield_mgr->design_values_to_db($cf2insert,$tcversionId,null,'simple');
    return $resultMsg;
}

/**
 * processRequirements
 *
 * Analise requirements info related to test case being imported.
 * If everything OK, assign to test case.
 * Else return an array of messages.
 *
 */
function processRequirements(&$dbHandler,&$reqMgr,$tcaseName,$tcaseId,$tcReq,$reqSpecSet,$messages,$userID)
{
  static $missingReqMsg;
  static $missingReqSpecMsg;
  static $missingReqInDBMsg;
  static $cachedReqSpec;
  
  $resultMsg=null;
  $tables = tlObjectWithDB::getDBTables(array('requirements'));


  foreach($tcReq as $ydx => $value)
  {
    $cachedReqSpec=array();
    $doit=false;

    // Look for req doc id we get from file, inside Req Spec Set
    // we got from DB
    if( ($doit=isset($reqSpecSet[$value['doc_id']])) )
    {
      if( !(isset($cachedReqSpec[$value['req_spec_title']])) )
      {
        // $cachedReqSpec
        // key: Requirement Specification Title get from file
        // value: map with follogin keys
        //        id => requirement specification id from DB
        //        req => map with key: requirement document id
        $cachedReqSpec[$value['req_spec_title']]['id']=$reqSpecSet[$value['doc_id']]['id'];
        $cachedReqSpec[$value['req_spec_title']]['req']=null;
      }
    }
    
    if($doit)
    {
      $useit=false;
      $req_spec_id = $cachedReqSpec[$value['req_spec_title']]['id'];
    
      // Check if requirement with desired document id exists on requirement specification on DB.
      // If not => create message for user feedback.
      if( !($useit=isset($cachedReqSpec[$value['req_spec_title']]['req'][$value['doc_id']])) )
      {
        $sql = " SELECT REQ.id from {$tables['requirements']} REQ " .
               " WHERE REQ.req_doc_id='{$dbHandler->prepare_string($value['doc_id'])}' " .
               " AND REQ.srs_id={$req_spec_id} ";     
                   
        $rsx=$dbHandler->get_recordset($sql);
        if( $useit=((!is_null($rsx) && count($rsx) > 0) ? true : false) )
        {
          $cachedReqSpec[$value['req_spec_title']]['req'][$value['doc_id']]=$rsx[0]['id'];
        }  
      }
          
      if($useit)
      {

        $reqMgr->assign_to_tcase($cachedReqSpec[$value['req_spec_title']]['req'][$value['doc_id']],$tcaseId,$userID);
      }
      else
      {
        if( !isset($missingReqMsg[$value['doc_id']]) )
        {
          $missingReqMsg[$value['doc_id']]=sprintf($messages['req'],
                                                   $value['doc_id'],$value['req_spec_title']);  
        }
        $resultMsg[] = array($tcaseName,$missingReqMsg[$value['doc_id']]); 
      }
    } 
    else
    {
      // We didnt find Req Doc ID in Req Spec Set got from DB
      if( !isset($missingReqInDBMsg[$value['doc_id']]) )
      {
        $missingReqInDBMsg[$value['doc_id']]=sprintf($messages['reqNotInDB'],
                                                     $value['doc_id'],'');  
      }
      $resultMsg[] = array($tcaseName,$missingReqInDBMsg[$value['doc_id']]); 
    }
      
  } //foreach
     
  return $resultMsg;
}



/**
 * 
 *
 */
function importTestCasesFromSimpleXML(&$db,&$simpleXMLObj,$parentID,$tproject_id,$userID,$kwMap,$duplicateLogic)
{
  $resultMap = null;
  $xmlTCs = $simpleXMLObj->xpath('//testcase');
  $tcData = getTestCaseSetFromSimpleXMLObj($xmlTCs);
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
 */
function getTestCaseSetFromSimpleXMLObj($xmlTCs)
{
  $tcSet = null;
  if (!$xmlTCs)
  {
    return $tcSet;
  }
    
  $jdx = 0;
  $loops2do=sizeof($xmlTCs);
  $tcaseSet = array();
  
  // $tcXML['elements'] = array('string' => array("summary","preconditions"),
    //             'integer' => array("node_order","externalid","execution_type","importance"));
  // $tcXML['attributes'] = array('string' => array("name"), 'integer' =>array('internalid'));

  // TICKET 4963: Test case / Tes suite XML format, new element to set author
  $tcXML['elements'] = array('string' => array("summary" => null,"preconditions" => null,
                                               "author_login" => null,"estimated_exec_duration" => null),
                             'integer' => array("node_order" => null,"externalid" => null,"is_open" => null,"active" => null,"status" => null,
                                                "execution_type" => null ,"importance" => null));
  $tcXML['attributes'] = array('string' => array("name" => 'trim'), 
                               'integer' =>array('internalid' => null));

  for($idx = 0; $idx < $loops2do; $idx++)
  {
    $dummy = getItemsFromSimpleXMLObj(array($xmlTCs[$idx]),$tcXML);
    $tc = $dummy[0]; 
        
    if ($tc)
    {
      // Test Case Steps
      $steps = getStepsFromSimpleXMLObj($xmlTCs[$idx]->steps->step);
      $tc['steps'] = $steps;

      $keywords = getKeywordsFromSimpleXMLObj($xmlTCs[$idx]->keywords->keyword);
      if ($keywords)
      {
        $tc['keywords'] = $keywords;
      }

      $cf = getCustomFieldsFromSimpleXMLObj($xmlTCs[$idx]->custom_fields->custom_field);
      if($cf)
      {
          $tc['customfields'] = $cf;  
      } 

      $requirements = getRequirementsFromSimpleXMLObj($xmlTCs[$idx]->requirements->requirement);
      if($requirements)
      {
          $tc['requirements'] = $requirements;  
      } 
    }  
    $tcaseSet[$jdx++] = $tc;    
  }
  return $tcaseSet;
}


/**
 * 
 *
 * @internal revisions
 */
function getStepsFromSimpleXMLObj($simpleXMLItems)
{
  $itemStructure['elements'] = array('string' => array("actions"=>null,"expectedresults" => null),
                                     'integer' => array("step_number" => null,"execution_type" => null));
                               
  // 20110205 - franciscom - seems key 'transformations' is not managed on
  // getItemsFromSimpleXMLObj(), then ??? is useless???                               
  $itemStructure['transformations'] = array("expectedresults" => "expected_results");
                               
  $items = getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure);

    // need to do this due to (maybe) a wrong name choice for XML element
  if( !is_null($items) )
  {
    $loop2do = count($items);
    for($idx=0; $idx < $loop2do; $idx++)
    {
      $items[$idx]['expected_results'] = '';
      if( isset($items[$idx]['expectedresults']) )
      {
        $items[$idx]['expected_results'] = $items[$idx]['expectedresults'];
        unset($items[$idx]['expectedresults']);
      }
    }
  }
  return $items;
}

function getCustomFieldsFromSimpleXMLObj($simpleXMLItems)
{
  $itemStructure['elements'] = array('string' => array("name" => 'trim',"value" => 'trim'));
  $items = getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure);
  return $items;

}

function getRequirementsFromSimpleXMLObj($simpleXMLItems)
{
  $itemStructure['elements'] = array('string' => array("req_spec_title" => 'trim',
                                                       "doc_id" => 'trim' ,"title" => 'trim' ));
  $items = getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure);
  return $items;
}

function getKeywordsFromSimpleXMLObj($simpleXMLItems)
{
  $itemStructure['elements'] = array('string' => array("notes" => null));
  $itemStructure['attributes'] = array('string' => array("name" => 'trim'));
  $items = getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure);
  return $items;
}


/*
  function: importTestSuite
  args :
  returns: 
  
  @internal revisions
  20120623 - franciscom - TICKET 5070 - test suite custom fields import
  
*/
function importTestSuitesFromSimpleXML(&$dbHandler,&$xml,$parentID,$tproject_id,
                     $userID,$kwMap,$importIntoProject = 0,$duplicateLogic)
{
  static $tsuiteXML;
  static $tsuiteMgr;
  static $myself;
  static $callCounter = 0;
  static $cfSpec;
  static $doCF;
  
  $resultMap = array();
  if(is_null($tsuiteXML) )
  {
    $myself = __FUNCTION__;
    $tsuiteXML = array();
    $tsuiteXML['elements'] = array('string' => array("details" => null),
                                 'integer' => array("node_order" => null));
    $tsuiteXML['attributes'] = array('string' => array("name" => 'trim'));
    
    $tsuiteMgr = new testsuite($dbHandler);
    $doCF = !is_null(($cfSpec = $tsuiteMgr->get_linked_cfields_at_design(null,null,null,
                                       $tproject_id,'name')));
  }
  
  if($xml->getName() == 'testsuite')
  {
            

    // getItemsFromSimpleXMLObj() first argument must be an array
    $dummy = getItemsFromSimpleXMLObj(array($xml),$tsuiteXML);
    $tsuite = current($dummy); 
    $tsuiteID = $parentID;  // hmmm, not clear

    if ($tsuite['name'] != "")
    {
      // Check if Test Suite with this name exists on this container
      // if yes -> update instead of create
      $info = $tsuiteMgr->get_by_name($tsuite['name'],$parentID);
      if( is_null($info) )
      {
        $ret = $tsuiteMgr->create($parentID,$tsuite['name'],$tsuite['details'],$tsuite['node_order']);
        $tsuite['id'] = $ret['id'];
      }
      else
      {
        $ret = $tsuiteMgr->update(($tsuite['id'] = $info[0]['id']),$tsuite['name'],$tsuite['details'],
                                  null,$tsuite['node_order']);
        
      }
      unset($dummy);

      $tsuiteID = $tsuite['id'];  // $tsuiteID is needed on more code pieces => DO NOT REMOVE
      if (!$tsuite['id'])
      {
        return null;
      }  

      if($doCF)
      {
        $cf = getCustomFieldsFromSimpleXMLObj($xml->custom_fields->custom_field);
        if(!is_null($cf))
        {  
          processTestSuiteCF($tsuiteMgr,$xml,$cfSpec,$cf,$tsuite,$tproject_id);
        }  
      }

      if( $keywords = getKeywordsFromSimpleXMLObj($xml->keywords->keyword) )
      {
        $kwIDs = buildKeywordList($kwMap,$keywords);
        $tsuiteMgr->addKeywords($tsuite['id'],$kwIDs);
      }

      unset($tsuite);
    }
    else if($importIntoProject)
    {
      $tsuiteID = intval($tproject_id);
    }

    $childrenNodes = $xml->children();  
    $loop2do = sizeof($childrenNodes);
    
    for($idx = 0; $idx < $loop2do; $idx++)
    {
      $target = $childrenNodes[$idx];
      switch($target->getName())
      {
        case 'testcase':
          // getTestCaseSetFromSimpleXMLObj() first argument must be an array
          $tcData = getTestCaseSetFromSimpleXMLObj(array($target));
          $resultMap = array_merge($resultMap,
                       saveImportedTCData($dbHandler,$tcData,$tproject_id,
                                          $tsuiteID,$userID,$kwMap,$duplicateLogic));
          unset($tcData);
        break;

        case 'testsuite':
          $resultMap = array_merge($resultMap,
                       $myself($dbHandler,$target,$tsuiteID,$tproject_id,
                            $userID,$kwMap,$importIntoProject,$duplicateLogic));
        break;


        // Important Development Notice
        // Due to XML file structure, while looping
        // we will find also this children:
        // node_order,keywords,custom_fields,details
        //
        // It's processing to get and save values is done
        // on other pieces of this code.
        //
        // Form a logical point of view seems the better 
        // to consider and process here testcase and testsuite as children.
        //
      }      
    }
  }
  return $resultMap;
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
  $guiObj->hitCriteria = $argsObj->hit_criteria;
  $guiObj->useRecursion = $argsObj->useRecursion;
  $guiObj->containerID = $argsObj->container_id;
  $guiObj->bImport = tlStringLen($argsObj->importType);
  $guiObj->bIntoProject = $argsObj->bIntoProject;
  $guiObj->resultMap = null;
  $guiObj->container_name = '';


  $dest_common = TL_TEMP_PATH . session_id(). "-importtcs";
  $dest_files = array('XML' => $dest_common . ".xml");
  $guiObj->dest = $dest_files['XML'];
  if(!is_null($argsObj->importType))
  {
    $guiObj->dest = $dest_files[$argsObj->importType];
  }
  
  $guiObj->file_check = array('status_ok' => 1, 'msg' => 'ok');
  
  if($argsObj->useRecursion)
  {
    $guiObj->import_title = lang_get('title_tsuite_import_to');  
    $guiObj->container_description = lang_get('test_suite');
  }
  else
  {
    $guiObj->import_title = lang_get('title_tc_import_to');
    $guiObj->container_description = lang_get('test_case');
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

/**
 * 
 *
 * @internal revisions
 * @since 1.9.4
 * 
 **/
function processTestSuiteCF(&$tsuiteMgr,$xmlObj,&$cfDefinition,&$cfValues,$tsuite,$tproject_id)
{

  static $messages;
    static $missingCfMsg;

  if(is_null($messages))
  {
      $messages = array();
      $messages['cf_warning'] = lang_get('no_cf_defined_can_not_import');
        $messages['start_warning'] = lang_get('start_warning');
      $messages['end_warning'] = lang_get('end_warning');
      $messages['testlink_warning'] = lang_get('testlink_warning');
      $messages['start_feedback'] = $messages['start_warning'] . "\n" . $messages['testlink_warning'] . "\n";
      $messages['cfield'] = lang_get('cf_value_not_imported_missing_cf_on_testproject');
      $messages['tsuite'] = lang_get('testsuite');
  }    

    $cf2insert=null;
    $resultMsg=null;
    foreach($cfValues as $value)
    {
       if( isset($cfDefinition[$value['name']]) )
       {
           $cf2insert[$cfDefinition[$value['name']]['id']]=array('type_id' => $cfDefinition[$value['name']]['type'],
                                                                 'cf_value' => $value['value']);         
       }
       else
       {
           if( !isset($missingCfMsg[$value['name']]) )
           {
               $missingCfMsg[$value['name']] = sprintf($messages['cfield'],$value['name'],$messages['tsuite']);
           }
           $resultMsg[] = array($tsuite['name'],$missingCfMsg[$value['name']]); 
       }
    }  
    $tsuiteMgr->cfield_mgr->design_values_to_db($cf2insert,$tsuite['id'],null,'simple');
    return $resultMsg;
}

/**
 * 
 */
function getReqSpecSet(&$dbHandler,$tproject_id)
{
  $debugMsg = __FUNCTION__;

  $tables = tlObjectWithDB::getDBTables(array('req_specs','nodes_hierarchy','requirements'));

  // get always Latest Revision Req. Spec Title 
  $sql = "/* $debugMsg */ " .
         " SELECT RSPEC.id, NHRSPEC.name AS title, RSPEC.doc_id AS rspec_doc_id, REQ.req_doc_id " .
         " FROM {$tables['req_specs']} RSPEC " . 
         " JOIN {$tables['nodes_hierarchy']} NHRSPEC ON NHRSPEC.id = RSPEC.id " .
         " JOIN {$tables['requirements']} REQ ON REQ.srs_id = RSPEC.id " .
         " WHERE RSPEC.testproject_id = " . intval($tproject_id) .
         " ORDER BY RSPEC.id,title";

  $rs = $dbHandler->fetchRowsIntoMap($sql,'req_doc_id');
  
  return $rs;
}