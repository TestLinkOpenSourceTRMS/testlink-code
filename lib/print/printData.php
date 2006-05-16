<?php
/**
* 	TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
* @version 	$Id: printData.php,v 1.18 2006/05/16 19:35:40 schlundus Exp $
*	@author 	Martin Havlat
* 
* Shows the data that will be printed.
*
* @todo more css available for print
* @todo print results of tests
*
* @author: francisco mancardi - 20050915 - refactoring / I18N
* @author: francisco mancardi - 20050914 - refactoring
* @author: francisco mancardi - 20050830 - refactoring
* @author: francisco mancardi - 20050830 - refactoring print_header()
* 
* 20051118 - scs - title in print_header wasnt escaped
*/
require('../../config.inc.php');
require_once("common.php");
require_once("print.inc.php");
require_once("../testcases/archive.inc.php");
testlinkInitPage($db);

// numbering of chapters
$component_number = 0;
$category_number = 0;
// output string
$CONTENT_HEAD = "";
$CONTENT = "";
/** if print TOC */
$type = isset($_GET['type']) ?  $_GET['type'] : null;
$tproject_id   = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';
$printingOptions = null;

$printingOptions = array 
						( 'toc' => 0,
						  'body' => 0,
						  'summary' => 0,
						  'header' => 0,
						 );
foreach($printingOptions as $opt => $val)
{
	$printingOptions[$opt] = (isset($_GET[$opt]) && ($_GET[$opt] == 'y'));
}						 



/*

// --------------------------------------------------------------------------------
// Work with Test Specification of Product
if($_GET['type'] == 'product')
{
	// user wants to print the entire test specification
	if($_GET['edit'] == 'product')
	{
	    print_header($db,"", $toc); // no more information
	
	    $sqlMGTCOM = "SELECT  id,name,intro,scope,ref,method,lim, prodid" .
	    		         " FROM mgtcomponent WHERE  mgtcomponent.prodid=" . 
	    		         $_SESSION['testprojectID'] . " ORDER BY mgtcomponent.name" ;
	  	$resultMGTCOM = $db->exec_query($sqlMGTCOM);
	  	while($myrowCOM = $db->fetch_array($resultMGTCOM))
		{ 
			//display components until we run out
			print_component($myrowCOM);
			generate_product_CATs($db,$myrowCOM['id']);
		}
	}
	else if($_GET['edit'] == 'component')
	{
	    //if the user wants to print only a component they will enter here
	  	$myrowCOM = getComponent($db,$_GET['data']);
	  	print_header($db,lang_get("component") . ": " . $myrowCOM['name'], $toc);
	  	print_component($myrowCOM);
		generate_product_CATs($_GET['data']);
	}
	else if($_GET['edit'] == 'category')
	{
	    //if the user wants to print only a category they will enter here
	  	$myrowCAT = getCategory($db,$_GET['data']); 
	  	$myrowCOM = getComponent($db,$myrowCAT['compid']);
	  	print_header($db,lang_get("category") . ": ". $myrowCAT[1], $toc);
	  	print_component($myrowCOM);
	  	print_category($myrowCAT);
	
		//Print TCs
		generate_product_TCs($_GET['data']);
	}
	else if(!$_GET['edit'])
	{ 
		//if the user didn't pick anything this statement will be run
		tLog("GET['edit'] has invalid value.", 'ERROR');
		exit();
	}
} // endif product

// ------------------------------------------------------------------------------------
// ----------            Test Case Suite / Test Plan  Print    ------------------------
// ------------------------------------------------------------------------------------
if($_GET['type'] == 'testSet')
{
	//if the user wants to print the entire test plan they have chosen this if statement
	if($_GET['level'] == 'root')
	{
	    // get testplan name for display
	    print_header($db,lang_get('test_case_suite') . ": " . $_SESSION['testPlanName'], $toc);
	
	    $sql = " SELECT  mgtcomponent.id,mgtcomponent.name,mgtcomponent.intro," .
	    		   " mgtcomponent.scope,mgtcomponent.ref,mgtcomponent.method,mgtcomponent.lim," .
	    		   " component.id AS compid" .
	    		   " FROM mgtcomponent,component " .
	    		   " WHERE mgtcompid=mgtcomponent.id" .
	    		   " AND component.projid=" . $_SESSION['testPlanId'] . 
				     " ORDER BY mgtcomponent.name";

		$resultCOM = $db->exec_query($sql);
		while($myrow = $db->fetch_array($resultCOM))
		{ 
			//display components until we run out
			print_component($myrow);
			generate_testSuite_Categories($db,$myrow['compid']);
		}
	}
	else if($_GET['level'] == 'component')
	{
	    //if the user wants to print only a component they will enter here  
	  	// get component data
	  	$compID = $_GET['data'];
	    $myrowMGTCOM = getTPcomponent($db,$compID);
	
	    // print
	    print_header($db,lang_get('test_case_suite') . " : " . $_SESSION['testPlanName'] . " - " . 
	                 $myrowMGTCOM['name'], $toc);
	  	print_component($myrowMGTCOM);
		  generate_testSuite_Categories($db,$compID);
	}
	else if($_GET['level'] == 'category')
	{
	  //if the user wants to print only a category they will enter here
		// Get category
	  $catID = $_GET['data'];
	  $myrowCAT = getTPcategory($db,$catID);
	  $myrowMGTCOM = getTPcomponent($db,$myrowCAT['compid']); 

	  print_header($db,lang_get('test_case_suite') . ": " . $_SESSION['testPlanName'] . " - " . $myrowCAT['name'], $toc);
	  print_component($myrowMGTCOM);
	  generate_testSuite_Categories($db,$myrowCAT['compid'], $catID);

	}
	else
	{
		tLog("GET['level'] has invalid value.", 'ERROR');
		exit();
	}
}

*/


if ($type == 'testproject')
{
	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;
	$test_spec = $tree_manager->get_subtree($tproject_id,array('testplan'=>'exclude me'),
	                                                     array('testcase'=>'exclude my children'),null,null,true);
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = 1;
	if($test_spec)
	{
		$code = renderTreeForPrinting($db,$printingOptions,$test_spec,null,0,1);
	}
	
}

function renderTreeForPrinting(&$db,&$printingOptions,&$node,$tocPrefix,$tcCnt,$level)
{
	$code = null;
	$bCloseTOC = 0;	
	switch($node['node_type_id'])
	{
		case 1:
			$code .= renderProjectNode($db,$printingOptions,"",$node);
			
			break;	
		case 2:
			if (!is_null($tocPrefix))
				$tocPrefix .= ".";
			$tocPrefix .= $tcCnt;
			$code .= renderTestSuiteNode($db,$printingOptions,$node,$tocPrefix,$level);
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
			$code .= renderTreeForPrinting($db,$printingOptions,$current,$tocPrefix,$tsCnt,$level+1);
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

function renderProjectNode(&$db,&$printingOptions,$title,&$node)
{
	$stitle = lang_get('title_test_spec');
	if (strlen($title))
		$stitle .= "-" . htmlspecialchars($title);
	
	$my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

	$tproject = new testproject(&$db);
	$projectData = $tproject->get_by_id($node['id']);

	$code = printHeader($stitle,$_SESSION['basehref']);
	$code .= printFirstPage($db,$stitle, $projectData['name'], $projectData['notes'], $my_userID);

	$printingOptions['toc_numbers'][1] = 0;
	if ($printingOptions['toc'])
	{
		$printingOptions['tocCode'] = '<div class="toc"><h2>'.lang_get('title_toc').'</h2>';
		$code .= "{{INSERT_TOC}}";
		$code .= '</div>';
	}
	$printingOptions['tc_list'] = null;		
	return $code;
}

function renderTestSuiteNode(&$db,&$printingOptions,&$node,$tocPrefix,$level) 
{
	$code = null;
	$name = htmlspecialchars($node['name']);
	if ($printingOptions['toc']) 
	{
	 	$printingOptions['tocCode'] .= '<p style="padding-left: '.(10*$level).'px;"><a href="#cat' . $node['id'] . '">' . 
	 	                 $name . '</a></p>';
		$code .= "<a name='cat" . $node['id'] . "'></a>";
	}
 	$code .= "<h1>" . $tocPrefix . " ". lang_get('test suite') ." " . 
   	                     $name . "</h1>";
						 
	$node['details'] = "Blubba";
	if ($printingOptions['header']) 
  	{
    	$code .= "<h2>" . $tocPrefix . ".0 ". lang_get('details') . "</h2><div>" .  
    	            $node['details'] . "</div>";
    	$code .= "<h2>" . $tocPrefix . ".1 ".lang_get('data')."</h2>";
 	}
	
	return $code;
}
// add MS Word header 
if ($_GET['format'] == 'msword')
{
	header("Content-Disposition: inline; filename=testplan.doc");
	header("Content-Description: PHP Generated Data");
	header("Content-type: application/vnd.ms-word; name='My_Word'");
	flush();
}

echo $code;
?>