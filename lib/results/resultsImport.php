<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Results import from XML file
 * 
 * @package 	TestLink
 * @author 		Kevin Levy
 * @copyright 	2010, TestLink community 
 * @version    	CVS: $Id: resultsImport.php,v 1.22 2010/10/04 19:48:00 franciscom Exp $
 *
 * @internal Revisions:
 * 20110512 - franciscom - BUGID 4467
 * 20101004 - franciscom - added new checks other than	if( isset($tcase_exec['bug_id']) )
 *						   to avoid warnings on event viewer.	
 * 20100926 - franciscom - BUGID 3751: New attribute "execution type" makes old XML import files incompatible
 * 20100823 - franciscom - BUGID 3543 - added execution_type
 * 20100821 - franciscom - BUGID 3470 - reopened
 * 20100328 - franciscom - BUGID 3470, BUGID 3475
 * 20100328 - franciscom - BUGID 3331 add bug id management
 * 20100214 - franciscom - xml managed using simpleXML
 *
 **/

require('../../config.inc.php');
require_once('common.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

$templateCfg = templateConfiguration();

$args = init_args($db);
$gui = new stdClass();

// 20100821 - franciscom
// CRITIC:
// All this logics is done to extract from referer important parameters
// like: build id, etc.
// Is not very clear why we have choose to use this logic, but when doing the filter refactoring
// changes on key names (from build_id -> setting_build, and others), broken the code.
//
// On 20100821 I've (franciscom) choose a different approach:
// changing the javascript function openImportResult() in test_automation.js.
// Then I will remove this logic.
// 
// $ref=$_SERVER['HTTP_REFERER'];
// $url_array=preg_split('/[?=&]/',$ref);
// $key2extract = array('build_id' => 'buildID','platform_id' => 'platformID', 'tplan_id' => 'tplanID');
// foreach($key2extract as $accessKey => $memberKey)
// {
// 	if( in_array($accessKey,$url_array) ) 
// 	{
// 		$dummyIndex = array_search($accessKey,$url_array) + 1;
// 		$args->$memberKey=$url_array[$dummyIndex];
// 	}
// 
// }

$gui->import_title=lang_get('title_results_import_to');
$gui->buildID=$args->buildID;
$gui->platformID=$args->platformID;
$gui->tplanID=$args->tplanID;

$gui->file_check=array('status_ok' => 1, 'msg' => 'ok');
$gui->importTypes=array("XML" => "XML");
$gui->importLimit = config_get('import_file_max_size_bytes');
$gui->doImport = ($args->importType != "");
$gui->testprojectName=$args->testprojectName;


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
?>

<?php
/*
  function: 

  args :
  
  returns: 

*/
function importExecutionResultsFromXML(&$db,$fileName,$context)
{	
	$resultMap=null;
	$xml = @simplexml_load_file($fileName);
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
		$contextKeys = array('testproject' => 'tprojectID', 'testplan' => 'tplanID', 
							 'build' => 'buildID', 'platform' => 'platformID');
		
		foreach( $contextKeys as $xmlkey => $execkey)
		{
			if( ($joker = $xml->$xmlkey) )
			{
				$executionContext->$execkey = (int) $joker['id'];
			}
		} 				

		$xmlTCExec = $xml->xpath("//testcase");
		$resultData = importExecutionsFromXML($xmlTCExec);
		if ($resultData) 
		{
			$resultMap = saveImportedResultData($db,$resultData,$executionContext);
		}
	}
	return $resultMap;
}




/*
  function: saveImportedResultData

  args :
  
  returns: 

  rev: 
 		20100823 - franciscom - BUGID 3543 - added execution_type          		
		20100328 - franciscom - BUGID 3331 manage bug id	
*/
function saveImportedResultData(&$db,$resultData,$context)
{
	if (!$resultData)
	{
		return;
	}
	$debugMsg = ' FUNCTION: ' . __FUNCTION__;
	$tables = tlObjectWithDB::getDBTables(array('executions','execution_bugs'));
	
	
	$l18n = array('import_results_tc_not_found' => '' ,'import_results_invalid_result' => '',
				  'tproject_id_not_found' => '', 'import_results_ok' => '');
	foreach($l18n as $key => $value)
	{
		$l18n[$key] = lang_get($key);
	}
	
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
	$resulstCfg=config_get('results');
	$tcaseCfg=config_get('testcase_cfg');
	
	$resultMap=array();
	$tplan_mgr=null;
	$tc_qty=sizeof($resultData);

	if($tc_qty)
	{
		$tplan_mgr=new testplan($db);
		$tproject_mgr=new testproject($db);
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
	//				  if presente is valid i.e. inside the TL domain
	//
	$checks['status_ok'] = true;		
	$checks['msg'] = null;
	$dummy = $tproject_mgr->get_by_id($context->tprojectID);
	$checks['status_ok'] = !is_null($dummy);
	if( !$checks['status_ok'] )
	{
		$checks['msg'][] = sprintf($l18n['tproject_id_not_found'],$context->tprojectID);
	}

	if( !$checks['status_ok'] )
	{
		foreach($checks['msg'] as $warning )
		{
			$resultMap[]=array($warning);
		}
	}
    $doIt = $checks['status_ok'];
    
    
	// --------------------------------------------------------------------	
	
	for($idx=0; $doIt && $idx < $tc_qty;$idx++)
	{
		$tester_id = 0;
	  	$tester_name = '';	
	  	$using_external_id = false;
    	$message = null;
	  	$status_ok = true;
		$tcase_exec = $resultData[$idx];
		
		// BUGID 3751: New attribute "execution type" makes old XML import files incompatible
		// Important NOTICE:
		// tcase_exec is passed BY REFERENCE to allow check_exec_values()change execution type if needed
		//
		$checks = check_exec_values($db,$tcase_mgr,$user_mgr,$tcaseCfg,$tcase_exec,$columnDef['execution_bugs']);
    	$status_ok = $checks['status_ok'];		
		if($status_ok)
		{
			$tcase_id=$checks['tcase_id'];
			$tcase_external_id=trim($tcase_exec['tcase_external_id']);
        	$tester_id=$checks['tester_id'];
		    
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
   		
	  	if( $status_ok) 
	  	{
	  		$tcase_identity=$using_external_id ? $tcase_external_id : $tcase_id; 
		    $result_code=strtolower($tcase_exec['result']);
		    $result_is_acceptable=isset($resulstCfg['code_status'][$result_code]) ? true : false;
		    		
		    $notes=$tcase_exec['notes'];
		    $message=null;
			$filters = array('tcase_id' => $tcase_id, 'build_id' => $context->buildID,
		    			 	 'platform_id' => $context->platformID);

		    $linked_cases=$tplan_mgr->get_linked_tcversions($context->tplanID,$filters);
		    $info_on_case=$linked_cases[$tcase_id];

		    if (!$linked_cases)
		    {
		    	$message=sprintf($l18n['import_results_tc_not_found'],$tcase_identity);
  	    	}
		    else if (!$result_is_acceptable) 
		    {
		    	$message=sprintf($l18n['import_results_invalid_result'],$tcase_identity,$tcase_exec['result']);
		    } 
		    else 
		    {
		    	$tcversion_id=$info_on_case['tcversion_id'];
		    	$version=$info_on_case['version'];
          		$notes=$db->prepare_string(trim($notes));
          		
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

				// BUGID 3543 - added execution_type          		
          		$sql = " /* $debugMsg */ " .
		      	       " INSERT INTO {$tables['executions']} (build_id,tester_id,status,testplan_id," .
		               " tcversion_id,execution_ts,notes,tcversion_number,platform_id,execution_type)" .
	          	       " VALUES ({$context->buildID}, {$tester_id},'{$result_code}',{$context->tplanID}, ".
	          	       " {$tcversion_id},{$execution_ts},'{$notes}', {$version}, " . 
	          	       " {$context->platformID}, {$tcase_exec['execution_type']})";
	          	$db->exec_query($sql); 

				// BUGID 3331
				if( isset($tcase_exec['bug_id']) && !is_null($tcase_exec['bug_id']) && is_array($tcase_exec['bug_id']) )
				{ 
					$execution_id = $db->insert_id($tables['executions']);
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
		    	$message=sprintf($l18n['import_results_ok'],$tcase_identity,$version,$tester_name,
		    	                 $resulstCfg['code_status'][$result_code],$execution_ts);

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
	    for($idx=0; $idx <$exec_qty ; $idx++)
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
  	$execInfo['execution_type'] = intval((int) trim($xmlTCExec->execution_type)); //BUGID 3543


	$bugQty = count($xmlTCExec->bug_id);
	if( ($bugQty = count($xmlTCExec->bug_id)) > 0 )
	{
		foreach($xmlTCExec->bug_id as $bug)
		{
			$execInfo['bug_id'][] = (string) $bug; // BUGID 3331  
		}
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
	$xml = @simplexml_load_file($fileName);
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

  	$args->importType=isset($_REQUEST['importType']) ? $_REQUEST['importType'] : null;

	// BUGID 3470
	// Need to use REQUEST because sometimes data arrives on GET and other on POST (has hidden fields)
  	$args->buildID = isset($_REQUEST['buildID']) ? intval($_REQUEST['buildID']) : null;
  	$args->platformID = isset($_REQUEST['platformID']) ? intval($_REQUEST['platformID']) : null;
  	$args->tplanID = isset($_REQUEST['tplanID']) ? intval($_REQUEST['tplanID']) : null;
  	$args->tplanID = !is_null($args->tplanID) ? $args->tplanID : $_SESSION['testplanID'];

  	$args->tprojectID = isset($_REQUEST['tprojectID']) ? intval($_REQUEST['tprojectID']) : null;
  	if( is_null($args->tprojectID))
  	{
  		$args->tprojectID = $_SESSION['testprojectID'];
		$args->testprojectName = $_SESSION['testprojectName'];
  		
  	}
	else
	{
  		$tproject_mgr = new testproject($dbHandler);
  		$dummy = $tproject_mgr->get_by_id($args->tprojectID);
  		$args->testprojectName = $dummy['name'];
	}
  	
  	$args->doUpload=isset($_REQUEST['UploadFile']) ? 1 : 0;
  	$args->userID=$_SESSION['userID'];
  	
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

  @internal revisions:
  20100926 - franciscom - BUGID 3751: New attribute "execution type" makes old XML import files incompatible
  						  Passed $execValues BY REFERENCE to allow change of execution type if needed	
*/
function check_exec_values(&$db,&$tcase_mgr,&$user_mgr,$tcaseCfg,&$execValues,&$columnDef)
{
	$tables = tlObjectWithDB::getDBTables(array('users','execution_bugs'));

    $checks=array('status_ok' => false, 'tcase_id' => 0, 'tester_id' => 0, 'msg' => array()); 
	
	$tcase_id=$execValues['tcase_id'];
	$tcase_external_id=trim($execValues['tcase_external_id']);
		
    // external_id has precedence over internal id
    $using_external_id = ($tcase_external_id != "");
    if($using_external_id)
    {
        // need to get internal id  
        $checks['tcase_id']=$tcase_mgr->getInternalID($tcase_external_id,$tcaseCfg->glue_character);
        $checks['status_ok']=intval($checks['tcase_id']) > 0 ? true : false;
        if(!$checks['status_ok'])
        {
           $checks['msg'][]=sprintf(lang_get('tcase_external_id_do_not_exists'),$tcase_external_id); 
        }
    }
    else
    {
       // before using internal id, I want to check it's a number  
       $checks['tcase_id']=$tcase_id;
       $checks['status_ok']=intval($checks['tcase_id']) > 0 ? true : false;
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
    
    // BUGID 4467
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
	
	// BUGID 3543
    if($checks['status_ok'] && isset($execValues['execution_type']) )
    {
    	// BUGID 3751
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
    return $checks;
}
?>