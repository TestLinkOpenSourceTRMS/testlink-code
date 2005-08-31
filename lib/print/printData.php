<?php
/**
* 	TestLink Open Source Project - http://testlink.sourceforge.net/ 
*
* 	@version 	$Id: printData.php,v 1.3 2005/08/31 08:45:11 franciscom Exp $
*	@author 	Martin Havlat
* 
* 	This page shows the data that will be printed.
*
* 	@todo more css available for print
* 	@todo print results of tests
*
* db calls for Test Set includes wrong SQL; I expect that update for containers solve this problem
* 	- solved in 1.1.2.6
*
*
* @ author: francisco mancardi - 20050830
* refactoring print_header()
*
* @ author: francisco mancardi - 20050810
* deprecated $_SESSION['product'] removed
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
  
  // 20050830 - fm
  $prodName = isset($_SESSION['productName']) ? strings_stripSlashes($_SESSION['productName']) : null;
  $my_userID = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;

  
  $title = lang_get('title_test_spec') . "-" . $title;
  
  $CONTENT_HEAD .= printHeader($title);
  
  // 20050830 - fm
  $CONTENT_HEAD .= printFirstPage($title, $prodName, $my_userID);

  if ($toc)
  	$CONTENT_HEAD .= '<div class="toc"><h2>'.lang_get('title_toc').'</h2>';
}

/** print a component */
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
	  	$CONTENT_HEAD .= '<p><a href="#com' . $component[0] . '">' . htmlspecialchars($component[1]) . '</a></p>';
		$CONTENT .= "<a name='com" . $component[0] . "'></a>";
	}
   	$CONTENT .= "<h1>" . $component_number . " ".lang_get('component')." " . htmlspecialchars($component[1]) . "</h1>";

  	if ($_GET['header'] == 'y') 
  	{
    	$CONTENT .= "<h2>" . $component_number . ".0 ".lang_get('introduction')."</h2><div><pre>" .  htmlspecialchars($component[2]) . "</pre></div>";
    	$CONTENT .= "<h3>" . $component_number . ".0.1 ".lang_get('scope')."</h3><div>" .  $component[3] . "</div>";
    	$CONTENT .= "<h3>" . $component_number . ".0.2 ".lang_get('references')."</h3><div><pre>" .  htmlspecialchars($component[4]) . "</pre></div>";
    	$CONTENT .= "<h2>" . $component_number . ".1 ".lang_get('methodology')."</h2><div><pre>" . htmlspecialchars($component[5]) . "</pre></div>";
    	$CONTENT .= "<h3>" . $component_number . ".1.1 ".lang_get('limitations')."</h3><div><pre>" . htmlspecialchars($component[6]) . "</pre></div>";
    	$CONTENT .= "<h2>" . $component_number . ".2 ".lang_get('categories')."</h2>";
 	}
} 

/** print a category */
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
	  	$CONTENT_HEAD .= '<p style="padding-left: 10px;"><a href="#cat' . $category[0] . '">' . htmlspecialchars($category[1]) . '</a></p>';
		$CONTENT .= "<a name='cat" . $category[0] . "'></a>";
	}
    $CONTENT .= "<h3>" . $component_number . ".2." . $category_number . " " . htmlspecialchars($category[1]) . "</h3>";

  	if ($_GET['header'] == 'y') 
  	{
		$CONTENT .= "<p>" . $category[2] . "</p>";
	    $CONTENT .= "<h4>" . $component_number . ".2." . $category_number . ".1 ".lang_get('setup_and_config')."</h4><div><pre>" .  htmlspecialchars($category[3])."</pre></div>";
    	$CONTENT .= "<h4>" . $component_number . ".2." . $category_number . ".2 ".lang_get('test_data')."</h4><div><pre>" .  htmlspecialchars($category[4])."</pre></div>";
	    $CONTENT .= "<h4>" . $component_number . ".2." . $category_number . ".3 ".lang_get('tools')."</h4><div><pre>" .  htmlspecialchars($category[5])."</pre></div>";
    	$CONTENT .= "<h4>" . $component_number . ".2." . $category_number . ".4 ".lang_get('test_cases')."</h4>";
    	$CONTENT .= "<p>";
  	}
}

/** print a test case data */
function print_testcase($testcase) 
{
  	global $CONTENT;
  	global $CONTENT_HEAD;
  	global $toc;
  	
	if ($toc) 
	{
	  	$CONTENT_HEAD .= '<p style="padding-left: 20px;"><a href="#tc' . $testcase[0] . '">' . htmlspecialchars($testcase[1]) . '</a></p>';
		$CONTENT .= "<a name='tc" . $testcase[0] . "'></a>";
	}
  	$CONTENT .= "<div class='tc'><table width=90%>";
  	$CONTENT .= "<tr><th>".lang_get('test_case')." " . $testcase[0] . ": " . htmlspecialchars($testcase[1]) . "</th></tr>";

  	if ($_GET['body'] == 'y') 
  	{
    	$CONTENT .= "<tr><td><u>".lang_get('summary')."</u>: " .  $testcase[2] . "</td></tr>";
    	$CONTENT .= "<tr><td><u>".lang_get('steps')."</u>:<br />" .  $testcase[3] . "</td></tr>";
    	$CONTENT .= "<tr><td><u>".lang_get('expected_results')."</u>:<br />" .  $testcase[4] . "</td></tr>";
  	} else if (($_GET['body'] == 'n') and ($_GET['summary'] == 'y')) {
    	$CONTENT .= "<tr><td><u>".lang_get('summary')."</u>: " .  $testcase[2] . "</td></tr>";
  	}

  	$CONTENT .= "</table></div>";
}

/** print Test Specification data within category */
function generate_product_TCs($idCategory)
{
	$sqlTC = "select id,title, summary, steps, exresult from " .
				"mgttestcase where catid=" . $idCategory . " order by TCorder, id";
    $resultTC = do_mysql_query($sqlTC);

	if (!$resultTC)
		tLog($sqlTC . ' | error: ' . mysql_error(), 'ERROR');

	if (mysql_num_rows($resultTC) > 0)
	{
	    while ($myrowTC = mysql_fetch_row($resultTC))
		{
			print_testcase($myrowTC);
		}
	}
  	else
    	$CONTENT .= "<p>".lang_get('no_test_case')."</p>";
}

/** print Test Case Suite data within category */
function generate_testSuite_TCs($idCategory)
{
/* commented query use mgttestcase instead of testcase table
	  	$sqlTC = "select mgttestcase.id ,mgttestcase.title, mgttestcase.summary, " .
	  			"mgttestcase.steps, mgttestcase.exresult from " .
	    		"mgttestcase,testcase where testcase.mgttcid=mgttestcase.id" .  
				" and testcase.catid=" . $idCategory . " order by testcase.TCorder, testcase.mgttcid";
*/
	$sqlTC = "select id,title, summary, steps, exresult,mgttcid, keywords from " .
 	   		"testcase where catid=" . $idCategory . " order by TCorder, mgttcid";
	$resultTC = do_mysql_query($sqlTC);

	if (!$resultTC)
		tLog($sqlTC . ' | error: ' . mysql_error(), 'ERROR');

	if (mysql_num_rows($resultTC) > 0)
	{
		while ($myrowTC = mysql_fetch_row($resultTC))
		{
	   		print_testcase($myrowTC);
		}
	}
  	else
    	$CONTENT .= "<p>".lang_get('no_test_case')."</p>";
}

function generate_testSuite_Categories($idComponent)
{
	  	//Display all of the categories of the COM above
	    $sqlCAT = "select id from category where compid=" . $idComponent . 
				" order by CATorder, id";
	  	$resultCAT = do_mysql_query($sqlCAT);
	
	  	while ($myrowCAT = mysql_fetch_row($resultCAT))
		{ //display all the categories until we run out
	    	//Select the id and name from the com that was passed in
	    	$sqlMGTCAT = "select mgtcategory.id,mgtcategory.name,mgtcategory.objective," .
	    			"mgtcategory.config,mgtcategory.data,mgtcategory.tools from " .
	      			"mgtcategory,category where mgtcatid=mgtcategory.id and " .
	      			"category.id=" . $myrowCAT[0];
			$resultMGTCAT = do_mysql_query($sqlMGTCAT);
	    	$myrowMGTCAT = mysql_fetch_row($resultMGTCAT); //display components until we run out
	
	    	print_category($myrowMGTCAT);
	    	generate_testSuite_TCs($myrowCAT[0]);
	  	}
}

function generate_product_CATs($idComponent)
{
    $sqlCAT = "select id,name,objective,config,data,tools from mgtcategory where compid=" . 
	    		$idComponent .	" order by CATorder, id";
  	$resultCAT = do_mysql_query($sqlCAT);
	while ($myrowCAT = mysql_fetch_row($resultCAT))
	{ //display all the categories until we run out
	   	print_category($myrowCAT);
		generate_product_TCs($myrowCAT[0]);
	}
}

// Work with Test Specification of Product
if($_GET['type'] == 'product')
{
	// user wants to print the entire test specification
	if($_GET['edit'] == 'product')
	{
	    print_header("", $toc); // no more information
	
	    $sqlMGTCOM = "select id,name,intro,scope,ref,method,lim, prodid" .
	    		" from mgtcomponent where  mgtcomponent.prodid=" . 
	    		$_SESSION['productID'] . " order by mgtcomponent.name" ;
	  	$resultMGTCOM = do_mysql_query($sqlMGTCOM);
	  	while($myrowCOM = mysql_fetch_row($resultMGTCOM))
		{ 
			//display components until we run out
			print_component($myrowCOM);
			generate_product_CATs($myrowCOM[0]);
	  	}
	//if the user wants to print only a component they will enter here
	}
	else if($_GET['edit'] == 'component')
	{
	  	$myrowCOM = getComponent($_GET['data']);
	  	print_header("Component: " . $myrowCOM[1], $toc);
	  	print_component($myrowCOM);
		generate_product_CATs($_GET['data']);
	//if the user wants to print only a category they will enter here
	}
	else if($_GET['edit'] == 'category')
	{
	  	$myrowCAT = getCategory($_GET['data']); 
	  	$myrowCOM = getComponent($myrowCAT[0]);
	
	  	print_header("Category: " . $myrowCAT[1], $toc);
	  	print_component($myrowCOM);
	  	print_category($myrowCAT);
	
	  	//Print TCs
		generate_product_TCs($_GET['data']);
		//if the user didn't pick anything this statement will be run
	}
	else if(!$_GET['edit'])
	{ 
		tLog("GET['edit'] has invalid value.", 'ERROR');
		exit();
	}
} // endif product


// ---------- Test Case Suite Print --------------------------------------

if($_GET['type'] == 'testSet')
{
	//if the user wants to print the entire test plan they have chosen this if statement
	if($_GET['level'] == 'root')
	{
	    // get project name for display
	    print_header(lang_get('test_case_suite').": " . $_SESSION['testPlanName'], $toc);
	
	    //Select the id and name from the com that was passed in
	    $sqlMGTCOM = "select mgtcomponent.id,mgtcomponent.name,mgtcomponent.intro," .
	    		"mgtcomponent.scope,mgtcomponent.ref,mgtcomponent.method,mgtcomponent.lim," .
	    		"component.id from mgtcomponent,component where mgtcompid=mgtcomponent.id" .
	    		" and component.projid=" . $_SESSION['testPlanId'] . 
				" order by mgtcomponent.name";

  	  	$resultMGTCOM = do_mysql_query($sqlMGTCOM);
	  	while($myrowMGTCOM = mysql_fetch_row($resultMGTCOM))
		{ 
			//display components until we run out
	    	print_component($myrowMGTCOM);
	    	generate_testSuite_Categories($myrowMGTCOM[7]);
	  	}
	//if the user wants to print only a component they will enter here
	}
	else if($_GET['level'] == 'component')
	{
	  	// get component data
	    $sqlMGTCOM = "select mgtcomponent.id,mgtcomponent.name,mgtcomponent.intro," .
	    		"mgtcomponent.scope,mgtcomponent.ref,mgtcomponent.method,mgtcomponent.lim from " .
	    		"mgtcomponent,component where mgtcompid=mgtcomponent.id and component.id=" . 
	    		$_GET['data'];
	    $resultMGTCOM = do_mysql_query($sqlMGTCOM);
	    $myrowMGTCOM = mysql_fetch_row($resultMGTCOM); //display components until we run out
	
	    // print
	    print_header("Test Case Suite: " . $_SESSION['testPlanName'] . " - " . $myrowMGTCOM[1], $toc);
	  	print_component($myrowMGTCOM);
		generate_testSuite_Categories($_GET['data']);
	//if the user wants to print only a category they will enter here
	}
	else if($_GET['level'] == 'category')
	{
		// Get category
	    $sqlCAT = "select id,name,compid from category where id=" . $_GET['data'] . 
				" order by CATorder, id";
	  	$resultCAT = do_mysql_query($sqlCAT);
	  	$myrowCAT = mysql_fetch_row($resultCAT); //display all the components until we run out
	  	tLog($sqlCAT . " ===>>> " . implode(",", $myrowCAT));
	
		// info about component above
		$sqlMGTCOM = "select mgtcomponent.id,mgtcomponent.name,mgtcomponent.intro," .
				"mgtcomponent.scope,mgtcomponent.ref,mgtcomponent.method,mgtcomponent.lim from " .
	    		"mgtcomponent,component where component.mgtcompid=mgtcomponent.id and component.id=" . 
	    		$myrowCAT[2];
	  	$resultMGTCOM = do_mysql_query($sqlMGTCOM);
	  	if (!$resultMGTCOM)
	  		tLog($sqlMGTCOM . ' | error: ' . mysql_error(), 'ERROR');
	  	$myrowMGTCOM = mysql_fetch_row($resultMGTCOM); //display components until we run out
	
	  	//Select the id and name from the com that was passed in
	    $sqlMGTCAT = "select mgtcategory.id,mgtcategory.name,mgtcategory.objective," .
	    		"mgtcategory.config,mgtcategory.data,mgtcategory.tools from " .
	    		"mgtcategory,category where category.mgtcatid=mgtcategory.id and category.id=" . 
	    		$myrowCAT[0];
	  	$resultMGTCAT = do_mysql_query($sqlMGTCAT);
	  	$myrowMGTCAT = mysql_fetch_row($resultMGTCAT); //display components until we run out
	
	  	print_header("Test Case Suite: " . $_SESSION['testPlanName'] . " - " . $myrowMGTCAT[1], $toc);
	  	print_component($myrowMGTCOM);
	  	print_category($myrowMGTCAT);
		generate_testSuite_TCs($myrowCAT[0]);
	}
	else
	{
		// something is wrong 
		tLog("GET['level'] has invalid value.", 'ERROR');
		exit();
	}
} // if project


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