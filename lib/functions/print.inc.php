<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: print.inc.php,v $
 * @version $Revision: 1.21 $
 * @modified $Date: 2007/01/26 21:01:23 $ by $Author: schlundus $
 *
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Functions for support printing of documents. 
 *
 * 20070106 - franciscom
 * 1. remove of some magic numbers
 * 
 */
/** 
@parameter $userID
@return string First + Last name 
*/
function getAuthor(&$db,$userID)
{
    $sql = "SELECT first,last,login FROM users WHERE id=" . $userID;
    $result = $db->exec_query($sql);
    $myrow = $db->fetch_array($result);
    
    $ret_val = $myrow['first'] . ' ' . $myrow['last'];
    if (strlen(trim($ret_val)) == 0 )
    {
    	$ret_val = $myrow['login'];
    }	
    return $ret_val; 
}

/** 
 * print HTML header 
 * Standard: HTML 4.01 trans (because is more flexible to bugs in user data)
 */
function printHeader($title, $base_href, $cssTemplate = TL_DOC_BASIC_CSS)
{
	$output = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
	$output .= "<html>\n<head>\n";
	$output .= '<meta http-equiv="Content-Type" content="text/html; charset='.TL_TPL_CHARSET.'" />';
	$output .= '<title>' . htmlspecialchars($title). "</title>\n";
	$output .= '<link type="text/css" rel="stylesheet" href="' . $base_href . $cssTemplate . '" />';
	$output .= "\n</head>\n<body>\n";

	return $output;
}

/** 
  print HTML - initial page of document 
*/
function printFirstPage(&$db,$title, $prodName, $prodNotes, $userID)
{
	$g_date_format = config_get('date_format');
	$prodName = htmlspecialchars($prodName);
	$author = htmlspecialchars(getAuthor($db,$userID));
	$title = htmlspecialchars($title);
	
	$output = '<div>';
	$output .= '<div class="pageheader">'. $prodName ."</div>\n";
	
	if (TL_DOC_COMPANY != '' ||  TL_DOC_COMPANY_LOGO != '' )
	{
		$output .= '<br /><center><table class="company">';

  	if (TL_DOC_COMPANY != '' )
  	{
		  $output .= '<tr><td id="company_name">'. htmlspecialchars(TL_DOC_COMPANY) ."</td></tr>";
		}
		$output .= '<tr><td/></tr>'; 
	  
  	if (TL_DOC_COMPANY_LOGO != '' )
	  {
	    $output .= '<tr><td id="company_logo">'. 
	               str_replace('%BASE_HREF%',$_SESSION['basehref'],TL_DOC_COMPANY_LOGO) ."</td></tr>";
	  }
		$output .= "</table></center>\n";
	}
	
	$output .= "</div>\n";



	$output .= '<h1 id="doctitle">'.$title."</h1>\n";
	$output .= '<div id="summary">' .
		         '<p id="prodname">'. lang_get('product').": " . $prodName . "</p>\n";
	if (strlen($prodNotes))
		$output .= '<p id="prodnotes">'. $prodNotes . "</p>\n";
		       
	$output .= '<p id="author">' . lang_get('author').": " . $author . "</p>\n" .
		         '<p id="printedby">' . lang_get('printed_by_TestLink_on')." ". 
		         strftime($g_date_format, time()) . "</p></div>\n";

	if (TL_DOC_COPYRIGHT != '')
		$output .= '<div class="pagefooter" id="copyright">'.htmlspecialchars(TL_DOC_COPYRIGHT)."</div>\n";
	if (TL_DOC_CONFIDENT != '')
		$output .= '<div class="pagefooter" id="confidential">'.htmlspecialchars(TL_DOC_CONFIDENT)."</div>\n";

	return $output;
}


function renderTestSpecTreeForPrinting(&$db,&$printingOptions,&$node,$tocPrefix,$tcCnt,$level)
{
  $tree_mgr=New tree($db);
  $map_id_descr=array_flip($tree_mgr->get_available_node_types());
  
	$code = null;
	$bCloseTOC = 0;	
	if (isset($node['node_type_id']))
	{
	  $verbose_node_type=$map_id_descr[$node['node_type_id']];
		switch($verbose_node_type)
		{
			case 'testproject':
				$code .= renderProjectNodeForPrinting($db,$printingOptions,$printingOptions['title'],$node);
				break;
					
			case 'testsuite':
				if (!is_null($tocPrefix))
					$tocPrefix .= ".";
				$tocPrefix .= $tcCnt;
				$code .= renderTestSuiteNodeForPrinting($db,$printingOptions,$node,$tocPrefix,$level);
				break;
			
			case 'testcase':
				$code .= renderTestCaseForPrinting($db,$printingOptions,$node,$level);
				break;
		}
	}
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = $node['childNodes'];
		$tsCnt = 0;
    $children_qty=sizeof($childNodes);
		for($i = 0;$i <$children_qty ;$i++)
		{
			$current = $childNodes[$i];
			if(is_null($current))
				continue;
			
			if (isset($current['node_type_id']) && $map_id_descr[$current['node_type_id']] == 'testsuite')
				$tsCnt++;
			$code .= renderTestSpecTreeForPrinting($db,$printingOptions,$current,$tocPrefix,$tsCnt,$level+1);
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

function renderTestCaseForPrinting(&$db,&$printingOptions,&$node,$level) 
{
 	$id = $node['id'];
	$name = htmlspecialchars($node['name']);
	
	$code = null;
	if ($printingOptions['toc']) 
	{
	  	$printingOptions['tocCode']  .= '<p style="padding-left: '.(15*$level).'px;"><a href="#tc' . $id . '">' . 
	  	                 $name . '</a></p>';
		$code .= "<a name='tc" . $id . "'></a>";
	}
 	$code .= "<div class=\"tc\"><table class=\"tc\" width=\"90%\">";
 	$code .= "<tr><th>".lang_get('test_case')." " . $id . ": " . 
 	            $name . "</th></tr>";
	
	if ($printingOptions['body'] || $printingOptions['summary'])
	{
		$tc = new testcase($db);
		$tcInfo = $tc->get_by_id($id,$node['tcversion_id']);
		if ($tcInfo)
			$tcInfo = $tcInfo[0];
		unset($tc);			
		$code .= "<tr><td><u>".lang_get('summary')."</u>: " .  $tcInfo['summary'] . "</td></tr>";
	 	if ($printingOptions['body']) 
	 	{
		   	$code .= "<tr><td><u>".lang_get('steps')."</u>:<br />" .  $tcInfo['steps'] . "</td></tr>";
		   	$code .= "<tr><td><u>".lang_get('expected_results')."</u>:<br />" .  $tcInfo['expected_results'] . "</td></tr>";
	 	}
		unset($tc);
	}
  	$code .= "</table></div>";
	
	return $code;
}

function renderProjectNodeForPrinting(&$db,&$printingOptions,$title,&$node)
{
	$stitle = lang_get('title_test_spec');
	if (strlen($title))
		$stitle .= " - " . $title;
	
	$my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

	$tproject = new testproject($db);
	$projectData = $tproject->get_by_id($node['id']);
	unset($tproject);
	$code = printHeader($stitle,$_SESSION['basehref']);
	$code .= printFirstPage($db,$stitle, $projectData['name'], $projectData['notes'], $my_userID);

	$printingOptions['toc_numbers'][1] = 0;
	if ($printingOptions['toc'])
	{
		$printingOptions['tocCode'] = '<div class="toc"><h2>'.lang_get('title_toc').'</h2>';
		$code .= "{{INSERT_TOC}}";
	}
	
	return $code;
}


function renderTestSuiteNodeForPrinting(&$db,&$printingOptions,&$node,$tocPrefix,$level) 
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
    	$code .= "<h2>{$tocPrefix}.0 ". lang_get('details'). 
				 "</h2><div>{$tInfo['details']}</div><br />";
 	}
	
	return $code;
}
?>
