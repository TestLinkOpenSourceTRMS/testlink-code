<?php
/**
* 	TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
* @version 	$Id: printData.php,v 1.11 2005/12/28 07:34:55 franciscom Exp $
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
* @author: francisco mancardi - 20050810 - deprecated $_SESSION['product'] removed
* 
* 20051118 - scs - title in print_header wasnt escaped
*/
require('../../config.inc.php');
require("common.php");
require_once("print.inc.php");
require_once("../testcases/archive.inc.php");
testlinkInitPage();

// numbering of chapters
$component_number = 0;
$category_number = 0;
// output string
$CONTENT_HEAD = "";
$CONTENT = "";
/** if print TOC */
$toc = isset($_GET['toc']) && ($_GET['toc'] == 'y') ? true : false;

/** this function prints the document header */
function print_header($title, $toc)
{
	global $CONTENT_HEAD;
	
	$prodName = isset($_SESSION['productName']) ? strings_stripSlashes($_SESSION['productName']) : null;
	$my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;
	
	
	$title = lang_get('title_test_spec') . "-" . htmlspecialchars($title);
	
	$CONTENT_HEAD .= printHeader($title,$_SESSION['basehref']);
	$CONTENT_HEAD .= printFirstPage($title, $prodName, $my_userID);

	if ($toc)
		$CONTENT_HEAD .= '<div class="toc"><h2>'.lang_get('title_toc').'</h2>';
}

/** 
print a component 

20050915 - fm - refactoring using field name instead of numbers
20050831 - fm -
After adding fckeditor to all fields in category,
I need to remove htmlspecialchars() calls and <pre></pre>
*/
function print_component($component) 
{
	global $CONTENT;
  	global $CONTENT_HEAD;
  	global $toc;
  	global $component_number;
  	global $category_number;
	
  	$component_number++;
  	$category_number = 0;

	if ($toc) 
	{
  		$CONTENT_HEAD .= '<p><a href="#com' . $component['id'] . '">' . 
  	                 htmlspecialchars($component['name']) . '</a></p>';
		$CONTENT .= "<a name='com" . $component['id'] . "'></a>";
	}
   	$CONTENT .= "<h1>" . $component_number . " ". lang_get('component') ." " . 
   	                     htmlspecialchars($component['name']) . "</h1>";

  	if ($_GET['header'] == 'y') 
  	{
    	$CONTENT .= "<h2>" . $component_number . ".0 ". lang_get('introduction') . "</h2><div>" .  
    	            $component['intro'] . "</div>";
    	$CONTENT .= "<h3>" . $component_number . ".0.1 ".lang_get('scope')."</h3><div>" .  
    	            $component['scope'] . "</div>";
    	$CONTENT .= "<h3>" . $component_number . ".0.2 ".lang_get('references') . "</h3><div>" .  
    	            $component['ref'] . "</div>";
    	$CONTENT .= "<h2>" . $component_number . ".1 " . lang_get('methodology') . "</h2><div>" . 
    	            $component['method'] . "</div>";
    	$CONTENT .= "<h3>" . $component_number . ".1.1 ".lang_get('limitations')."</h3><div>" . 
    	            $component['lim'] . "</pre></div>";
    	$CONTENT .= "<h2>" . $component_number . ".2 ".lang_get('categories')."</h2>";
 	}
} 

/** 
print a category 

20050831 - fm 
After adding fckeditor to all fields in category,
I need to remove htmlspecialchars() calls and <pre></pre>

*/

function print_category($category) 
{
  	global $CONTENT;
  	global $CONTENT_HEAD;
  	global $toc;
  	global $component_number;
  	global $category_number;
  	$category_number++;

	if ($toc) 
	{
	 	$CONTENT_HEAD .= '<p style="padding-left: 10px;"><a href="#cat' . $category['id'] . '">' . 
	 	                 htmlspecialchars($category['name']) . '</a></p>';
		$CONTENT .= "<a name='cat" . $category['id'] . "'></a>";
	}
    $CONTENT .= "<h3>" . $component_number . ".2." . $category_number . " " . 
                         htmlspecialchars($category['name']) . "</h3>";

  	if ($_GET['header'] == 'y') 
  	{
		  $CONTENT .= "<p>" .  $category['objective'] . "</p>";
	    $CONTENT .= "<h4>" . $component_number . ".2." . 
	                         $category_number . ".1 ". lang_get('setup_and_config')."</h4><div>" .  
	                         $category['config']."</div>";
	                         
    	$CONTENT .= "<h4>" . $component_number . ".2." . 
    	                     $category_number . ".2 ". lang_get('test_data')."</h4><div>" .  
    	                     $category['data'] . "</div>";
    	                     
	    $CONTENT .= "<h4>" . $component_number . ".2." . 
	                         $category_number . ".3 ". lang_get('tools')."</h4><div>" .  
	                         $category['tools'] . "</div>";
	                         
    	$CONTENT .= "<h4>" . $component_number . ".2." . $category_number . ".4 " . 
    	                     lang_get('test_cases')."</h4>";
    	$CONTENT .= "<p>";
  	}
}


/** print a test case data */
//20051022 - scs - print out mgttcid instead of tcid
function print_testcase($testcase) 
{
 	global $CONTENT;
 	global $CONTENT_HEAD;
 	global $toc;
	
	$idx = isset($testcase['mgttcid']) ? 'mgttcid' : 'id';
	
	if ($toc) 
	{
	  	$CONTENT_HEAD .= '<p style="padding-left: 20px;"><a href="#tc' . $testcase['id'] . '">' . 
	  	                 htmlspecialchars($testcase['title']) . '</a></p>';
		$CONTENT .= "<a name='tc" . $testcase[$idx] . "'></a>";
	}
 	$CONTENT .= "<div class='tc'><table width=90%>";
 	$CONTENT .= "<tr><th>".lang_get('test_case')." " . $testcase[$idx] . ": " . 
 	            htmlspecialchars($testcase['title']) . "</th></tr>";


 	if ($_GET['body'] == 'y' || $_GET['summary'] == 'y')
 	{
 	 	$CONTENT .= "<tr><td><u>".lang_get('summary')."</u>: " .  $testcase['summary'] . "</td></tr>";
 	} 
 	if ($_GET['body'] == 'y') 
 	{
	   	$CONTENT .= "<tr><td><u>".lang_get('steps')."</u>:<br />" .  $testcase['steps'] . "</td></tr>";
	   	$CONTENT .= "<tr><td><u>".lang_get('expected_results')."</u>:<br />" .  $testcase['exresult'] . "</td></tr>";
 	}

  	$CONTENT .= "</table></div>";
}

/*
20050831 - fm - logic reuse
*/
function generate_TCs($rs)
{
  global $CONTENT;

	if ($GLOBALS['db']->num_rows($rs) > 0)
	{
	    while ($myrow = $GLOBALS['db']->fetch_array($rs))
		{
			print_testcase($myrow);
		}
	}
	else
	{
    	$CONTENT .= "<p>" . lang_get('no_test_case') . "</p>";
	}
}


/** print Test Specification data within category */
function generate_product_TCs($idCategory)
{
	$sqlTC = " SELECT  id,title, summary, steps, exresult " .
				" FROM mgttestcase " .
				" WHERE catid=" . $idCategory . 
				" ORDER BY TCorder, id";

	$resultTC = do_sql_query($sqlTC);
	
	if (!$resultTC)
	{
		tLog($sqlTC . ' | error: ' . $GLOBALS['db']->error_msg(), 'ERROR');
	}
	generate_TCs($resultTC);
}

/** print Test Case Suite data within category */
function generate_testSuite_TCs($idCategory)
{
	$sqlTC = " SELECT id,title, summary, steps, exresult,mgttcid, keywords " .
			" FROM testcase " .
			" WHERE catid=" . $idCategory . " ORDER BY TCorder, mgttcid";
	$resultTC = do_sql_query($sqlTC);
	
	if (!$resultTC)
	{
		tLog($sqlTC . ' | error: ' . $GLOBALS['db']->error_msg(), 'ERROR');
	}
	
	generate_TCs($resultTC);
}

/*
20050914 - fm - mgtcategory.name 

20050911 - fm - Use Join
code reuse adding catID
catID=0 -> all

20050831 - fm - switch to $GLOBALS['db']->fetch_array()
*/
function generate_testSuite_Categories($idComponent,$catID=0)
{
	// Now use a Join
	// mgtcategory.name or category.name ???
	$sql =" SELECT mgtcategory.id, mgtcategory.objective," .
		" mgtcategory.config,mgtcategory.data,mgtcategory.tools," .
		" mgtcategory.CATorder, " .
		" mgtcategory.name, category.id AS catid " .  
		" FROM  mgtcategory,category " .
		" WHERE mgtcategory.id=category.mgtcatid" .
		" AND  category.compid = " . $idComponent;
     
	if($catID)
	{
		$sql .= " AND category.id=" . $catID;
	}     
	$sql .= " ORDER BY CATorder, id";
	$res = do_sql_query($sql);
	
	while ($myrow = $GLOBALS['db']->fetch_array($res))
	{  
		print_category($myrow);
		generate_testSuite_TCs($myrow['catid']);
	}
}


function generate_product_CATs($idComponent)
{
    $sqlCAT = " SELECT id,name,objective,config,data,tools " .
              " FROM mgtcategory " .
              " WHERE compid=" . $idComponent .	
              " order by CATorder, id";
  	$resultCAT = do_sql_query($sqlCAT);
	while ($myrowCAT = $GLOBALS['db']->fetch_array($resultCAT))
	{   
		print_category($myrowCAT);
		generate_product_TCs($myrowCAT['id']);
	}
}

/* 20050911 - fm - refactoring*/
function getTPcomponent($compID)
{
  $sql = " SELECT  mgtcomponent.id, mgtcomponent.name,mgtcomponent.intro," .
  	     " mgtcomponent.scope,mgtcomponent.ref,mgtcomponent.method,mgtcomponent.lim " .
  	     " FROM mgtcomponent,component " .
  		   " WHERE mgtcompid=mgtcomponent.id " .
  		   " AND component.id=" . $compID;

  $res = do_sql_query($sql);
  $myrow = $GLOBALS['db']->fetch_array($res);
  return $myrow;
}

/* 
20050914 - fm - refactoring
20050911 - fm - refactoring
*/
function getTPcategory($catID)
{
	$sql = " SELECT category.id,mgtcategory.name,category.compid " . 
	       " FROM category,mgtcategory " .
	       " WHERE category.mgtcatid=mgtcategory.id " .
	       " AND category.id=" . $catID . 
			   " ORDER BY mgtcategory.CATorder, category.id";
	
	$res = do_sql_query($sql);
	$myrow = $GLOBALS['db']->fetch_array($res);
	return $myrow;
}

// --------------------------------------------------------------------------------
// Work with Test Specification of Product
if($_GET['type'] == 'product')
{
	// user wants to print the entire test specification
	if($_GET['edit'] == 'product')
	{
	    print_header("", $toc); // no more information
	
	    $sqlMGTCOM = "SELECT  id,name,intro,scope,ref,method,lim, prodid" .
	    		         " FROM mgtcomponent WHERE  mgtcomponent.prodid=" . 
	    		         $_SESSION['productID'] . " ORDER BY mgtcomponent.name" ;
	  	$resultMGTCOM = do_sql_query($sqlMGTCOM);
	  	while($myrowCOM = $GLOBALS['db']->fetch_array($resultMGTCOM))
		{ 
			//display components until we run out
			print_component($myrowCOM);
			generate_product_CATs($myrowCOM['id']);
		}
	}
	else if($_GET['edit'] == 'component')
	{
	    //if the user wants to print only a component they will enter here
	  	$myrowCOM = getComponent($_GET['data']);
	  	print_header(lang_get("component") . ": " . $myrowCOM['name'], $toc);
	  	print_component($myrowCOM);
		generate_product_CATs($_GET['data']);
	}
	else if($_GET['edit'] == 'category')
	{
	    //if the user wants to print only a category they will enter here
	  	$myrowCAT = getCategory($_GET['data']); 
	  	$myrowCOM = getComponent($myrowCAT['compid']);
	  	print_header(lang_get("category") . ": ". $myrowCAT[1], $toc);
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
	    // get project name for display
	    print_header(lang_get('test_case_suite') . ": " . $_SESSION['testPlanName'], $toc);
	
	    $sql = " SELECT  mgtcomponent.id,mgtcomponent.name,mgtcomponent.intro," .
	    		   " mgtcomponent.scope,mgtcomponent.ref,mgtcomponent.method,mgtcomponent.lim," .
	    		   " component.id AS compid" .
	    		   " FROM mgtcomponent,component " .
	    		   " WHERE mgtcompid=mgtcomponent.id" .
	    		   " AND component.projid=" . $_SESSION['testPlanId'] . 
				     " ORDER BY mgtcomponent.name";

		$resultCOM = do_sql_query($sql);
		while($myrow = $GLOBALS['db']->fetch_array($resultCOM))
		{ 
			//display components until we run out
			print_component($myrow);
			generate_testSuite_Categories($myrow['compid']);
		}
	}
	else if($_GET['level'] == 'component')
	{
	    //if the user wants to print only a component they will enter here  
	  	// get component data
	  	$compID = $_GET['data'];
	    $myrowMGTCOM = getTPcomponent($compID);
	
	    // print
	    print_header(lang_get('test_case_suite') . " : " . $_SESSION['testPlanName'] . " - " . 
	                 $myrowMGTCOM['name'], $toc);
	  	print_component($myrowMGTCOM);
		  generate_testSuite_Categories($compID);
	}
	else if($_GET['level'] == 'category')
	{
	  //if the user wants to print only a category they will enter here
		// Get category
	  $catID = $_GET['data'];
	  $myrowCAT = getTPcategory($catID);
	  $myrowMGTCOM = getTPcomponent($myrowCAT['compid']); 

	  print_header(lang_get('test_case_suite') . ": " . $_SESSION['testPlanName'] . " - " . $myrowCAT['name'], $toc);
	  print_component($myrowMGTCOM);
	  generate_testSuite_Categories($myrowCAT['compid'], $catID);

	}
	else
	{
		tLog("GET['level'] has invalid value.", 'ERROR');
		exit();
	}
}

// add MS Word header 
if ($_GET['format'] == 'msword')
{
	header("Content-Disposition: inline; filename=testplan.doc");
	header("Content-Description: PHP Generated Data");
	header("Content-type: application/vnd.ms-word; name='My_Word'");
	flush();
}

//close TOC and print docs
if ($toc)
	$CONTENT_HEAD .= '</div><hr />';
$output = $CONTENT_HEAD . $CONTENT . "</body></html>";
tLog ($output);

// print all document
echo $output;
?>