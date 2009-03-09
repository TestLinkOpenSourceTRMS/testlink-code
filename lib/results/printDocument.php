<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: printDocument.php,v $
 *
 * @version $Revision: 1.24 $
 * @modified $Date: 2009/03/09 20:13:03 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * SCOPE:
 * Generate documentation Test report based on Test plan data.
 *
 * Revisions :
 *  20090309 - franciscom - BUGID - use test case execution while printing test plan
 * 	20090213 - havlatm - support for OpenOffice
 *	20081207 - franciscom - BUGID 1910 - fixed estimated execution time computation.  
 *	20070509 - franciscom - added Contribution BUGID
 *
 */
require_once('../../config.inc.php');
require_once("common.php");
require_once("print.inc.php");
require_once("displayMgr.php");

$dummy = null;
$tree = null;
$generatedText = null;					
$doc_info = new stdClass(); // gather title, author, product, test plan, etc.
$doc_data = new stdClass(); // gather content and tests related data

testlinkInitPage($db);
$args = init_args();
$item_type = $args->level;

// Elements in this array must be updated if $arrCheckboxes, in printDocOptions.php is changed.
$printingOptions = array ( 'toc' => 0,'body' => 0,'summary' => 0,'header' => 0, 
		'passfail' => 0, 'author' => 0, 'requirement' => 0, 'keyword' => 0, 
		'testplan' => 0, 'metrics' => 0);

foreach($printingOptions as $opt => $val)
{
	$printingOptions[$opt] = (isset($_REQUEST[$opt]) && ($_REQUEST[$opt] == 'y'));
}					

$resultsCfg = config_get('results');
$status_descr_code = $resultsCfg['status_code'];
$status_code_descr = array_flip($status_descr_code);

$tproject = new testproject($db);
$tree_manager = &$tproject->tree_manager;
$hash_descr_id = $tree_manager->get_available_node_types();
$hash_id_descr = array_flip($hash_descr_id);

$decoding_hash = array('node_id_descr' => $hash_id_descr,
                     'status_descr_code' =>  $status_descr_code,
                     'status_code_descr' =>  $status_code_descr);

$order_cfg = null;  // 20090309 - BUGID
switch ($args->doc_type)
{
	case 'testspec': 
		$doc_info->type = DOC_TEST_SPEC; 
		$doc_info->type_name = lang_get('title_test_spec');
		break;
	
	case 'testplan': 
		$doc_info->type = DOC_TEST_PLAN; 
		$doc_info->type_name = lang_get('test_plan');
  	$order_cfg = array("type" =>'exec_order',"tplan_id" => $args->tplan_id);
		break;
	
	case 'testreport': 
		$doc_info->type = DOC_TEST_REPORT; 
		$doc_info->type_name = lang_get('test_report');
		break;
		
	case 'reqspec': 
		$doc_info->type = DOC_REQ_SPEC; 
		$doc_info->type_name = lang_get('req_spec');
		break;
		
	default:
		die ('printDocument.php> Invalid document type $_REQUEST["type"]');
}


$test_spec = $tree_manager->get_subtree($args->itemID,
		                                    array('testplan'=>'exclude me', 'requirement_spec'=>'exclude me', 
		                                          'requirement'=>'exclude me'),
		                                    array('testcase'=>'exclude my children', 
		                                          'requirement_spec'=> 'exclude my children'),
		                                    null,null,RECURSIVE_MODE,$order_cfg);

$tproject_info = $tproject->get_by_id($args->tproject_id);
$doc_info->tproject_name = htmlspecialchars($tproject_info['name']);
$doc_info->tproject_scope = $tproject_info['notes'];


$user = tlUser::getById($db,$_SESSION['userID']);
if ($user)
{
	$doc_info->author = htmlspecialchars($user->getDisplayName());
}


switch ($doc_info->type)
{
    case DOC_TEST_SPEC: // test specification
		switch($item_type)
		{
			case 'testproject':
				$tree = &$test_spec;
				$doc_info->title = $doc_info->project_name;
			break;
    	      
			case 'testsuite':
    	      	$tsuite = new testsuite($db);
    	  	    $tInfo = $tsuite->get_by_id($args->itemID);
    	  	    $tInfo['childNodes'] = isset($test_spec['childNodes']) ? $test_spec['childNodes'] : null;
    	  	    $tree['childNodes'] = array($tInfo);
				$doc_info->title = isset($tInfo['name']) ? $args->tproject_name .
    	  	      	$tlCfg->gui_title_separator_2.$tInfo['name'] : $args->tproject_name;
    	  	  break;    
    }
    break;
    
    case DOC_TEST_PLAN:
    case DOC_TEST_REPORT:
		    $tplan_mgr = new testplan($db);
		    $tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
		    $doc_info->testplan_name = htmlspecialchars($tplan_info['name']);
		    $doc_info->testplan_scope = $tplan_info['notes'];
		    $doc_info->title = $doc_info->testplan_name;
		
        $tcase_filter = null;
        $execid_filter = null;
        $executed_qty = 0;
         
        switch($item_type)
        {
			      case 'testproject': // all
    	   	      $tp_tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id);
    	   	      $tree = &$test_spec;
    	   	      if (!$tp_tcs)
    	   	      {
    	   	    	   $tree['childNodes'] = null;
    	   	      }
    	   	      //@TODO:REFACTOR	
    	   	      prepareNode($db,$tree,$decoding_hash,$dummy,
    	   	                  $dummy,$tp_tcs,SHOW_TESTCASES,null,null,0,1,0);
            break;
    	       
			      case 'testsuite':
				        $tsuite = new testsuite($db);
				        $tInfo = $tsuite->get_by_id($args->itemID);
                 
    	          $children_tsuites = $tree_manager->get_subtree_list($args->itemID,$hash_descr_id['testsuite']);
    	          if( !is_null($children_tsuites) and strlen(trim($children_tsuites)) > 0)
    	          {
                	$branch_tsuites = explode(',',$children_tsuites);
                }
                $branch_tsuites[]=$args->itemID;
    	   	       
    	   	       
    	   	      $tp_tcs = $tplan_mgr->get_linked_tcversions($args->tplan_id,null,0,null,null,null,0,null,false,null, 
    	   	                                                   $branch_tsuites);
    	   	      $tcase_filter=!is_null($tp_tcs) ? array_keys((array)$tp_tcs): null;
    	         
    	   	      $tInfo['node_type_id'] = $hash_descr_id['testsuite'];
    	   	      $tInfo['childNodes'] = isset($test_spec['childNodes']) ? $test_spec['childNodes'] : null;
    	   	       
    	   	      //@TODO: schlundus, can we speed up with NO_EXTERNAL?
    	   	      prepareNode($db,$tInfo,$decoding_hash,$dummy,$dummy,$tp_tcs,SHOW_TESTCASES);
    	   	      $doc_info->title = isset($tInfo['name']) ? $tInfo['name'] : $doc_info->testplan_name;
                  
    	   	      $tree['childNodes'] = array($tInfo);
            break;
        }  // switch($item_type)
         
      // Create list of execution id, that will be used to compute execution time if
    	// CF_EXEC_TIME custom field exists and is linked to current testproject
    	$doc_data->statistics = null;                                            
 		  if ($printingOptions['metrics'])
 		  {
       	$executed_qty=0;
    	 	if ($tp_tcs)
    	 	{
    	 		foreach($tp_tcs as $tcase_id => $info)
			    {
	    	         if( $info['exec_status'] != $status_descr_code['not_run'] )
	        	     {  
	            	     $execid_filter[] = $info['exec_id'];
	                	 $executed_qty++;
		             }    
		         }    
    		}
	        $doc_data->statistics['estimated_execution']['minutes'] = 
	        		$tplan_mgr->get_estimated_execution_time($args->tplan_id,$tcase_filter);
    	    $doc_data->statistics['estimated_execution']['tcase_qty'] = count($tp_tcs);
         
			if( $executed_qty > 0)
        	{ 
				$doc_data->statistics['real_execution']['minutes'] = 
						$tplan_mgr->get_execution_time($args->tplan_id,$execid_filter);
             	$doc_data->statistics['real_execution']['tcase_qty'] = $executed_qty;
         	}
 		}
    break;
}


// ----- rendering logic -----
$generatedText = renderHTMLHeader($doc_info->type.' '.$doc_info->title,$_SESSION['basehref']);
$generatedText .= renderFirstPage($doc_info);
//$generatedText .= renderToc($doc_data);

if($tree)
{
	$tree['name'] = $args->tproject_name;
	$tree['id'] = $args->tproject_id;
	$tree['node_type_id'] = $hash_descr_id['testproject'];
	switch ($doc_info->type)
	{
		case DOC_TEST_SPEC:
			$generatedText .= renderSimpleChapter(lang_get('scope'), $doc_info->tproject_scope);
			$generatedText .= renderTestSpecTreeForPrinting($db,$tree,$item_type,$printingOptions,null,0,1,$args->user_id);
		break;
	
		case DOC_TEST_PLAN:
			if ($printingOptions['testplan'])
			{
				$generatedText .= renderSimpleChapter(lang_get('scope'), $doc_info->testplan_scope);
			}
				
		case DOC_TEST_REPORT:
			$generatedText .= renderTestPlanForPrinting($db,$tree,$item_type,$printingOptions,null,0,1,
		                                             $args->user_id,$args->tplan_id,$args->tproject_id);
			if (($doc_info->type == DOC_TEST_REPORT) && ($printingOptions['metrics']))
			{
				$generatedText .= buildTestPlanMetrics($doc_data->statistics);
			}	
		break;
	}

	$generatedText .= renderEof();
}

// echo '>>>'.$printingOptions['metrics'];
// print_r($printingOptions);
// print_r($_REQUEST);

// add application header to HTTP 
if (($args->format == 'format_odt') || ($args->format == 'format_msword'))
{
	flushHttpHeader($args->format, DOC_TEST_REPORT);
}

// send out the data
// print_r($doc_data->statistics);
echo $generatedText;


/** Process input data */
function init_args()
{
	$args = new stdClass();
	$args->doc_type = $_REQUEST['type'];
	$args->level = isset($_REQUEST['level']) ?  $_REQUEST['level'] : null;
	$args->format = isset($_REQUEST['format']) ? $_REQUEST['format'] : null;
	$args->itemID = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$args->tplan_id = isset($_REQUEST['docTestPlanId']) ? $_REQUEST['docTestPlanId'] : 0;
	
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';
	$args->user_id = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

	return $args;
}
?>