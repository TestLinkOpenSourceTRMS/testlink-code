<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * @author - Kevin Levy
 *
*/
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

$importType = isset($_POST['importType']) ? $_POST['importType'] : null;
$buildID = isset($_GET['build']) ? intval($_GET['build']) : null;

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
		$file_check = check_valid_ftype($_FILES['uploadedFile'],$importType);
		
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

$smarty = new TLSmarty();
$smarty->assign('import_title',$import_title);  
$smarty->assign('buildID', $buildID);
$smarty->assign('file_check',$file_check);  
$smarty->assign('resultMap',$resultMap); 
$smarty->assign('tcFormatStrings',$g_tcFormatStrings);
$smarty->assign('importTypes',$g_tcImportTypes);
$smarty->assign('testprojectName', $testprojectName);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->assign('bImport',strlen($importType));
$smarty->display('resultsImport.tpl');

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

function saveImportedResultData(&$db,$resultData,&$tplan_id,$container_id,$userID,$buildID)
{
	if (!$resultData)
		return;
	
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
		$result = $tc['result'];
		
		// TO-DO test result must be p,f, or n
		
		$notes = $tc['notes'];
		$message = null;
		$linked_cases = $tplan_mgr->get_linked_tcversions($tplan_id, $id);
		$info_on_case = $linked_cases[$id];
		if ($linked_cases) {
			$tcversion_id = $info_on_case['tcversion_id'];
			$message = "test case $id is in the test plan, tcversion_id = $tcversion_id ";
           $notes = $db->prepare_string(trim($notes));		
		   $db_now = $db->db_now();
		   $sql="INSERT INTO executions (build_id,tester_id,status,testplan_id,tcversion_id,execution_ts,notes)
	      	  VALUES ({$buildID}, {$userID},'{$result}',{$tplan_id}, {$tcversion_id},{$db_now},'{$notes}')";
	    $db->exec_query($sql); 

		}
		else {
			$message = "test case $id not found in test plan - no data inserted";
		}
	
		$resultMap[] = array($id, $message);
	}
	return $resultMap;
}

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
