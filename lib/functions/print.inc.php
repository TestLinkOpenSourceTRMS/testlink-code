<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: print.inc.php,v $
 * @version $Revision: 1.61 $
 * @modified $Date: 2008/12/07 19:02:35 $ by $Author: franciscom $
 *
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 *
 * Functions for support printing of documents.
 *
 * rev:
 *     20081207 - franciscom - BUGID 1910 - changes on display of estimated execution time
 *                             added code to display CF with scope='execution'
 * 
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

/**
 * build HTML header
 * Standard: HTML 4.01 trans (because is more flexible to bugs in user data)
 */
function buildHTMLHeader($title,$cfg,$base_href)
{
	$output = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
	$output .= "<html>\n<head>\n";
	$output .= '<meta http-equiv="Content-Type" content="text/html; charset=' . $cfg->charset . '" />'.
		"\n<base href=\"".$base_href."\"/>\n";
	$output .= '<title>' . htmlspecialchars($title). "</title>\n";
	$output .= '<link type="text/css" rel="stylesheet" href="'. $base_href . $cfg->css_template ."\" />\n";
	$output .= '<style type="text/css" media="print">.notprintable { display:none;}</style>';
	$output .= "\n</head>\n";

	return $output;
}

/**
  print HTML - initial page of document
*/
function printFirstPage(&$db, $docCfg, $item_type, $title, $tproject_info, 
                        $userID, $printingOptions = null, $tplan_info = null,
                        $statistics=null)
{
  $estimated_string='';
  $real_string='';
  
	$date_format_cfg = config_get('date_format');
	$tproject_name = htmlspecialchars($tproject_info['name']);
	$tproject_notes = $tproject_info['notes'];
  
	$author = null;
	$user = tlUser::getById($db,$userID);
	if ($user)
	{
		$author = htmlspecialchars($user->getDisplayName());
  }
  
	$output = "<body>\n<div>";
	if ($docCfg->company_name != '' )
	{
		$output .= '<div style="float:right;">' . htmlspecialchars($docCfg->company_name) ."</div>\n";
	}
	$output .= '<div>'. $tproject_name . "</div><hr />\n";

	if ($docCfg->company_logo != '' )
	{
		$output .= '<p style="text-align: center;"><img alt="TestLink logo" title="configure using $tlCfg->company->logo_image"'.
        	     ' src="' . $_SESSION['basehref'] . TL_THEME_IMG_DIR . $docCfg->company_logo . '" /></p>';
	}
	$output .= "</div>\n";

	/* Title */
	$output .= '<div class="doc_title">';

	if(is_null($tplan_info))
	{
		$output .= lang_get('title_test_spec');
	}
	else
	{
		$output .= $printingOptions['passfail'] ? lang_get('test_report') : lang_get('test_plan');
 		$output .=  ' ' . htmlspecialchars($tplan_info['name']);
	}

	if($title != '')
	{
		$output .= '<p>' . lang_get($item_type) . ' - ' . htmlspecialchars($title) . "</p>\n";

    // Based on contribution (BUGID 1670)
		if(!is_null($tplan_info))
	  {
	     if( !is_null($statistics) &&  isset($statistics['estimated_execution']) ) 
	     {
	         $estimated_minutes = $statistics['estimated_execution']['minutes'];
	         $tcase_qty = $statistics['estimated_execution']['tcase_qty'];
	         
           if( $estimated_minutes > 60)
           {
	    	    	$estimated_string = lang_get('estimated_time_hours') . round($estimated_minutes/60,2) ;
		       } 
		       else
		       {
		          $estimated_string = lang_get('estimated_time_min') . $estimated_minutes;
           }
           $estimated_string = sprintf($estimated_string,$tcase_qty);
           
		   }
		   $output .= '<p style="font-size:14; text-align: center; font-weight: bold;">' . $estimated_string . "</p>\n";
	     if( !is_null($statistics) &&  isset($statistics['real_execution']) ) 
	     {
	         $real_minutes = $statistics['real_execution']['minutes'];
	         $tcase_qty = $statistics['real_execution']['tcase_qty'];
	         
	         if( $real_minutes > 0 )
	         {
               if( $real_minutes > 60)
               {
	    	        	$real_string = lang_get('real_time_hours') . round($real_minutes/60,2) ;
		           } 
		           else
		           {
		              $real_string = lang_get('real_time_min') . $real_minutes;
               }
               $real_string = sprintf($real_string,$tcase_qty);    
           }

		   }
		   $output .= '<p style="font-size:14; text-align: center; font-weight: bold;">' . $real_string . "</p>\n";
	  }
	}
	$output .= "</div>\n";


	// Print summary on the first page
	$output .= '<div class="summary">' .
		         '<p id="prodname">'. lang_get('project') .": " . $tproject_name . "</p>\n";

	$output .= '<p id="author">' . lang_get('author').": " . $author . "</p>\n" .
		         '<p id="printedby">' . lang_get('printed_by_TestLink_on')." ".
		         strftime($date_format_cfg, time()) . "</p></div>\n";

	if ($docCfg->company_copyright != '')
	{
		$output .= '<div class="pagefooter" id="copyright">' . 
		           htmlspecialchars($docCfg->company_copyright)."</div>\n";
	}
		           
	if ($docCfg->confidential_msg != '')
	{
		$output .= '<div class="pagefooter" id="confidential">' . 
		           htmlspecialchars($docCfg->confidential_msg)."</div>\n";
  }
	
	if (strlen($tproject_notes))
	{
		$output .= '<h1>'.lang_get('introduction').'</h1><p id="prodnotes">'. $tproject_notes . "</p>\n";
  }
  
  if (strlen($tplan_info['notes']))
  {
		$output .= '<p id="prodnotes">'. $tplan_info['notes'] . "</p>\n";
	}	

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
                                       $tocPrefix,$tcCnt,$level,$user_id,
                                       $tplan_id = 0,$tcPrefix = null,
                                       $tProjectID = 0,$estimated_minutes=0)
{
	static $tree_mgr;
	static $map_id_descr;
 	$code = null;

	if( !$tree_mgr )
	{ 
	    $tree_mgr = new tree($db);
 	    $map_id_descr = $tree_mgr->node_types;
 	}
 	$verbose_node_type = $map_id_descr[intval($node['node_type_id'])];
	
  switch($verbose_node_type)
	{
		case 'testproject':
			$code .= renderProjectNodeForPrinting($db,$node,$printingOptions,$item_type,
			                                      $printingOptions['title'],
			                                      $user_id,$tplan_id,$estimated_minutes);
		break;

		case 'testsuite':
			  // if (!is_null($tocPrefix))
			  // {
			  // 	$tocPrefix .= ".";
			  // }
			  // $tocPrefix .= $tcCnt;
			  
				$tocPrefix .= (!is_null($tocPrefix) ? "." : '') . $tcCnt;
			  $code .= renderTestSuiteNodeForPrinting($db,$node,$printingOptions,$tocPrefix,$level);
		break;

		case 'testcase':
			  $code .= renderTestCaseForPrinting($db,$node,$printingOptions,$level,$tplan_id,$tcPrefix,$tProjectID);
	  break;
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
			{
			    $tsCnt++;
			}
			$code .= renderTestSpecTreeForPrinting($db,$current,$item_type,$printingOptions,
			                                       $tocPrefix,$tsCnt,$level+1,$user_id,$tplan_id,$tcPrefix,$tProjectID);
		}
	}
	if ($verbose_node_type == 'testproject')
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
function renderTestCaseForPrinting(&$db,&$node,&$printingOptions,$level,
                                   $tplan_id=0,$prefix = null,$tProjectID = 0)
{
	  static $req_mgr;
	  static $tc_mgr;
	  static $results_cfg;
	  static $status_labels;
	  static $labels;
	  static $doc_cfg;
	  static $gui_cfg;
	  static $testcaseCfg;
	  static $tcase_prefix;
    static $userMap = array();
    
	  $code = null;
	  $tcInfo = null;
    $tcResultInfo = null;
    $tcase_pieces=null;
    
	  if( !$results_cfg )
	  {
 	      $tc_mgr = new testcase($db);
	      $doc_cfg = config_get('document_generator');
	      $gui_cfg = config_get('gui');
	      $testcaseCfg = config_get('testcase_cfg');
	      
	      $results_cfg = config_get('results');
        foreach($results_cfg['code_status'] as $key => $value)
        {
            $status_labels[$key] = "check your \$tlCfg->results['status_label'] configuration ";
            if( isset($results_cfg['status_label'][$value]) )
            {
                $status_labels[$key] = lang_get($results_cfg['status_label'][$value]);
            }    
        }
        
        $labels=array("last_exec_result" => '', "testnotes" => '', "none" => '', "reqs" => '',
                      "author" => '', "summary" => '',"steps" => '', "expected_results" =>'',
                      "build" => '', "test_case" => '', "keywords" => '',"version" => '', 
                      "test_status_not_run" => '', "not_aplicable" => '');
                                
        foreach($labels as $id => $value)
        {
            $labels[$id] = lang_get($id);
        }
    
	      if( !is_null($prefix) )
	      {
	          $tcase_prefix = $prefix;
	      }
	  }
	
    $versionID = isset($node['tcversion_id']) ? $node['tcversion_id'] : TC_LATEST_VERSION;
    $id = $node['id'];
    $tcInfo = $tc_mgr->get_by_id($id,$versionID);
    if ($tcInfo)
    {
    	$tcInfo = $tcInfo[0];  
    }
    if( !$tcase_prefix )
    {
    	$tcase_prefix = $tc_mgr->getPrefix($id);
    }
	  $external_id = $tcase_prefix . $testcaseCfg->glue_character . $tcInfo['tc_external_id'];
	  $name = htmlspecialchars($node['name']);
  	
  	$cfields = array('specScope' => '', 'execScope' => '');

	  // get custom fields that has specification scope
	  $cfields['specScope'] = $tc_mgr->html_table_of_custom_field_values($id,'design',null,null,$tplan_id,$tProjectID);
  	if(strlen(trim($cfields['specScope'])) > 0 )
  	{
		    $cfields['specScope'] = str_replace('<td class="labelHolder">','<td>',$cfields['specScope']);  
		    $cfields['specScope'] = str_replace('<table>','',$cfields['specScope']);
		    $cfields['specScope'] = str_replace('</table>','',$cfields['specScope']);
  	}

    // Need to get CF with execution scope
	  $sql =  " SELECT E.id AS execution_id, E.status, E.execution_ts, " .
	          " E.notes, E.build_id, E.tcversion_id,E.tcversion_number,E.testplan_id," .
	          " B.name AS build_name " .
	          " FROM executions E, builds B" .
	          " WHERE E.build_id= B.id " . 
	          " AND E.tcversion_id = {$versionID} " .
	          " AND E.testplan_id = {$tplan_id} " .
	  		    " ORDER BY execution_id DESC";
	  $exec_info = $db->get_recordset($sql,null,1);
    
    if(!is_null($exec_info ))
    { 
        $execution_id=$exec_info[0]['execution_id'];
        $cfields['execScope'] = $tc_mgr->html_table_of_custom_field_values($versionID,'execution',null,$execution_id,
	                                                                         $tplan_id,$tProjectID);
  	    if(strlen(trim($cfields['execScope'])) > 0 )
  	    {
		        $cfields['execScope'] = str_replace('<td class="labelHolder">','<td>',$cfields['execScope']);  
		        $cfields['execScope'] = str_replace('<table>','',$cfields['execScope']);
		        $cfields['execScope'] = str_replace('</table>','',$cfields['execScope']);
  	    }
    }
    
	  if ($printingOptions['toc'])
	  {
	      $printingOptions['tocCode']  .= '<p style="padding-left: '.(15*$level).'px;"><a href="#tc' . $id . '">' .
	       	                              $name . '</a></p>';
	    	$code .= "<a name=\"tc{$id}\"></a>";
	  }
      
 	  $code .= '<div><table class="tc" width="90%">';
 	  $code .= '<tr><th colspan="2">' . $labels['test_case'] . " " . htmlspecialchars($external_id) . ": " . $name;
    
	  // add test case version
	  // mht: is it possible that version is not set? - remove condition
	  if($doc_cfg->tc_version_enabled && isset($node['version']) ) 
	  {
	  	$code .= '&nbsp;<span style="font-size: 80%;"' . $gui_cfg->role_separator_open . $label['version'] . 
	  	         $gui_cfg->title_sep_1 .  $node['version'] . $gui_cfg->role_separator_close . '</span>';
	  }
 	  $code .= '</th></tr>';

  	if ($printingOptions['author'])
  	{
	      $authorName = null;
  	    $authorID = $tcInfo['author_id'];
	      
	      if(isset($userMap[$authorID]))
	      {
	          $authorName	= $userMap[$authorID];
	      }
	      else
	      {
	          $authorName = null;
	          $user = tlUser::getByID($db,$tcInfo['author_id']);
	          if ($user)
	          {
	          	$authorName = $user->getDisplayName();
	          	$userMap[$authorID] = htmlspecialchars($authorName);
	          }
	      }
	      
	      $code .= '<tr><td width="20%" valign="top"><span class="label">'.$labels['author'].':</span></td>';
        $code .= '<td>' . $authorName . "</td></tr>";
  	}

    if (($printingOptions['body'] || $printingOptions['summary']))
    {
        $tcase_pieces=array('summary');
    }
    
    if (($printingOptions['body']))
    {
        $tcase_pieces[]='steps';
        $tcase_pieces[]='expected_results';        
    }
    
    if( !is_null($tcase_pieces) )
    {
        foreach( $tcase_pieces as $key )
        {
            $code .= "<tr><td colspan=\"2\"><span class='label'>" . 
                     $labels[$key].":</span><br />" .  $tcInfo[$key] . "</td></tr>";
        }
    }
    $code .= $cfields['specScope'] . $cfields['execScope'];

	  // generate test results
	  if ($printingOptions['passfail'])
	  {
	      $build_name='';
	      if ($exec_info) 
	      {
	  		    $tcstatus2 = $status_labels[$exec_info[0]['status']];
	  		    $tcnotes2 = $exec_info[0]['notes'];
            $build_name = " - ({$labels['build']}:{$exec_info[0]['build_name']})";
	      }
	      else
	      {
	  		    $tcstatus2 = $labels["test_status_not_run"];
	  		    $tcnotes2 = $labels["not_aplicable"];
	  	  }
	  	
	  	$code .= '<tr><td width="30%" valign="top"><span class="label">' . 
	  	         $labels['last_exec_result'] . $build_name . 
	  			     ':</span><br /><span style="text-align:center; padding:10px;">'.$tcstatus2.'</span></td>'.
	  			     '<td><u>'.$labels['testnotes'] . ':</u><br />' . $tcnotes2 . "</td></tr>\n";
	  }


	  // collect REQ for TC
	  // MHT: based on contribution by JMU (1045)
	  if ($printingOptions['requirement'])
	  {
	    if(!$req_mgr)
	    { 
	        $req_mgr = new requirement_mgr($db);
	  	}
	  	
	  	$requirements = $req_mgr->get_all_for_tcase($id);
	  	$code .= '<tr><td width="20%" valign="top"><span class="label">'. $labels['reqs'].'</span><td>';
	  	if (sizeof($requirements))
	  	{
	  		foreach ($requirements as $req)
	  		{
	  			$code .=  htmlspecialchars($req['req_doc_id'] . ":  " . $req['title']) . "<br />";
	  		}
	  	}
	  	else
	  	{
	  		$code .= '&nbsp;' . $labels['none'] . '<br />';
	  	}
	  	
	  	$code .= "</td></tr>\n";
	  }
	  
	  // collect keywords for TC
	  // MHT: based on contribution by JMU (1045)
	  if ($printingOptions['keyword'])
	  {
	  	$code .= '<tr><td width="20%" valign="top"><span class="label">'. $labels['keywords'].':</span><td>';
    
	  	$arrKeywords = $tc_mgr->getKeywords($id);
	  	if (sizeof($arrKeywords))
	  	{
	  		foreach ($arrKeywords as $kw)
	  		{
	  			$code .= htmlspecialchars($kw['keyword']) . "<br />";
	  		}
	  	}
	  	else
	  	{
	  		$code .= '&nbsp;' . $labels['none'] . '<br>';
	  	}
	  	$code .= "</td></tr>\n";
	  }

	  $code .= "</table>\n</div>\n";
	  return $code;
}

/*
  function: renderProjectNodeForPrinting

  args :

  returns:
  
  rev: 20081207 - franciscom
       minor refactoring to remove global coupling

*/
function renderProjectNodeForPrinting(&$db,&$node,&$printingOptions,$item_type,
                                      $title,$user_id,$tplan_id=0,$estimated_minutes=0)
{
  $docCfg = config_get('document_generator');
  
  $cfg = new stdClass();
  $cfg->charset=config_get('charset');
  $cfg->css_template = $docCfg->css_template;
  
	$tproject = new testproject($db);
	$tproject_info = $tproject->get_by_id($node['id']);
	$tplan_info = null;

	if($tplan_id != 0)
	{
		$tplan_mgr = new testplan($db);
		$tplan_info = $tplan_mgr->get_by_id($tplan_id);
	}

	$code = buildHTMLHeader($title,$cfg,$_SESSION['basehref']);
	$code .= printFirstPage($db, $docCfg, $item_type, $title, $tproject_info, 
	                        $user_id, $printingOptions, $tplan_info,$estimated_minutes);
	                        
	$printingOptions['toc_numbers'][1] = 0;
	if ($printingOptions['toc'])
	{
		$printingOptions['tocCode'] = '<div class="toc"><h2>'.lang_get('title_toc').'</h2>';
		$code .= "{{INSERT_TOC}}";
	}

	return $code;
}


/*
  function: renderTestSuiteNodeForPrinting

  args :

  returns:
  
  rev: 20081207 - franciscom - refactoring using static to decrease exec time.

*/
function renderTestSuiteNodeForPrinting(&$db,&$node,&$printingOptions,$tocPrefix,$level)
{
  static $tsuite_mgr;
  static $labels;
  if( !$labels)
  { 
	    $labels=array('test_suite' => lang_get('test_suite'),'details' => lang_get('details'));
	}
  
	$code = null;
	$name = isset($node['name']) ? htmlspecialchars($node['name']) : '';
	if ($printingOptions['toc'])
	{
	 	$printingOptions['tocCode'] .= '<p style="padding-left: '.(10*$level).'px;"><a href="#cat' . $node['id'] . '">' .
	 	                               $name . '</a></p>';
		$code .= "<a name='cat{$node['id']}'></a>";
	}
 	$code .= "<h1>{$tocPrefix} {$labels['test_suite']} {$name}</h1>";

	if ($printingOptions['header'])
  {
    if( !$tsuite_mgr)
    { 
		    $tsuite_mgr = new testsuite($db);
		}
		$tInfo = $tsuite_mgr->get_by_id($node['id']);
   	$code .= "<h2>{$tocPrefix}.0 {$labels['details']} </h2><div>{$tInfo['details']}</div><br />";
 	}

	return $code;
}



/*
  function: renderTestPlanForPrinting

  args:

  returns:

*/
function renderTestPlanForPrinting(&$db,&$node,$item_type,&$printingOptions,
                                   $tocPrefix,$tcCnt,$level,$user_id,$tplan_id,
                                   $tProjectID,$statistics)

{
	$tProjectMgr = new testproject($db);
	$tcPrefix = $tProjectMgr->getTestCasePrefix($tProjectID);
	$code =  renderTestSpecTreeForPrinting($db,$node,$item_type,$printingOptions,
                                         $tocPrefix,$tcCnt,$level,$user_id,
                                         $tplan_id,$tcPrefix,$tProjectID,$statistics);
	return $code;
}
?>
