<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: resultsImport.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2007/12/02 17:08:16 $  by $Author: franciscom $

 * @author - Kevin Levy
 *
 * rev :
 *      20071101 - franciscom - added manish contribution
 *      20070904 - franciscom - refactoring
*/
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

$template_dir='results/';

$importType = isset($_POST['importType']) ? $_POST['importType'] : null;
$buildID = isset($_GET['build']) ? intval($_GET['build']) : null;

$ref = $_SERVER['HTTP_REFERER'];
$url_array = split('[?=&]',$ref);

if( in_array('build_id',$url_array) ) {
	$buildIdIndex = array_search('build_id',$url_array) + 1;
	$buildID = $url_array[$buildIdIndex];
}

if ($buildID == null) {
	$buildID = isset($_POST['build']) ? intval($_POST['build']) : null;
}

$bRecursive = 0;
$container_id = 0;
$containerType = 0;

$location = isset($_POST['location']) ? strings_stripSlashes($_POST['location']) : null; 
$resultMap = null;
$do_upload = isset($_REQUEST['UploadFile']) ? 1 : 0;

$userID = $_SESSION['userID'];
$tproject_id = $_SESSION['testprojectID'];
$tplan_id = $_SESSION['testPlanId'];

$testprojectName = $_SESSION['testprojectName'];

$dest = TL_TEMP_PATH . session_id()."-importtcs.csv";
$file_check = array('status_ok' => 1, 'msg' => 'ok');

$import_title = lang_get('title_results_import_to');
$container_description=lang_get('import_results');

if ($do_upload)
{
	// check the uploaded file
	$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
		
	if (($source != 'none') && ($source != ''))
	{ 
		$file_check['status_ok']=1;
		if($file_check['status_ok'])
		{
			if (move_uploaded_file($source, $dest))
			{
				switch($importType)
				{
					case 'XML':
					$pcheck_fn  = "check_xml_tc_tsuite";
					$pimport_fn = "importTestCaseDataFromXML";
					break;
				}
				if ($pcheck_fn)
				{
					$file_check = $pcheck_fn($dest,$bRecursive);
					if($file_check['status_ok'])
					{
						if ($pimport_fn)
						{
							$resultMap = $pimport_fn($db,$dest,$tplan_id,$userID,$buildID);
						}
					}
				}
			}
		}
	}
	else
	{
		$file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
		$importType = null;
	}
}

$import_file_types = array("XML" => "XML");


$smarty = new TLSmarty();
$smarty->assign('import_title',$import_title);  
$smarty->assign('buildID', $buildID);
$smarty->assign('file_check',$file_check);  
$smarty->assign('resultMap',$resultMap); 
$smarty->assign('importTypes',$import_file_types);
$smarty->assign('testprojectName', $testprojectName);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->assign('bImport',strlen($importType));
$smarty->display($template_dir .'resultsImport.tpl');
?>

<?php
/*
  function: 

  args :
  
  returns: 

*/
function importTestCaseDataFromXML(&$db,$fileName,&$tplan_id,$userID, $buildID)
{	
	$xmlTCs = null;
	$resultMap  = null;
	$dom = domxml_open_file($fileName);
	
	if ($dom)
	{
		$root = $dom->document_element();
		$resultMap = importResults($db,$root,$parentID,$tplan_id,$userID, $buildID);
	}
	return $resultMap;
}

/*
  function: 

  args :
  
  returns: 

*/
function importResults(&$db, &$node, &$parentID, &$tplan_id, &$userID, $buildID) {
	$resultMap = null;
	if ($node->tagname == 'results')
	{
		$xmlTCs = $node->get_elements_by_tagname("testcase");
		$resultData = importTCsFromXML($xmlTCs);
		if ($resultData) {
			$resultMap = saveImportedResultData($db,$resultData,$tplan_id,$parentID,$userID,$buildID);
		}
	}
	return $resultMap;
}

/*
  function: 

  args :
  
  returns: 

*/
function saveImportedResultData(&$db,$resultData,&$tplan_id,$container_id,$userID,$buildID)
{
	if (!$resultData)
		return;
	
	$tc_status=config_get('tc_status');
	
	$resultMap = array();
	$tplan_mgr = null;
	$tc_qty = sizeof($resultData);
	if($tc_qty)
	{
		$tplan_mgr = new testplan($db);
	}
	for($i = 0; $i <$tc_qty ;$i++)
	{
		$tc = $resultData[$i];
		$id = $tc['id'];
		if(!$id){
			$tcname = $tc['name'];
			$query = "SELECT nodes.id from nodes_hierarchy nodes WHERE nodes.name = '{$tcname}'";
			$result = $db->exec_query($query);
			$id = $db->db_result($result);
			error_log("Id is:- ".$id);
		}
		$result = $tc['result'];
		$result_is_acceptable = false;

		if ($result == $tc_status['passed'] || $result == $tc_status['blocked'] ||
		    $result == $tc_status['failed']) { 
			$result_is_acceptable = true;
		}

				
		$notes = $tc['notes'];
		$message = null;
		$linked_cases = $tplan_mgr->get_linked_tcversions($tplan_id, $id);
		$info_on_case = $linked_cases[$id];
		
		if (!$linked_cases){
			$message = sprintf(lang_get('import_results_tc_not_found'),$id);
  	}
		else if (!$result_is_acceptable) {
			$message = sprintf(lang_get('import_results_invalid_result'),$id);
		} 
		else {
			$tcversion_id = $info_on_case['tcversion_id'];
			$message = sprintf(lang_get('import_results_tc_exists'),$id,$tcversion_id);
			
      $notes = $db->prepare_string(trim($notes));		
		  $db_now = $db->db_now();
		  $sql="INSERT INTO executions (build_id,tester_id,status,testplan_id,tcversion_id,execution_ts,notes)
	      	  VALUES ({$buildID}, {$userID},'{$result}',{$tplan_id}, {$tcversion_id},{$db_now},'{$notes}')";
	    $db->exec_query($sql); 
		}
		
		$resultMap[] = array($id, $message);
	}
	return $resultMap;
}

/*
  function: 

  args :
  
  returns: 

*/
function importTCsFromXML($xmlTCs)
{
	$tcs = null;
	if (!$xmlTCs) {
		return $tcs;
	}
	$j = 0;
	for($i = 0;$i < sizeof($xmlTCs);$i++)
	{
		$xmlTC = $xmlTCs[$i];
		if ($xmlTC->node_type() != XML_ELEMENT_NODE)
			continue;
		$tc = importTCFromXML($xmlTC);
		if ($tc)
		{
			$tcs[$j++] = $tc;
		}
	}
	
	return $tcs;
}

/*
  function: importTCFromXML()

  args :
  
  returns: 

*/
function importTCFromXML(&$xmlTC)
{
	if (!$xmlTC)
		return null;
		
	$tc = null;
	
	$tc['id'] = $xmlTC->get_attribute("id");
	if(!$tc['id']) {
		$tc['name'] = $xmlTC->get_attribute("name");
	}
	
	$tc['result'] = trim(getNodeContent($xmlTC,"result"));
	$tc['notes'] = trim(getNodeContent($xmlTC,"notes"));
	$tc['customField1'] = trim(getNodeContent($xmlTC,"customField1"));
	return $tc; 		
}


/*
  function: check_valid_ftype()

  args :
  
  returns: 

*/
function check_valid_ftype($upload_info,$import_type)
{
	$ret = array();
	$ret['status_ok'] = 0;
	$ret['msg']='ok';
	
	$mime_types = array();
	$import_type = strtoupper($import_type);
	
	$mime_import_types['text/xml'] = array('XML' => 'XML');
	
	if(isset($mime_import_types[$upload_info['type']])) 
	{
		if(isset($mime_import_types[$upload_info['type']][$import_type]))
		{
			$ret['status_ok'] = 1;
			if( isset($mime_types['check_ext'][$upload_info['type']]))
			{
				$path_parts = pathinfo($upload_info['name']);
				if($path_parts['extension'] != $mime_types['check_ext'][$upload_info['type']])
				{
					$status_ok = 0;    
					$ret['msg'] = lang_get('file_is_not_text');
				}
			}
		}
		else
			$ret['msg'] = lang_get('file_is_not_ok_for_import_type');
	}
	else
		$ret['msg'] = lang_get('file_is_not_xml');
	
	return $ret;
}


/*
  function: 

           Check if at least the file starts seems OK

*/
function check_xml_tc_tsuite($fileName,$bRecursive)
{
	$dom = domxml_open_file($fileName);
	$file_check = array('status_ok' => 0, 'msg' => 'dom_ko');    		  
	if ($dom)
	{
		$file_check = array('status_ok' => 1, 'msg' => 'ok');    		  
		$root = $dom->document_element();
		if($root->tagname != 'results') {
			$file_check=array('status_ok' => 0, 'msg' => lang_get('wrong format for results file'));
		}
	}
	return $file_check;
}
?>
