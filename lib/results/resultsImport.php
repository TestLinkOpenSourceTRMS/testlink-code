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
 * @version    	CVS: $Id: resultsImport.php,v 1.16 2010/03/28 13:54:35 franciscom Exp $
 *
 * @internal Revisions:
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

$args=init_args();
$gui = new stdClass();

$ref=$_SERVER['HTTP_REFERER'];
$url_array=preg_split('/[?=&]/',$ref);
if( in_array('build_id',$url_array) ) 
{
	$buildIdIndex=array_search('build_id',$url_array) + 1;
	$args->buildID=$url_array[$buildIdIndex];
}

$gui->import_title=lang_get('title_results_import_to');
$gui->buildID=$args->buildID;
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
							$resultMap=$pimport_fn($db,$dest,$args->tplan_id,$args->userID,$args->buildID);
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
function importExecutionResultsFromXML(&$db,$fileName,&$tplan_id,$userID, $buildID)
{	
	$resultMap=null;
	$xml = @simplexml_load_file($fileName);
	if($xml !== FALSE)
	{
		$resultMap=importResults($db,$xml,$tplan_id,$userID, $buildID);
	}
	return $resultMap;
}

/*
  function: 

  args :
  
  returns: 

*/
function importResults(&$db, &$xml,&$tplan_id, &$userID, $buildID) 
{
	$resultMap = null;
	if($xml->getName() == 'results')
	{
		$xmlTCExec = $xml->xpath("//testcase");
		$resultData = importExecutionsFromXML($xmlTCExec);
		if ($resultData) 
		{
			$resultMap=saveImportedResultData($db,$resultData,$tplan_id,$userID,$buildID);
		}
	}
	return $resultMap;
}

/*
  function: 

  args :
  
  returns: 

  20100328 - franciscom - BUGID 3331 manage bug id	
*/
function saveImportedResultData(&$db,$resultData,&$tplan_id,$userID,$buildID)
{
	if (!$resultData)
	{
		return;
	}
	$debugMsg = ' FUNCTION: ' . __FUNCTION__;
	$tables = tlObjectWithDB::getDBTables(array('executions','execution_bugs'));
	
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
	$user=new tlUser($userID);
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
	}
	
	for($idx=0; $idx < $tc_qty ;$idx++)
	{
		$tester_id=0;
	  	$tester_name='';	
	  	$using_external_id=false;
    	$message=null;
	  	$status_ok=true;
		$tcase_exec=$resultData[$idx];
		
		$checks=check_exec_values($db,$tcase_mgr,$user_mgr,$tcaseCfg,$tcase_exec,$columnDef['execution_bugs']);
    	$status_ok=$checks['status_ok'];		
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
		    $filters = array('tcase_id' => $tcase_id);
		    $linked_cases=$tplan_mgr->get_linked_tcversions($tplan_id,$filters);
		    $info_on_case=$linked_cases[$tcase_id];

		    if (!$linked_cases)
		    {
		    	$message=sprintf(lang_get('import_results_tc_not_found'),$tcase_identity);
  	    	}
		    else if (!$result_is_acceptable) 
		    {
		    	$message=sprintf(lang_get('import_results_invalid_result'),$tcase_identity,$tcase_exec['result']);
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
              		$tester_id=$userID;
          		}
          		$sql = " /* $debugMsg */ " .
		      	       " INSERT INTO {$tables['executions']} (build_id,tester_id,status,testplan_id," .
		               " tcversion_id,execution_ts,notes,tcversion_number)" .
	          	       " VALUES ({$buildID}, {$tester_id},'{$result_code}',{$tplan_id}, ".
	          	       " {$tcversion_id},{$execution_ts},'{$notes}', {$version})";
	          	$db->exec_query($sql); 

				// BUGID 3331 
				if( isset($tcase_exec['bug_id']) )
				{ 
					$execution_id = $db->insert_id($tables['executions']);
          			$sql = " /* $debugMsg */ " .
		      		       " INSERT INTO {$tables['execution_bugs']} (bug_id,execution_id)" .
	          		       " VALUES ('" . $db->prepare_string($tcase_exec['bug_id']) . "', {$execution_id} )";
	          		$db->exec_query($sql); 
				}
		    	$message=sprintf(lang_get('import_results_ok'),$tcase_identity,$version,$tester_name,
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
  	$execInfo['bug_id'] = (string) trim($xmlTCExec->bug_id); // BUGID 3331
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
  function: init_args()

  args :
  
  returns: 

*/
function init_args()
{
	$args=new stdClass();
  	$_REQUEST=strings_stripSlashes($_REQUEST);

  	$args->importType=isset($_REQUEST['importType']) ? $_REQUEST['importType'] : null;
  	$args->buildID=isset($_REQUEST['build']) ? intval($_REQUEST['build']) : null;
  	
  	$args->doUpload=isset($_REQUEST['UploadFile']) ? 1 : 0;
  	$args->userID=$_SESSION['userID'];
  	$args->tplan_id=$_SESSION['testplanID'];
  	$args->testprojectName=$_SESSION['testprojectName'];
  	
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

*/
function check_exec_values(&$db,&$tcase_mgr,&$user_mgr,$tcaseCfg,$execValues,&$columnDef)
{
	$tables = tlObjectWithDB::getDBTables(array('users','execution_bugs'));

    $checks=array('status_ok' => false, 'tcase_id' => 0, 'tester_id' => 0, 
                  'msg' => array()); 

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
    // BUGID 3331
    $execValues['bug_id'] = trim($execValues['bug_id']);
    if($checks['status_ok'] && $execValues['bug_id'] != '' )
    {
		if( ($field_len = strlen($execValues['bug_id'])) > $columnDef['bug_id']->max_length )
		{
		    $checks['status_ok']=false;
		    $checks['msg'][]=sprintf(lang_get('bug_id_invalid_len'),$field_len,$columnDef['bug_id']->max_length); 
		}
		
	}
    return $checks;
}
?>