<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: print.inc.php,v $
 * @version $Revision: 1.51 $
 * @modified $Date: 2008/09/21 19:02:48 $ by $Author: schlundus $
 *
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 *
 * Functions for support printing of documents.
 *
 * rev:
 *     20080820 - franciscom - added contribution (BUGID 1670)
 *                             Test Plan report:
 *                             Total Estimated execution time will be printed
 *                             on table of contents. 
 *                             Compute of this time can be done if: 
 *                             - Custom Field with Name CF_ESTIMATED_EXEC_TIME exists
 *                             - Custom Field is managed at design time
 *                             - Custom Field is assigned to Test Cases
 *                             
 *                             Important Note:
 *                             Lots of controls must be developed to avoid problems 
 *                             presenting with results, when user use time with decimal part.
 *                             Example:
 *                             14.6 minuts what does means? 
 *                             a) 14 min and 6 seconds?  
 *                             b) 14 min and 6% of 1 minute => 14 min 3.6 seconds ?
 *
 *                             Implementation at (20080820) is very simple => is user
 *                             responsibility to use good times (may be always interger values)
 *                             to avoid problems.
 *                             Another choice: TL must round individual times before doing sum.
 *
 *     20080819 - franciscom - renderTestCaseForPrinting() - removed mysql only code
 *
 *     20080602 - franciscom - display testcase external id
 *     20080525 - havlatm - fixed missing test result
 *     20080505 - franciscom - renderTestCaseForPrinting() - added custom fields
 *     20080418 - franciscom - document_generation configuration .
 *                             removed tlCfg global coupling
 *
 *     20071014 - franciscom - renderTestCaseForPrinting() added printing of test case version
 *     20070509 - franciscom - changes in renderTestSpecTreeForPrinting() interface
 */

require_once("exec.inc.php");
require_once("requirement_mgr.class.php");


/**
 * print HTML header
 * Standard: HTML 4.01 trans (because is more flexible to bugs in user data)
 */
function printHeader($title, $base_href)
{
	global $tlCfg;	

	$output = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
	$output .= "<html>\n<head>\n";
	$output .= '<meta http-equiv="Content-Type" content="text/html; charset=' . $tlCfg->charset . '" />'.
		"\n<base href=\"".$base_href."\"/>\n";
	$output .= '<title>' . htmlspecialchars($title). "</title>\n";
	$output .= '<link type="text/css" rel="stylesheet" href="'. $base_href . $tlCfg->document_generator->css_template ."\" />\n";
	$output .= '<style type="text/css" media="print">.notprintable { display:none;}</style>';
	$output .= "\n</head>\n";

	return $output;
}

/**
  print HTML - initial page of document
*/
function printFirstPage(&$db, $item_type, $title, $tproject_info, 
                        $userID, $printingOptions=null, $tplan_info=null)
{
	$docCfg = config_get('document_generator');
	
	$g_date_format = config_get('date_format');
	$tproject_name = htmlspecialchars($tproject_info['name']);
	$tproject_notes = $tproject_info['notes'];
  
	$author = null;
	$user = tlUser::getById($db,$userID);
	if ($user)
		$author = htmlspecialchars($user->getDisplayName());

	$output = "<body>\n<div>";

	if ($docCfg->company_name != '' )
		$output .= '<div style="float:right;">' . htmlspecialchars($docCfg->company_name) ."</div>\n";
	$output .= '<div>'. $tproject_name . "</div><hr />\n";

	if ($docCfg->company_logo != '' )
	{
		$output .= '<p style="text-align: center;"><img alt="TestLink logo" title="configure using $tlCfg->company->logo_image"'.
        	     ' src="' . $_SESSION['basehref'] . TL_THEME_IMG_DIR . $docCfg->company_logo . '" /></p>';
	}
	$output .= "</div>\n";

	/* Print title */
	$output .= '<div class="doc_title">';

	if(is_null($tplan_info))
	{
		$output .= lang_get('title_test_spec');
	}
	else
	{
		if ($printingOptions['passfail'])
			$output .= lang_get('test_report');
		else
			$output .= lang_get('test_plan');

   	$output .=  ' ' . htmlspecialchars($tplan_info['name']);
	}

	if($title != '')
	{
		$output .= '<p>' . lang_get($item_type) . ' - ' . htmlspecialchars($title) . "</p>\n";

    	// Based on contribution (BUGID 1670)
		if(!is_null($tplan_info))
	  	{
		   	$tplan_mgr = new testplan($db);
	        $estimated_minutes = $tplan_mgr->get_estimated_execution_time($tplan_info['id']);
	        if( $estimated_minutes > 60)
	    		$estimated_string = lang_get('estimated_time_hours') . round($estimated_minutes/60,2) ;
		    else
		        $estimated_string = lang_get('estimated_time_min') . $estimated_minutes;

		        $output .= '<p style="font-size:14; text-align: center; font-weight: bold;">' .
			               $estimated_string . "</p>\n";
	    }
	}
	$output .= "</div>\n";


	// Print summary on the first page
	$output .= '<div class="summary">' .
		         '<p id="prodname">'. lang_get('project') .": " . $tproject_name . "</p>\n";

	$output .= '<p id="author">' . lang_get('author').": " . $author . "</p>\n" .
		         '<p id="printedby">' . lang_get('printed_by_TestLink_on')." ".
		         strftime($g_date_format, time()) . "</p></div>\n";

	if ($docCfg->company_copyright != '')
		$output .= '<div class="pagefooter" id="copyright">' . 
		           htmlspecialchars($docCfg->company->company_copyright)."</div>\n";
		           
	if ($docCfg->confidential_msg != '')
		$output .= '<div class="pagefooter" id="confidential">' . 
		           htmlspecialchars($docCfg->confidential_msg)."</div>\n";

	if (strlen($tproject_notes))
		$output .= '<h1>'.lang_get('introduction').'</h1><p id="prodnotes">'. $tproject_notes . "</p>\n";


	return $output;
}


/*
  function: renderTestSpecTreeForPrinting

  args :

        [$tplan_id]

  returns:

  rev :
       20070509 - franciscom - added $tplan_id in order to refactor and
                               add contribution BUGID

*/
function renderTestSpecTreeForPrinting(&$db,&$node,$item_type,&$printingOptions,
                                       $tocPrefix,$tcCnt,$level,$user_id,$tplan_id=0)
{
	$tree_mgr = new tree($db);
 	$map_id_descr = array_flip($tree_mgr->get_available_node_types());
	$code = null;
	$bCloseTOC = 0;
	if (isset($node['node_type_id']))
	{
	  	$verbose_node_type = $map_id_descr[$node['node_type_id']];
		switch($verbose_node_type)
		{
			case 'testproject':
				$code .= renderProjectNodeForPrinting($db,$node,$printingOptions,$item_type,
				                                      $printingOptions['title'],$user_id,$tplan_id);
				break;

			case 'testsuite':
				if (!is_null($tocPrefix))
					$tocPrefix .= ".";
				$tocPrefix .= $tcCnt;
				$code .= renderTestSuiteNodeForPrinting($db,$node,$printingOptions,$tocPrefix,$level);
				break;

			case 'testcase':
				$code .= renderTestCaseForPrinting($db,$node,$printingOptions,$level,$tplan_id);
				break;
		}
	}
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = $node['childNodes'];
		$tsCnt = 0;
   		$children_qty = sizeof($childNodes);
		for($i = 0;$i < $children_qty ;$i++)
		{
			$current = $childNodes[$i];
			if(is_null($current))
				continue;

			if (isset($current['node_type_id']) && $map_id_descr[$current['node_type_id']] == 'testsuite')
				$tsCnt++;
			$code .= renderTestSpecTreeForPrinting($db,$current,$item_type,$printingOptions,
			                                       $tocPrefix,$tsCnt,$level+1,$user_id,$tplan_id);
		}
	}
	if (isset($node['node_type_id']) && $map_id_descr[$node['node_type_id']] == 'testproject')
	{
		if ($printingOptions['toc'])
		{
			$printingOptions['tocCode'] .= '</div><hr />';
			$code = str_replace("{{INSERT_TOC}}",$printingOptions['tocCode'],$code);
		}
		$code .= "</body></html>";
	}

	return $code;
}

/*
  function: renderTestCaseForPrinting

  args :

  returns:

  rev :
       20080819 - franciscom - removed mysql only code
       20071014 - franciscom - display test case version
       20070509 - franciscom - added Contribution

*/
function renderTestCaseForPrinting(&$db,&$node,&$printingOptions,$level,$tplan_id=0)
{
	global $g_tc_status_verbose_labels;
	global $g_tc_status;
	global $tlCfg;

	$code = null;
	$tcInfo = null;
  	$tcResultInfo = null;
	  
	$testcaseCfg = config_get('testcase_cfg');
	$tc_mgr = new testcase($db);
	$id = $node['id'];
	$versionID = isset($node['tcversion_id']) ? $node['tcversion_id'] : TC_LATEST_VERSION;
 	  
 	// needed to get external id
 	$tcInfo = $tc_mgr->get_by_id($id,$versionID);
    if ($tcInfo)
		$tcInfo = $tcInfo[0];  
 	
	$prefix = $tc_mgr->getPrefix($id);
 	$external_id = $prefix . $testcaseCfg->glue_character . $tcInfo['tc_external_id'];
 	  
	$name = htmlspecialchars($node['name']);
  	$cfields = array('specScope' => '', 'execScope' => '');
  	$printType='testproject';
  	if($tplan_id > 0)
  	{
     	$printType = 'testplan';
     	$cfield_scope = 'execution';
  	}
	
  	// get custom fields that has specification scope
	$cfields['specScope'] = $tc_mgr->html_table_of_custom_field_values($id);
  	if(strlen(trim($cfields['specScope'])) > 0 )
  	{
		$cfields['specScope'] = str_replace('<td class="labelHolder">','<td>',$cfields['specScope']);  
		$cfields['specScope'] = str_replace('<table>','',$cfields['specScope']);
		$cfields['specScope'] = str_replace('</table>','',$cfields['specScope']);
  	}
  
	if ($printingOptions['toc'])
	{
	    $printingOptions['tocCode']  .= '<p style="padding-left: '.(15*$level).'px;"><a href="#tc' . $id . '">' .
	     	                 $name . '</a></p>';
	  	$code .= "<a name='tc" . $id . "'></a>";
	}
    
 	$code .= '<div><table class="tc" width="90%">';
 	$code .= '<tr><th colspan="2">' . lang_get('test_case') . " " . htmlspecialchars($external_id) . ": " . $name;

	// add test case version
	// mht: is it possible that version is not set? - remove condition
	if($tlCfg->document_generator->tc_version_enabled && isset($node['version']) ) 
	{
		$code .= '&nbsp;<span style="font-size: 80%;"' . $tlCfg->gui->role_separator_open . lang_get('version') . $tlCfg->gui->title_sep_1 . 
	  			$node['version'] . $tlCfg->gui->role_separator_close . '</span>';
	}
 	$code .= '</th></tr>';

  	if ($printingOptions['author'])
  	{
	    $authorName = null;
	    $user = tlUser::getByID($db,$tcInfo['author_id']);
	    if ($user)
	    	$authorName = $user->getDisplayName();
	    $code .= '<tr><td width="20%" valign="top"><span class="label">'.lang_get('author').':</span></td>';
        $code .= '<td>' . htmlspecialchars($authorName) . "</td></tr>";
  	}

  	if (($printingOptions['body'] || $printingOptions['summary']))
	{
		$code .= "<tr><td colspan=\"2\"><span class='label'>".lang_get('summary').":</span><br />" .  $tcInfo['summary'] . "</td></tr>";
	}

  	if (($printingOptions['body']))
	{
	   	$code .= "<tr><td colspan=\"2\"><span class='label'>".lang_get('steps').":</span><br />" .  $tcInfo['steps'] . "</td></tr>";
	   	$code .= "<tr><td colspan=\"2\"><span class='label'>".lang_get('expected_results').":</span><br />" .  $tcInfo['expected_results'] . "</td></tr>";
	}
  
    $code .= $cfields['specScope'];


	// generate test results
	if ($printingOptions['passfail'])
	{

		// contribution by SDM 
		// printing testresult and notes in 'Print Test Plan'
		// @TODO refactorize
		// $id2 = $id+=1;
    // 
    // $query = mysql_query("SELECT status, execution_ts, notes FROM executions" .
		// 		" WHERE tcversion_id = $id2 AND testplan_id = $tplan_id" .
		// 		" and execution_ts = (select MAX(execution_ts) from executions" .
		// 		" where tcversion_id = $id2 and testplan_id = $tplan_id" .
		// 		" group by tcversion_id, testplan_id)");
    // 
		// $result = mysql_fetch_assoc($query);

    // 20080819 - franciscom - refactoring
		$sql =  " SELECT status, execution_ts, notes FROM executions" .
				    " WHERE tcversion_id = $versionID AND testplan_id = $tplan_id" .
				    " and execution_ts = (select MAX(execution_ts) from executions" .
				    " where tcversion_id = $versionID and testplan_id = $tplan_id" .
				    " group by tcversion_id, testplan_id)";
                                   
		$result = $db->get_recordset($sql);
                                   
	    if (!$result) 
	    {
			    $tcstatus2 = lang_get("test_status_not_run");
			    $tcnotes2 = lang_get("not_aplicable");
	    }
	    else
	    {
			    $tcstatus2 = $result[0]['status'];
			    $tcstatus2 = lang_get($g_tc_status_verbose_labels[array_search($tcstatus2, $g_tc_status)]);
			    $tcnotes2 = $result[0]['notes'];
		  }
		
		$code .= '<tr><td width="20%" valign="top"><span class="label">'.lang_get('passfail').
				':</span><br /><span style="text-align:center; padding:10px;">'.$tcstatus2.'</span></td><td><u>'.
				lang_get('testnotes') . ':</u><br />' . $tcnotes2 . "</td></tr>\n";
	}


	// collect REQ for TC
	// MHT: based on contribution by JMU (1045)
	if ($printingOptions['requirement'])
	{
		$req_mgr = new requirement_mgr($db);
		$arrReqs = $req_mgr->get_all_for_tcase($id);

		$code .= '<tr><td width="20%" valign="top"><span class="label">'.lang_get('reqs').'</span><td>';
		if (sizeof($arrReqs))
		{
			foreach ($arrReqs as $req)
			{
				//@TODO: htmlspecialchars needed?
				$code .=  $req['id'] . ":  " . $req['title'] . "<br />";
			}
		}
		else
		{
			$code .= '&nbsp;' . lang_get('none') . '<br>';
		}
		$code .= "</td></tr>\n";
	}
	// collect keywords for TC
	// MHT: based on contribution by JMU (1045)
	if ($printingOptions['keyword'])
	{
		$code .= '<tr><td width="20%" valign="top"><span class="label">'.lang_get('keywords').':</span><td>';

		$arrKeywords = $tc_mgr->getKeywords($id,null);
		if (sizeof($arrKeywords))
		{
			foreach ($arrKeywords as $kw)
			{
				$code .= htmlspecialchars($kw['keyword']) . "<br />";
			}
		}
		else
		{
			$code .= '&nbsp;' . lang_get('none') . '<br>';
		}
		$code .= "</td></tr>\n";
	}

	$code .= "</table>\n</div>\n";

  if( !is_null($tc_mgr) )
	{
	  unset($tc_mgr);
	}


	return $code;
}

/*
  function:

  args :

  returns:

*/
function renderProjectNodeForPrinting(&$db,&$node,&$printingOptions,$item_type,
                                      $title,$user_id,$tplan_id=0)
{

	$tproject = new testproject($db);
	$tproject_info = $tproject->get_by_id($node['id']);
	$tplan_info = null;

	if($tplan_id != 0)
	{
		$tplan_mgr = new testplan($db);
		$tplan_info = $tplan_mgr->get_by_id($tplan_id);
	}

	$code = printHeader($title,$_SESSION['basehref']);
	$code .= printFirstPage($db, $item_type, $title, $tproject_info, $user_id, $printingOptions, $tplan_info);
	$printingOptions['toc_numbers'][1] = 0;
	if ($printingOptions['toc'])
	{
		$printingOptions['tocCode'] = '<div class="toc"><h2>'.lang_get('title_toc').'</h2>';
		$code .= "{{INSERT_TOC}}";
	}

	return $code;
}


/*
  function:

  args :

  returns:

*/
function renderTestSuiteNodeForPrinting(&$db,&$node,&$printingOptions,$tocPrefix,$level)
{
	$code = null;
	$name = isset($node['name']) ? htmlspecialchars($node['name']) : '';
	if ($printingOptions['toc'])
	{
	 	$printingOptions['tocCode'] .= '<p style="padding-left: '.(10*$level).'px;"><a href="#cat' . $node['id'] . '">' .
	 	                 $name . '</a></p>';
		$code .= "<a name='cat{$node['id']}'></a>";
	}
 	$code .= "<h1>{$tocPrefix} ". lang_get('test_suite') ." {$name}</h1>";

	if ($printingOptions['header'])
  	{
		$tsuite = new testsuite($db);
		$tInfo = $tsuite->get_by_id($node['id']);
		unset($tsuite);
    $code .= "<h2>{$tocPrefix}.0 " . lang_get('details') . "</h2><div>{$tInfo['details']}</div><br />";
 	}

	return $code;
}



/*
  function:

  args :

  returns:

*/
function renderTestPlanForPrinting(&$db,&$node,$item_type,&$printingOptions,
                                       $tocPrefix,$tcCnt,$level,$user_id,$tplan_id)

{
  $code =  renderTestSpecTreeForPrinting($db,$node,$item_type,$printingOptions,
                                         $tocPrefix,$tcCnt,$level,$user_id,$tplan_id);

  return $code;
}
?>
