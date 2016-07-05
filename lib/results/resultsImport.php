<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Results import from XML file
 * 
 * @filesource  resultsImport.php
 * @package     TestLink
 * @author      Kevin Levy
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2010,2014 TestLink community 
 *
 * @internal revisions
 * @since 1.9.13
 *
 **/

require('../../config.inc.php');
require_once('common.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
require_once('exec.inc.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();


$args = init_args($db);
$gui = initializeGui($args);

$resultMap=null;
$dest=TL_TEMP_PATH . session_id()."-results.import";
$container_description=lang_get('import_xml_results');

if ($args->doUpload)
{
  // check the uploaded file
  $source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
  if (($source != 'none') && ($source != ''))
  { 
    $gui->file_check['status_ok']=1;
    if($gui->file_check['status_ok'])
    {
      if (move_uploaded_file($source, $dest))
      {
        switch($args->importType)
        {
          case 'XML':
            $pcheck_fn="check_xml_execution_results";
            $pimport_fn="importExecutionResultsFromXML";
          break;
        }
        
        if ($pcheck_fn)
        {
          $gui->file_check=$pcheck_fn($dest);
          if($gui->file_check['status_ok'])
          {
            if ($pimport_fn)
            {
              $resultMap=$pimport_fn($db,$dest,$args);
            }
          }
        }
      }
    }
  }
  else
  {
    $gui->file_check=array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
    $args->importType=null;
  }
}

$gui->resultMap=$resultMap;
$smarty=new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
  function: 

  args :
  
  returns: 

*/
function importExecutionResultsFromXML(&$db,$fileName,$context)
{ 
  $resultMap=null;

  $xml = @simplexml_load_file_wrapper($fileName);
  if($xml !== FALSE)
  {
    $resultMap = importResults($db,$xml,$context);
  }
  return $resultMap;
}


/*
  function: 

  args :
  
  returns: 

*/
function importResults(&$db,&$xml,$context)
{
  $resultMap = null;
  if($xml->getName() == 'results')
  {
    // check if additional data (context execution) has been provided,
    // if yes overwrite GUI selection with value get from file
    //
    $executionContext = $context;
    $contextKeys = array('testproject'  => array('id' => 'tprojectID', 'name' => 'tprojectName'), 
                         'testplan'   => array('id' => 'tplanID', 'name' => 'tplanName'),  
                         'build'    => array('id' => 'buildID', 'name' => 'buildName'),   
                         'platform'   => array('id' => 'platformID', 'name' => 'platformName'));
    

    foreach( $contextKeys as $xmlkey => $execElem)
    {
      if( ($joker = $xml->$xmlkey) )
      {
        // IMPORTANT NOTICE: name has precedence over id
        if( isset($joker['name']) )
        {
          $executionContext->$execElem['name'] = (string) $joker['name'];
          $executionContext->$execElem['id'] = null; // get rid of id passed from GUI
          continue;
        }
        if( isset($joker['id']) )
        {
          $executionContext->$execElem['id'] = (int) $joker['id'];
          $executionContext->$execElem['name'] = null;
        }
      }
    }         

    $xmlTCExec = $xml->xpath("//testcase");
    $resultData = importExecutionsFromXML($xmlTCExec);
    if ($resultData) 
    {
      $resultMap = saveImportedResultData($db,$resultData,$executionContext,$context);
    }
  }
  return $resultMap;
}




/*
  function: saveImportedResultData

  args :
  
  returns: 

  rev: 
*/
function saveImportedResultData(&$db,$resultData,$context,$options)
{
  if (!$resultData)
  {
    return;
  }
  $debugMsg = ' FUNCTION: ' . __FUNCTION__;
  $tables = tlObjectWithDB::getDBTables(array('executions','execution_bugs'));
  
  $tcaseCfg=config_get('testcase_cfg');

  // --------------------------------------------------------------------------------------- 
  $l10n = array('import_results_tc_not_found' => '' ,'import_results_invalid_result' => '',
                'tproject_id_not_found' => '', 'import_results_ok' => '',
                'invalid_cf' => '', 'import_results_skipped' => '');

  foreach($l10n as $key => $value)
  {
    $l10n[$key] = lang_get($key);
  }

  $resultsCfg=config_get('results');
  foreach($resultsCfg['status_label'] as $ks => $lbl)
  {
    $key = $resultsCfg['status_code'][$ks];
    $l10n[$key] = lang_get($lbl);
  }
  // ---------------------------------------------------------------------------------------

  // Get Column definitions to get size dinamically instead of create constants
  $columnDef = array();
  $adodbObj = $db->get_dbmgr_object();
  $columnDef['execution_bugs'] = $adodbObj->MetaColumns($tables['execution_bugs']);
  $keySet = array_keys($columnDef['execution_bugs']);
  foreach($keySet as $keyName)
  {
    if( ($keylow=strtolower($keyName)) != $keyName )
    { 
      $columnDef['execution_bugs'][$keylow] = $columnDef['execution_bugs'][$keyName];
      unset($columnDef['execution_bugs'][$keyName]);
    }
  } 
  $user=new tlUser($context->userID);
  $user->readFromDB($db);
  
  $tcase_mgr=new testcase($db);
  
  $resultMap=array();
  $tplan_mgr=null;
  $tc_qty=sizeof($resultData);

  if($tc_qty)
  {
    $tplan_mgr=new testplan($db);
    $tproject_mgr=new testproject($db);
    $build_mgr=new build_mgr($db);
  }
  
  // Need to do checks on common settings
  //
  // test project exists
  //
  // test plan id: 
  //              belongs to target test project
  //              is active 
  // build id:
  //          belongs to target test plan
  //          is open
  //
  // platform id:
  //          is linked  to target test plan
  //
  // execution type if not present -> set to MANUAL
  //          if presente is valid i.e. inside the TL domain
  //
  $checks = array();
  $checks['status_ok'] = true;    
  $checks['msg'] = null;
  $dummy = null;
  
  if( !is_null($context->tprojectID) && intval($context->tprojectID) > 0)
  {
    $dummy = array($tproject_mgr->get_by_id($context->tprojectID,array('output' => 'existsByID')));
  }
  else if( !is_null($context->tprojectName) )
  {
    $dummy = $tproject_mgr->get_by_name($context->tprojectName,null,array('output' => 'existsByName'));
  }

  $checks['status_ok'] = !is_null($dummy);
  if( !$checks['status_ok'] )
  {
    $checks['msg'][] = sprintf($l10n['tproject_id_not_found'],$context->tprojectID);
  }

  if( !$checks['status_ok'] )
  {
    foreach($checks['msg'] as $warning )
    {
      $resultMap[]=array($warning);
    }
  }
  
  if( ($doIt = $checks['status_ok']) )
  {
    $context->tprojectID = $dummy[0]['id']; 
  }
    
    
    
  // -------------------------------------------------------------------- 
  $dummy = null;
  if( !is_null($context->tplanID) && intval($context->tplanID) > 0 )
  {
    $dummy = $tplan_mgr->get_by_id($context->tplanID,array('output' => 'minimun'));
    if( !is_null($dummy) )
    {
      $dummy['id'] = $context->tplanID;  
    } 
  }
  else if( !is_null($context->tplanName) )
  {
    $dummy = $tplan_mgr->get_by_name($context->tplanName,$context->tprojectID,array('output' => 'minimun'));
    if( !is_null($dummy) )
    {
      $dummy = $dummy[0];
    }
  }
  
  if( !is_null($dummy) )
  {
      $context->tplanID = $dummy['id']; 
  }

  if( (intval($context->tprojectID) <= 0) && intval($context->tplanID) > 0)
  {
    $dummy = $tplan_mgr->tree_manager->get_node_hierarchy_info($context->tplanID);
    $context->tprojectID = $dummy['parent_id'];
  }
  // -------------------------------------------------------------------- 
  
  // -------------------------------------------------------------------- 
  $dummy = null;
  $tplan_mgr->platform_mgr->setTestProjectID($context->tprojectID);
  if( !is_null($context->platformID) && intval($context->platformID) > 0 )
  {
    $dummy = array($tplan_mgr->platform_mgr->getByID($context->platformID));
  }
  else if( property_exists($context,'platformName') && !is_null($context->platformName) )
  {
    if( !is_null($xx = $tplan_mgr->platform_mgr->getID($context->platformName) ) )
    {
      $dummy = array(0 => array('id' => $xx));
    }
  }
  if( !is_null($dummy) )
  {
      $context->platformID = $dummy[0]['id']; 
  }
  // -------------------------------------------------------------------- 

  // -------------------------------------------------------------------- 
  $optGB = array('tplan_id' => $context->tplanID, 'output' => 'minimun');
  $dummy = null;
  if( !is_null($context->buildID) && intval($context->buildID) > 0 )
  {
    $dummy = array($build_mgr->get_by_id($context->buildID,$optGB));
  }
  else if( !is_null($context->buildName) )
  {
    $dummy = $build_mgr->get_by_name($context->buildName,$optGB);
  }
  if( !is_null($dummy) )
  {
      $context->buildID = $dummy[0]['id'];  
  }
  // -------------------------------------------------------------------- 
    
  // -------------------------------------------------------------------- 
  for($idx=0; $doIt && $idx < $tc_qty;$idx++)
  {
    $tester_id = 0;
    $tester_name = '';  
    $using_external_id = false;
    $message = null;
    $status_ok = true;
    $tcase_exec = $resultData[$idx];
    
    // New attribute "execution type" makes old XML import files incompatible
    // Important NOTICE:
    // tcase_exec is passed BY REFERENCE to allow check_exec_values()change execution type if needed
    //
    $checks = check_exec_values($db,$tcase_mgr,$user_mgr,$tcaseCfg,$tcase_exec,$columnDef['execution_bugs']);
    $status_ok = $checks['status_ok'];    
    if($status_ok)
    {
      $tcase_id = $checks['tcase_id'];
      $tcase_external_id = trim($tcase_exec['tcase_external_id']);
      $tester_id = $checks['tester_id'];
        
      // external_id has precedence over internal id
      $using_external_id = ($tcase_external_id != "");
    } 
    else
    {
      foreach($checks['msg'] as $warning )
      {
        $resultMap[]=array($warning);
      }
    }
     
    if( $status_ok ) 
    {
      $tcase_identity = $using_external_id ? $tcase_external_id : $tcase_id; 
      $result_code = strtolower($tcase_exec['result']);
      $result_is_acceptable = isset($resultsCfg['code_status'][$result_code]) ? true : false;
      $notes = $tcase_exec['notes'];
      $message = null;
       

      $info_on_case = $tplan_mgr->getLinkInfo($context->tplanID,$tcase_id,$context->platformID);
      if(is_null($info_on_case))
      {
        $message=sprintf($l10n['import_results_tc_not_found'],$tcase_identity);
      }
      else if (!$result_is_acceptable) 
      {
        $message=sprintf($l10n['import_results_invalid_result'],$tcase_identity,$tcase_exec['result']);
      } 
      else 
      {
        $info_on_case = current($info_on_case);
        $tcversion_id = $info_on_case['tcversion_id'];
        $version = $info_on_case['version'];
        $notes = $db->prepare_string(trim($notes));
             
        // N.B.: db_now() returns an string ready to be used in an SQL insert
        //       example '2008-09-04', while $tcase_exec["timestamp"] => 2008-09-04
        //
        $execution_ts=($tcase_exec['timestamp'] != '') ? "'" . $tcase_exec["timestamp"] . "'": $db->db_now();
          
        if($tester_id != 0)
        {
          $tester_name=$tcase_exec['tester'];
        } 
        else
        {
          $tester_name=$user->login;
          $tester_id=$context->userID;
        }

        $addExecDuration = (strlen($tcase_exec['execution_duration']) > 0 && is_numeric($tcase_exec['execution_duration']));

        $lexid = 0;
        if($options->copyIssues)
        {
          $lexid = $tcase_mgr->getSystemWideLastestExecutionID($tcversion_id);
        }  

        $idCard = array('id' => $tcase_id,'version_id' => $tcversion_id);
        $exco = array('tplan_id' => $context->tplanID, 'platform_id' => $context->platformID, 
                      'build_id' => $context->buildID);
        $lexInfo = $tcase_mgr->getLatestExecSingleContext($idCard,$exco,array('output' => 'timestamp'));
        $doInsert = true;
        if(!is_null($lexInfo))
        {
          $tts = $lexInfo[$tcase_id][0]['execution_ts'];
          $doInsert = ($lexInfo[$tcase_id][0]['execution_ts'] != trim($execution_ts,"'"));
          $msgTxt = $l10n['import_results_skipped'];
        }  

        if( $doInsert )
        {
          $sql = " /* $debugMsg */ " .
                 " INSERT INTO {$tables['executions']} (build_id,tester_id,status,testplan_id," .
                 " tcversion_id,execution_ts,notes,tcversion_number,platform_id,execution_type" .
                 ($addExecDuration ? ',execution_duration':'') . ")" .
                 " VALUES ({$context->buildID}, {$tester_id},'{$result_code}',{$context->tplanID}, ".
                 " {$tcversion_id},{$execution_ts},'{$notes}', {$version}, " . 
                 " {$context->platformID}, {$tcase_exec['execution_type']}" .
                 ($addExecDuration ? ",{$tcase_exec['execution_duration']}" : '') . ")";

          $db->exec_query($sql); 
          $execution_id = $db->insert_id($tables['executions']);

          if($lexid > 0 && $options->copyIssues)
          {
            copyIssues($db,$lexid,$execution_id);
          }
		
		if(isset($tcase_exec['steps']) && !is_null($tcase_exec['steps']) && $execution_id > 0 )
		{
			$stepSet = $tcase_mgr->getStepsSimple($tcversion_id,0,
					  array('fields2get' => 'TCSTEPS.step_number,TCSTEPS.id',
							'accessKey' => 'step_number'));
			$sc = count($tcase_exec['steps']);

			for($sx=0; $sx < $sc; $sx++)
			{
			  $snum = $tcase_exec['steps'][$sx]['step_number'];
			  
			  if(isset($stepSet[$snum]))
			  {	
				$tcstep_id = $stepSet[$snum]['id'];
				$target = DB_TABLE_PREFIX . 'execution_tcsteps';
				
				$doIt = (!is_null($tcase_exec['steps'][$sx]['result']) && 
						   trim($tcase_exec['steps'][$sx]['result']) != '') || 
						  $tcase_exec['steps'][$sx]['result'] != $resultsCfg['status_code']['not_run'];

				if( $doIt )
				{
					$sql = " INSERT INTO {$target} (execution_id,tcstep_id,notes";
					$values = " VALUES ( {$execution_id},  {$tcstep_id} , " . 
							  "'" . $db->prepare_string($tcase_exec['steps'][$sx]['notes']) . "'";

					$status = strtolower(trim($tcase_exec['steps'][$sx]['result']));
					$status = $status[0];
					$sql .= ",status";
					$values .= ",'" . $db->prepare_string($tcase_exec['steps'][$sx]['result']) . "'";
					  
					$sql .= ") " . $values . ")";
					$db->exec_query($sql);

					$execution_tcsteps_id = $db->insert_id($target);
				  } 
			  }
			}
		}

          if( isset($tcase_exec['bug_id']) && !is_null($tcase_exec['bug_id']) && is_array($tcase_exec['bug_id']) )
          { 
            foreach($tcase_exec['bug_id'] as $bug_id)
            {
              $bug_id = trim($bug_id);
              $sql = " /* $debugMsg */ " .            
                     " SELECT execution_id AS check_qty FROM  {$tables['execution_bugs']} " .
                     " WHERE bug_id = '{$bug_id}' AND execution_id={$execution_id} ";
              $rs = $db->get_recordset($sql); 
              if( is_null($rs) )
              {
                $sql = " /* $debugMsg */ " .
                       " INSERT INTO {$tables['execution_bugs']} (bug_id,execution_id)" .
                       " VALUES ('" . $db->prepare_string($bug_id) . "', {$execution_id} )";
                      $db->exec_query($sql); 
              }
            }
          }
          if( isset($tcase_exec['custom_fields']) && !is_null($tcase_exec['custom_fields']) && is_array($tcase_exec['custom_fields']) )
          { 
            // Get linked custom fields to this test project, for test case on execution
            // $context->tprojectID
            $cfieldMgr = new cfield_mgr($db);
            $cfSetByName = $cfieldMgr->get_linked_cfields_at_execution($context->tprojectID,1,'testcase',null,null,null,'name');
            foreach($tcase_exec['custom_fields'] as $cf)
            {
              $ak = null;
              if( isset($cfSetByName[$cf['name']]) )
              {
                // write to db blind
                $ak[$cfSetByName[$cf['name']]['id']]['cf_value'] = $cf['value']; 
              }  
              else
              {
                $message=sprintf($l10n['invalid_cf'],$tcase_identity,$cf['name']);
              } 

              if(!is_null($ak))
              {
                $cfieldMgr->execution_values_to_db($ak,$tcversion_id,$execution_id,$context->tplanID,null,'plain');
              }  
            }  
          }

          if( !is_null($message) )
          {
            $resultMap[]=array($message);
          }  
          $msgTxt = $l10n['import_results_ok'];

        }  
        $message=sprintf($msgTxt,$tcase_identity,$version,$tester_name,$l10n[$result_code],$execution_ts);
      }
    }
  
    if( !is_null($message) )
    {       
      $resultMap[]=array($message);
    }   
  }
  return $resultMap;
}

/*
  function: importExecutionsFromXML

  args :
  
  returns: 

*/
function importExecutionsFromXML($xmlTCExecSet)
{
  $execInfoSet=null;
  if($xmlTCExecSet) 
  { 
    $jdx=0;
    $exec_qty=sizeof($xmlTCExecSet);
    for($idx=0; $idx < $exec_qty ; $idx++)
    {
      $xmlTCExec=$xmlTCExecSet[$idx];
      $execInfo = importExecutionFromXML($xmlTCExec);
      if ($execInfo)
      {
        $execInfoSet[$jdx++]=$execInfo;
      }
    }
  }
  return $execInfoSet;
}

/*
  function: importExecutionFromXML()

  args :
  
  returns: 

*/
function importExecutionFromXML(&$xmlTCExec)
{
  if (!$xmlTCExec)
  {
    return null;
  }
    
  $execInfo=array();;
  $execInfo['tcase_id'] = isset($xmlTCExec["id"]) ? (int)$xmlTCExec["id"] : 0;
  $execInfo['tcase_external_id'] = (string) $xmlTCExec["external_id"];

  // Developer Note - 20100328 - franciscom: 
  // seems that no PHP error is generated when trying to access an undefined
  // property. Do not know if will not be better anyway to use property_exists()
  //    
  $execInfo['tcase_name'] = (string) $xmlTCExec->name;
  $execInfo['result'] = (string) trim($xmlTCExec->result);
  $execInfo['notes'] = (string) trim($xmlTCExec->notes);
  $execInfo['timestamp'] = (string) trim($xmlTCExec->timestamp);
  $execInfo['tester'] = (string) trim($xmlTCExec->tester);
  $execInfo['execution_type'] = intval((int) trim($xmlTCExec->execution_type));
  $execInfo['execution_duration'] = trim($xmlTCExec->execution_duration);


  $bugQty = count($xmlTCExec->bug_id);
  if( ($bugQty = count($xmlTCExec->bug_id)) > 0 )
  {
    foreach($xmlTCExec->bug_id as $bug)
    {
      $execInfo['bug_id'][] = (string) $bug; // BUGID 3331  
    }
  }

  $execInfo['steps'] = null;
  if(property_exists($xmlTCExec, 'steps') && property_exists($xmlTCExec->steps, 'step'))
  {
    $itemStructure['elements'] = array('integer' => array("step_number" => 'intval'),
                                       'string' => array("result" => 'trim',"notes" => 'trim'));
    $execInfo['steps'] = getItemsFromSimpleXMLObj($xmlTCExec->steps->step,$itemStructure);
  }  

  $execInfo['custom_fields'] = null;
  if(property_exists($xmlTCExec, 'custom_fields') && property_exists($xmlTCExec->custom_fields, 'custom_field'))
  {
    $itemStructure['elements'] = array('string' => array("name" => 'trim',"value" => 'trim'));
    $execInfo['custom_fields'] = getItemsFromSimpleXMLObj($xmlTCExec->custom_fields->custom_field,$itemStructure);
  }  

  return $execInfo;     
}


/*
  function: 

           Check if at least the file starts seems OK

*/
function check_xml_execution_results($fileName)
{
  
  $file_check=array('status_ok' => 0, 'msg' => 'xml_ko');     
  $xml = @simplexml_load_file_wrapper($fileName);
  if($xml !== FALSE)
  {
    $file_check=array('status_ok' => 1, 'msg' => 'ok');         
    $elementName = $xml->getName();
    if($elementName != 'results') 
    {
      $file_check=array('status_ok' => 0, 'msg' => lang_get('wrong_results_import_format'));
    }
  }
  return $file_check;
}


/*
  function: init_args(&$dbHandler)

  args :
  
  returns: 

*/
function init_args(&$dbHandler)
{
  $args=new stdClass();
  $_REQUEST=strings_stripSlashes($_REQUEST);

  $args->importType = isset($_REQUEST['importType']) ? $_REQUEST['importType'] : null;
  $args->copyIssues = isset($_REQUEST['copyIssues']) ? 1 : 0;


  // Need to use REQUEST because sometimes data arrives on GET and other on POST (has hidden fields)
  $args->buildID = isset($_REQUEST['buildID']) ? intval($_REQUEST['buildID']) : null;
  $args->platformID = isset($_REQUEST['platformID']) ? intval($_REQUEST['platformID']) : null;
  $args->tplanID = isset($_REQUEST['tplanID']) ? intval($_REQUEST['tplanID']) : null;
  $args->tplanID = !is_null($args->tplanID) ? $args->tplanID : intval($_SESSION['testplanID']);

  $args->tprojectID = isset($_REQUEST['tprojectID']) ? intval($_REQUEST['tprojectID']) : null;
  if( is_null($args->tprojectID))
  {
    $args->tprojectID = intval($_SESSION['testprojectID']);
    $args->testprojectName = $_SESSION['testprojectName'];
  }
  else
  {
    $tproject_mgr = new testproject($dbHandler);
    $dummy = $tproject_mgr->get_by_id($args->tprojectID);
    $args->testprojectName = $dummy['name'];
  }
    
  $args->doUpload=isset($_REQUEST['UploadFile']) ? 1 : 0;
  $args->userID=intval($_SESSION['userID']);

  return $args;
}

/*
  function: check_exec_values()

  args :
  
  returns: map
           keys: 
           status_ok -> value=true / false
           tcase_id: test case id if controls OK
           tester_id: tester_id if controls OK  
           msg -> array with localized messages  

  @internal revisions
*/
function check_exec_values(&$db,&$tcase_mgr,&$user_mgr,$tcaseCfg,&$execValues,&$columnDef)
{
  $tables = tlObjectWithDB::getDBTables(array('users','execution_bugs'));
  $checks=array('status_ok' => false, 'tcase_id' => 0, 'tester_id' => 0, 'msg' => array()); 
  $tcase_id=$execValues['tcase_id'];
  $tcase_external_id=trim($execValues['tcase_external_id']);
  $using_external_id = ($tcase_external_id != ""); // external_id has precedence over internal id

  if($using_external_id)
  {
    // need to get internal id  
    $checks['tcase_id'] = $tcase_mgr->getInternalID($tcase_external_id);
    $checks['status_ok'] = intval($checks['tcase_id']) > 0 ? true : false;
    if(!$checks['status_ok'])
    {
       $checks['msg'][]=sprintf(lang_get('tcase_external_id_do_not_exists'),$tcase_external_id); 
    }
  }
  else
  {
    // before using internal id, I want to check it's a number  
    $checks['tcase_id'] = $tcase_id;
    $checks['status_ok'] = intval($checks['tcase_id']) > 0 ? true : false;
    if(!$checks['status_ok'])
    {
      $checks['msg'][]=sprintf(lang_get('tcase_id_is_not_number'),$tcase_id); 
    }
  }
  if($checks['status_ok'])
  {
    // useful for user feedback 
    $identity=$using_external_id ? $tcase_external_id : $checks['tcase_id']; 
  }
 
  if($checks['status_ok'] && $execValues['timestamp'] != '' )
  {
    $checks['status_ok']=isValidISODateTime($execValues['timestamp']);
    if(!$checks['status_ok'])
    {
      $checks['msg'][]=sprintf(lang_get('invalid_execution_timestamp'),$identity,$execValues['timestamp']); 
    }
  }

  if($checks['status_ok'] && $execValues['tester'] != '' )
  {
    $sql = "SELECT id,login FROM {$tables['users']} WHERE login ='" . 
    $db->prepare_string($execValues['tester']) . "'";
    $userInfo=$db->get_recordset($sql);
      
    if(!is_null($userInfo) && isset($userInfo[0]['id']) )
    {
      $checks['tester_id']=$userInfo[0]['id'];
    }
    else
    {
      $checks['status_ok']=false;
      $checks['msg'][]=sprintf(lang_get('invalid_tester'),$identity,$execValues['tester']); 
    }
  }
    
  $execValues['bug_id'] = isset($execValues['bug_id']) ? $execValues['bug_id'] : null;
  if($checks['status_ok'] && !is_null($execValues['bug_id']) && is_array($execValues['bug_id']) )
  {
    foreach($execValues['bug_id'] as $bug_id )
    {
    if( ($field_len = strlen(trim($bug_id))) > $columnDef['bug_id']->max_length )
    {
      $checks['msg'][]=sprintf(lang_get('bug_id_invalid_len'),$field_len,$columnDef['bug_id']->max_length); 
      $checks['status_ok']=false;
      break;
    }
  }
  }
  
  if($checks['status_ok'] && isset($execValues['execution_type']) )
  {
    $execValues['execution_type'] = intval($execValues['execution_type']); 
    $execDomain = $tcase_mgr->get_execution_types();
    if( $execValues['execution_type'] == 0 )
    {
      $execValues['execution_type'] = TESTCASE_EXECUTION_TYPE_MANUAL;
      // right now this is useless, but may be in future can be used, then I choose to leave it.
      $checks['msg'][]=sprintf(lang_get('missing_exec_type'),
                               $execValues['execution_type'],$execDomain[$execValues['execution_type']]);
    }
    else
    {
      $checks['status_ok'] = isset($execDomain[$execValues['execution_type']]);
      if( !$checks['status_ok'] )
      {
        $checks['msg'][]=sprintf(lang_get('invalid_exec_type'),$execValues['execution_type']);
      }
    }
  }


  if($checks['status_ok'] && isset($execValues['steps']) )
  {
    // To Be done
  }

  return $checks;
}


/**
 *
 *
 */
function initializeGui(&$argsObj)
{
  $guiObj = new stdClass();
  $guiObj->import_title = lang_get('title_results_import_to');
  $guiObj->buildID = $argsObj->buildID;
  $guiObj->platformID = $argsObj->platformID;
  $guiObj->tplanID = $argsObj->tplanID;

  $guiObj->file_check = array('status_ok' => 1, 'msg' => 'ok');
  $guiObj->importTypes = array("XML" => "XML");
  $guiObj->importLimit = config_get('import_file_max_size_bytes');
  $guiObj->doImport = ($argsObj->importType != "");
  $guiObj->testprojectName = $argsObj->testprojectName;
  $guiObj->copyIssues = $argsObj->copyIssues;
  return $guiObj;  
}