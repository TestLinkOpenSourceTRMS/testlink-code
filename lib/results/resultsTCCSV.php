<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* @filesource	resultsTCCSV.php
*
* @internal revisions
*
*/
require('../../config.inc.php');

// ---------------------------------------------------------------------
// IMPORTANT NOTICE - DO NOT REMOVE
// PHPexcel register it's autoload() method
// Smarty also.
// TL register it's own autoload() in common.php
//
// If PHPExcel require is done AFTER common.php
// things does not work.
// ---------------------------------------------------------------------
require_once('../../third_party/codeplex/PHPExcel.php');   // Must be included BEFORE common.php
require_once('common.php');
require_once("lang_api.php");
require_once('results.class.php');
require_once('displayMgr.php');

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);

$labels = getLabels();
$tplan_mgr = new testPlanUrgency($db);
list($tproject_info,$tplan_info) = getAncestorsInfo($db,$tplan_mgr,$args->tproject_id,$args->tplan_id);
$gui = initializeGui($args,$tplan_mgr,$tproject_info,$tplan_info);

if( is_null($args->doReport) || is_null($args->build_id) )
{
	$smarty = new TLSmarty();
	$smarty->assign('gui', $gui);
	$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
	exit(); // Not needed just to remember that execution will not continue
}

$cfg = getCfg($gui);
$not_run_label = lang_get($cfg['results']['status_label']['not_run']);
$i18n = array(lang_get($cfg['results']['status_label']['failed']) => PHPExcel_Style_Color::COLOR_RED,
			  lang_get($cfg['results']['status_label']['passed']) => PHPExcel_Style_Color::COLOR_GREEN);

$testCaseCfg = config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;;


$re = new results($db, $tplan_mgr, $tproject_info, $tplan_info,
 				  ALL_TEST_SUITES,ALL_BUILDS,ALL_PLATFORMS);

// Get Results on map with access key = test case's parent test suite id
$executionsMap = $re->getSuiteList();

// lastResultMap provides list of all test cases in plan - data set includes title and suite names
$lastResultMap = $re->getMapOfLastResult();


$gui->matrix = array();
if ($lastResultMap != null) 
{
	$versionTag = $labels['tcversion_indicator'];
	$priorityCache  = null;
	foreach ($lastResultMap as $suiteId => $tsuite) 
	{
		foreach ($tsuite as $testCaseId => $platformSet) 
		{
		
			// Will WORK JUST ON ONE PLATFORM
			if( isset($platformSet[$args->platform_id]) )
			{
				$targetPlatform[$args->platform_id] = $platformSet[$args->platform_id];
				foreach($targetPlatform as $platformId => $tcase) 
				{
					$linkedTCVersion = $tcase['version'];
					$external_id = $testCasePrefix . $tcase['external_id'];

				    $tc_name = htmlspecialchars("{$external_id}:{$tcase['name']}",ENT_QUOTES);
				    
					$rowArray = null;
					$rowArray['tsuite'] = $tcase['suiteName'];
					$rowArray['tcname'] = $tcase['name'];
					$rowArray['tcversionid'] = $tcase['tcversion_id'];
					$rowArray['platformid'] = $platformId;
            	
					if ($gui->show_platforms)
					{
						$rowArray['platform'] = $gui->platformSet[$platformId];
					}
				
					$suiteExecutions = $executionsMap[$suiteId];
				    
				    // Remember the status of the last build that was executed
					// Use array format for status as specified in tlTable::$data
					$lastBuildRun = null;
            	
					// iterate over all builds and lookup results for current test case			
					// Keeps a list of status for every build
					$buildExecStatus = array();
					for ($idx = 0 ; $idx < 1; $idx++)  // FORCED TO DO JUST ONE LOOP
					{
						$resultsForBuild = null;
						$lastStatus = $resultsCfg['status_code']['not_run'];
						$cssClass = $gui->map_status_css[$lastStatus]; 
						
						// iterate over executions for this suite, look for 
						// entries that match current:
						// test case id,build id ,platform id
						$qta_suites=sizeOf($suiteExecutions);
						for ($jdx = 0; $jdx < $qta_suites; $jdx++) 
						{
							$execution_array = $suiteExecutions[$jdx];
							if (($execution_array['testcaseID'] == $testCaseId) && 
							    ($execution_array['build_id'] == $args->build_id) &&
							    ($execution_array['platform_id'] == $platformId))
							{
								$status = $execution_array['status'];
								$resultsForBuildText = $map_tc_status_code_langet[$status];
								$resultsForBuildText .= sprintf($versionTag,$execution_array['version']);
            	
								$resultsForBuild = array(
									"tester_id" => $execution_array['tester_id'],
									"value" => $status,
									"text" => $resultsForBuildText,
									"notes" => $execution_array['notes']);
            	
								$lastStatus = $execution_array['status'];
							}
						}
						
						// If no execution was found => not run
						if( $resultsForBuild === null )
						{
							$cssClass = $gui->map_status_css[$resultsCfg['status_code']['not_run']]; 
							$resultsForBuildText = $not_run_label;
							$resultsForBuildText .= sprintf($versionTag,$linkedTCVersion);
            	
							$resultsForBuild = array(
								"tester_id" => null,
								"value" => $cfg['results']['status_code']['not_run'],
								"text" => $resultsForBuildText,
								"notes" => '');
						}
						
						$buildExecStatus[$idx] = $resultsForBuild;
					} // end build for loop
			
				    $rowArray['results'] = $buildExecStatus;
				    $gui->matrix[] = $rowArray;
				    $rowArray = null;
        		}  // foreach Platform
        	} // isset	
        }	
    }
} // end if


$tcaseMgr = new testcase($db);
$tmp_dir = config_get('temp_dir'); 


// main description
$cellRange = range('A','Z');
$colors4cell = array('font' => array('color' => PHPExcel_Style_Color::COLOR_RED));
// $styleReportContext = array('font' => array('bold' => true),
// 							'borders' => array(	'outline' => 
// 								   				array('style' => PHPExcel_Style_Border::BORDER_THICK),
// 								   				'horizontal' => 
// 								   				array('style' => PHPExcel_Style_Border::BORDER_THIN),
// 								  ),
// 							'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
// 								  			'startcolor' => array( 'argb' => 'FF9999FF'))
// 						   );

$styleReportContext = array('font' => array('bold' => true));
$styleDataHeader = array('font' => array('bold' => true),
							'borders' => array(	'outline' => 
								   				array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
								   				'vertical' => 
								   				array('style' => PHPExcel_Style_Border::BORDER_THIN),
								  ),
							'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,
								  			'startcolor' => array( 'argb' => 'FF9999FF'))
						   );



$lines2write = array(array($labels['xls_jmexport_testproject'],$gui->tproject_name),
			   		 array($labels['xls_jmexport_testplan'],$gui->tplan_name),
			   		 array($labels['xls_jmexport_created'],
			   		  	   localize_dateOrTimeStamp(null,$dummy,'date_format',time())),
			    	 array($labels['xls_jmexport_author'],$args->author));

$tcaseHeader = array($labels['xls_jmexport_testcase'],$labels['xls_jmexport_testobjectives'],
					 $labels['xls_jmexport_preconditions'],$labels['xls_jmexport_steps'],
					 $labels['xls_jmexport_expected_result'],$labels['xls_jmexport_result'],
					 $labels['xls_jmexport_tester'],
					 $labels['xls_jmexport_result_comment']);
					 
// Need to get CF to know how many additional columns need to be added
// IMPORTANT NOTICE - cf are ordered by display order - fantastic!!!
$cfields = $tcaseMgr->get_linked_cfields_at_design(null,null,null,null,$args->tproject_id);
$cfieldsHeader = array();
$colOffSet = 0;
if( !is_null($cfields) )
{
	foreach($cfields as $cfid => $cf)
	{
		$cfieldsHeader[] = $cf['label'];
	}
	$colOffSet = count($cfieldsHeader);
}
$statusCellLetter = $cellRange[$colOffSet + array_search($labels['xls_jmexport_result'],$tcaseHeader)];

$dataHeaders = array_merge($cfieldsHeader,$tcaseHeader);

$fp = null;
$tmpfname = tempnam($tmp_dir, "resultsTCCSV-tmp");

$file2download = $gui->tproject_name . '_' . $gui->tplan_name;

if($gui->show_platforms)
{
 $file2download .= '_' . $gui->platformSet[$args->platform_id];

}
$file2download .= '_' . $gui->buildSet[$args->build_id]['name'];
$file2download = str_replace(' ', '_',$file2download);



$userCache = null;  // key: user id, value: display name


switch($args->outputFormat)
{
	case 'CSV':
		$fp = fopen($tmpfname, 'w');
		foreach ($lines2write as $fields) 
		{
    		fputcsv($fp, $fields);
		}
		// separator + data header
		fputcsv($fp, array('','','','','','','','','','',));
		fputcsv($fp,$dataHeaders);
	break;
	
	case 'XLSX':
		$objPHPExcel = new PHPExcel();
		$cellArea = "A1:"; 
		foreach($lines2write as $zdx => $fields)
		{
			$cdx = $zdx+1;
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A{$cdx}", current($fields))
												->setCellValue("B{$cdx}", end($fields));
		}
		$cellArea .= "A{$cdx}";
		$objPHPExcel->getActiveSheet()->getStyle($cellArea)->applyFromArray($styleReportContext);	
		
		
		$startingRow = count($lines2write) + 2; // MAGIC
		$cellArea = "A{$startingRow}:";
		foreach($dataHeaders as $zdx => $field)
		{
			$cellID = $cellRange[$zdx] . $startingRow; 
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
			$cellAreaEnd = $cellRange[$zdx];
		}
		$cellArea .= "{$cellAreaEnd}{$startingRow}";
		$objPHPExcel->getActiveSheet()->getStyle($cellArea)->applyFromArray($styleDataHeader);	

		$startingRow++;
	break;
}


$qta_loops = count($gui->matrix);
$target = array("<p>","</p>","<br />", "<br>");
$replace = array("","","","");

// $startingRow
$cellArea = "A{$startingRow}:";
for($idx = 0; $idx < $qta_loops; $idx++)
{
 	// get pulp
 	$item = $tcaseMgr->get_by_id(null,$gui->matrix[$idx]['tcversionid']);
 	$item = $item[0];

 	// make a big text with steps and expected results
 	if( !is_null($item['steps']) )
	{ 	
 		$stepBlob = '';
 		$resBlob = '';
 		$qta_steps = count($item['steps']);
 		$initial = '';
 		for($sdx=0; $sdx < $qta_steps; $sdx++)
 		{ 
 			$dummy = str_replace($target,$replace,$item['steps'][$sdx]['actions']);
 			$stepBlob .= $initial . '#' . $item['steps'][$sdx]['step_number'] . "\n" . $dummy . "\n";
 						 
 			$dummy = trim($item['steps'][$sdx]['expected_results']);
 			$dummy = ($dummy == '') ? 'N/A' : $dummy;
 			$dummy = str_replace($target,$replace,$dummy);
 			$resBlob .= $initial . '#' . $item['steps'][$sdx]['step_number'] . "\n" . $dummy . "\n"; 
 			
 			$initial = "\n";
 		}
	} 	


	// Get CF values
	$tcCfields = $tcaseMgr->get_linked_cfields_at_design(null,$gui->matrix[$idx]['tcversionid'],
														 null,null,$args->tproject_id);
 	$cfieldsValues = array();
	foreach($tcCfields as $cfid => $cf)
	{
		// $cfieldsValues[] = $cf['value'];   
		$cfieldsValues[] = $tcaseMgr->cfield_mgr->string_custom_field_value($cf,null);
	}
	
	$uaccessKey = &$gui->matrix[$idx]['results'][0]['tester_id'];
	$testerName = '';
	if( !is_null($uaccessKey) )
	{
		if( !isset($userCache[uaccessKey]) )
  		{
			$userObj = tlUser::getByID($db,$uaccessKey);
  			$userCache[$uaccessKey] = $userObj ? $userObj->firstName . ' ' . $userObj->lastName : $labels['undefined'];
  		}
		$testerName = $userCache[$uaccessKey];
  	}	
	
	$line2write = array_merge($cfieldsValues,
							  array($gui->matrix[$idx]['tcname'],
							  		str_replace($target,$replace,$item['summary']),
							  		str_replace($target,$replace,$item['preconditions']),
							  		$stepBlob,$resBlob,
							  		$gui->tc_status_labels[$gui->matrix[$idx]['results'][0]['value']],
							  		$testerName,
							  		str_replace($target,$replace,$gui->matrix[$idx]['results'][0]['notes']))
							  		);
	
	
	switch($args->outputFormat)
	{
		case 'CSV':
			fputcsv($fp,$line2write);	
		break;
		
		case 'XLSX':
			foreach($line2write as $ldx => $field)
			{
				$cellID = $cellRange[$ldx] . $startingRow; 
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellID, $field);
				if($cellRange[$ldx] == $statusCellLetter)
				{
					if( isset($i18n[$field]) )
					{
						$objPHPExcel->getActiveSheet()->getStyle($cellID)
									->getFont()->getColor()->setARGB($i18n[$field]);
					}	
				}
				 
			}
			$colQty = count($line2write);
			$cellEnd = $cellRange[$colQty-1] . $startingRow;
			$startingRow++;
		break;
		
	}
}


switch($args->outputFormat)
{
	case 'CSV':
		fclose($fp);
	break;
	
	case 'XLSX':
		$styleData = array('borders' => 
							array('outline' => 
								  array('style' => PHPExcel_Style_Border::BORDER_THIN),
								  'vertical' => 
								  array('style' => PHPExcel_Style_Border::BORDER_THIN),
								 ),
						   );
		$cellArea .= $cellEnd;
		$objPHPExcel->getActiveSheet()->getStyle($cellArea)->applyFromArray($styleData);
		
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($tmpfname);
	break;
}

$content = file_get_contents($tmpfname);
unlink($tmpfname);
$file2download .=  '.' . strtolower($args->outputFormat);
downloadContentsToFile($content,$file2download);
exit();


/**
 * 
 *
 */
function init_args()
{
	
	$iParams = array("format" => array(tlInputParameter::INT_N),
					 "doReport" => array(tlInputParameter::INT_N),	
					 "build_id" => array(tlInputParameter::INT_N),	
					 "platform_id" => array(tlInputParameter::INT_N),	
		             "tproject_id" => array(tlInputParameter::INT_N),
		             "tplan_id" => array(tlInputParameter::INT_N));

	$args = new stdClass();
	R_PARAMS($iParams,$args);
	$args->basehref = $_SESSION['basehref'];
	
	$args->userID = $_SESSION['userID'];
	$args->user = $_SESSION['currentUser'];
	$args->author = $args->user->firstName . ' ' . $args->user->lastName;
	$args->outputFormat = 'XLSX';
	
    return $args;
}


/**
 * 
 *
 *
 */
function initializeGui(&$argsObj,&$tplanMgr,$tprojectInfo,$tplanInfo)
{
	$guiObj = new stdClass();
	$guiObj->map_status_css = null;
	$guiObj->title = lang_get('title_test_report_all_builds');
	$guiObj->printDate = '';
	$guiObj->matrixCfg  = config_get('resultMatrixReport');
	$guiObj->matrixData = array();

	$guiObj->report_type = $argsObj->format;
	$guiObj->tplan_id = $argsObj->tplan_id;
	$guiObj->tproject_id = $argsObj->tproject_id;

	$guiObj->tplan_name = $tplanInfo['name'];
	$guiObj->tproject_name = $tprojectInfo['name'];

	$getOpt = array('outputFormat' => 'map');
	$guiObj->platformSet = $tplanMgr->getPlatforms($argsObj->tplan_id,$getOpt);
	$guiObj->show_platforms = !is_null($guiObj->platformSet);
	
	$guiObj->buildSet = $tplanMgr->get_builds($argsObj->tplan_id, ('activeBuilds' == 'activeBuilds'));

	$resultsCfg = config_get('results');
	foreach($resultsCfg['code_status'] as $code => $verbose)
	{
  		if( isset($resultsCfg['status_label'][$verbose]))
  		{
    		$guiObj->tc_status_labels[$code] = lang_get($resultsCfg['status_label'][$verbose]);
    		$guiObj->map_status_css[$code] = $resultsCfg['code_status'][$code] . '_text';
  		}
	}

	return $guiObj;
}


/**
 * 
 *
 *
 */
function getAncestorsInfo(&$dbHandler,$tplanMgr,$tprojectID,$tplanID)
{
	$tprojectMgr = new testproject($dbHandler);
	$tprojectInfo = $tprojectMgr->get_by_id($tprojectID);
	unset($tprojectMgr);
	
	$tplanInfo = $tplanMgr->get_by_id($tplanID);
	return array($tprojectInfo,$tplanInfo);
}


/**
 * 
 *
 *
 */
function getCfg($guiObj)
{
	$lbl = array('testplan' => lang_get('testplan'), 'testproject' => lang_get('testproject'));

	$cfg = array();
	$cfg['results'] = config_get('results');
	$cfg['urgency'] = config_get('urgency');
	
	$cfg['mail'] = new stdClass();
	$cfg['mail']->cc = ''; 
	$cfg['mail']->subject = $guiObj->title . ' : ' . $lbl['testproject'] . ' : ' . 
							$guiObj->tproject_name . ' : ' . $lbl['testplan'] . ' : ' . $guiObj->tplan_name;

	return $cfg;
}


function getLabels()
{
	$lbl = init_labels(array('design' => null, 'execution' => null, 
							 'testproject' => null,'testplan' => null, 'created' => null , 
							 'result_on_last_build' => null,
							 'tcversion_indicator' => null,	
							 'undefined' => null,
							 'xls_jmexport_testproject' => null,
							 'xls_jmexport_testplan' => null,
							 'xls_jmexport_created' => null,
							 'xls_jmexport_author' => null,
							 'xls_jmexport_testcase' => null,
							 'xls_jmexport_testobjectives' => null,
							 'xls_jmexport_preconditions' => null,
							 'xls_jmexport_steps' => null,
							 'xls_jmexport_expected_result' => null,
							 'xls_jmexport_result' => null,
							 'xls_jmexport_tester' => null,
							 'xls_jmexport_result_comment' => null) );
	return $lbl;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$env['tproject_id'] = isset($argsObj->tproject_id) ? $argsObj->tproject_id : 0;
	$env['tplan_id'] = isset($argsObj->tplan_id) ? $argsObj->tplan_id : 0;
	checkSecurityClearance($db,$userObj,$env,array('testplan_metrics'),'and');
}

?>