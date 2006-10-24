<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: print.inc.php,v $
 * @version $Revision: 1.15 $
 * @modified $Date: 2006/10/24 20:35:01 $ by $Author: schlundus $
 *
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Functions for support printing of documents. 
 *
 * 20050830 - fm - refactoring
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
 *
 * 20050905 - fm - added argument base_href
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

  20060102 - fm - product notes
*/
function printFirstPage(&$db,$title, $prodName, $prodNotes, $userID)
{
	$g_date_format = config_get('date_format');
	$prodName = htmlspecialchars($prodName);
	$author = htmlspecialchars(getAuthor($db,$userID));
	$title = htmlspecialchars($title);
	
	$output = '<div class="pageheader">';
	$output .= '<span style="float: right;">'. $prodName ."</span>";
	if (TL_COMPANY != '')
		$output .= '<span>'. htmlspecialchars(TL_COMPANY) ."</span>\n";
	
	$output .= "</div>\n";
	$output .= "<h1>".$title."</h1>\n";
	$output .= "<div style='margin: 50px;'>" .
		       "<p>". lang_get('product').": " . $prodName . "</p>";
	if (strlen($prodNotes))
		$output .= "<p>". $prodNotes . "</p>";
		       
	$output .= "<p>".lang_get('author').": " . $author . "</p>" .
		       "<p>".lang_get('printed_by_TestLink_on')." ". strftime($g_date_format, time()) . "</p></div>";

	if (TL_DOC_COPYRIGHT != '')
		$output .= '<div class="pagefooter">'.htmlspecialchars(TL_DOC_COPYRIGHT)."</div>\n";
	if (TL_DOC_CONFIDENT != '')
		$output .= '<div class="pagefooter">'.htmlspecialchars(TL_DOC_CONFIDENT)."</div>\n";

	return $output;
}


function renderTestSpecTreeForPrinting(&$db,&$printingOptions,&$node,$tocPrefix,$tcCnt,$level)
{
	$code = null;
	$bCloseTOC = 0;	
	switch($node['node_type_id'])
	{
		case 1:
			$code .= renderProjectNodeForPrinting($db,$printingOptions,$printingOptions['title'],$node);
			break;	
		case 2:
			if (!is_null($tocPrefix))
				$tocPrefix .= ".";
			$tocPrefix .= $tcCnt;
			$code .= renderTestSuiteNodeForPrinting($db,$printingOptions,$node,$tocPrefix,$level);
			break;
		case 3:
			$code .= renderTestCaseForPrinting($db,$printingOptions,$node,$level);
			break;
	}
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = $node['childNodes'];
		$tsCnt = 0;
		for($i = 0;$i < sizeof($childNodes);$i++)
		{
			$current = $childNodes[$i];
			if(is_null($current))
				continue;
			
			if ($current['node_type_id'] == 2)
				$tsCnt++;
			$code .= renderTestSpecTreeForPrinting($db,$printingOptions,$current,$tocPrefix,$tsCnt,$level+1);
		}
	}
	if ($node['node_type_id'] == 1)
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
 	$code .= "<div class='tc'><table width=90%>";
 	$code .= "<tr><th>".lang_get('test_case')." " . $id . ": " . 
 	            $name . "</th></tr>";
	
	if ($printingOptions['body'] || $printingOptions['summary'])
	{
		$tc = new testcase($db);
		$tcInfo = $tc->get_last_version_info($id);
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
	$name = htmlspecialchars($node['name']);
	if ($printingOptions['toc']) 
	{
	 	$printingOptions['tocCode'] .= '<p style="padding-left: '.(10*$level).'px;"><a href="#cat' . $node['id'] . '">' . 
	 	                 $name . '</a></p>';
		$code .= "<a name='cat{$node['id']}'></a>";
	}
 	$code .= "<h1>{$tocPrefix} ". lang_get('test suite') ." {$name}</h1>";
						 
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