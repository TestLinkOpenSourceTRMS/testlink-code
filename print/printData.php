<?php

////////////////////////////////////////////////////////////////////////////////
//File:     printData.php
//Author:   Chad Rosen
//Purpose:  This page shows the data that will be printed.
////////////////////////////////////////////////////////////////////////////////

require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>

<?

$section_number = 0;
$CONTENT = "";
//this function prints the table of contents before the rest of the data
function table_of_contents($type,$data) {

  //print the Table Of Contents
  global $CONTENT;
  echo "Table Of Contents";
  $CONTENT .= "Table Of Contents";
  if($type == 'project') { //display the entire project 

    //display the project name
    

    //display the component names

    //display the category names

    //display the test case names

  }elseif($type == 'com') { //display all of the components, categories, and test cases
		
    //display the project name

    //display the component name

    //display the category names

    //display the test case names


  }elseif($type == 'cat') { //display all of the categories and test cases

    //display the project name

    //display the component name

    //display the category names

    //display the test case names

  }
}

//this function prints the header
function print_header($title) {
  echo "<head>";
  echo "<a href='./print/generateDoc.php'><b>Generate Report(.doc format)</b></a>";
  global $CONTENT;
  echo "<title>Test Plan for " . $title . "</title>";
  echo "</head>";
  echo "<h1 class=print>" . $title . "</h1>";
  echo "<address>Printed by TestLink on " . date('Y-m-d H:i:s', time()) . "</address>";
  echo "<hr class=print>";
  $CONTENT .= "<title>Test Plan for " . $title . "</title>";
  $CONTENT .= "</head>";
  $CONTENT .= "<h1 class=print>" . $title . "</h1>";
  $CONTENT .= "<address>Printed by TestLink on " . date('Y-m-d H:i:s', time()) . "</address>";
  $CONTENT .= "<hr class=print>";
}


//when I need to print a component I call this function
function print_component($component, $master) {
  global $CONTENT;
  echo "<h2 class=print>" . $master . ": " . $component[0] . "</h2>";
  $CONTENT .=  "<h2 class=print>" . $master . ": " . $component[0] . "</h2>";
  if ($_GET['header'] == 'y') {
    global $section_number;
    $section_number = 1;
    echo "<h3 class=print>" . $section_number . ".0 Introduction</h3>" .  $component[1];
    $CONTENT .= "<h3 class=print>" . $section_number . ".0 Introduction</h3>" .  $component[1];

    $section_number ++;
    echo "<h4 class=print>" . $section_number . ".1 Scope of this Document</h4>" .  $component[2];
    echo "<h4 class=print>" . $section_number . ".2 References</h4>" .  $component[3];
    echo "<h3 class=print>" . $section_number . ".0 Test Methodology</h3>" . $component[4];
    echo "<h4 class=print>" . $section_number . ".1 Test Limitations</h4>" . $component[5];
    $CONTENT .= "<h4 class=print>" . $section_number . ".1 Scope of this Document</h4>" .  $component[2];
    $CONTENT .= "<h4 class=print>" . $section_number . ".2 References</h4>" .  $component[3];
    $CONTENT .= "<h3 class=print>" . $section_number . ".0 Test Methodology</h3>" . $component[4];
    $CONTENT .= "<h4 class=print>" . $section_number . ".1 Test Limitations</h4>" . $component[5];
	 
 }
} 


//when I need to print a category I call this function
function print_category($category) {
  global $CONTENT;
  if ($_GET['header'] == 'y') {
    global $section_number;
    $section_number++;
    echo "<h3 class=print>" . $section_number . ".0 Category: " . $category[0] . "</h3>";
    echo $category[1];
    echo "<h4 class=print>" . $section_number . ".1 Setup and Configuration</h4>" .  $category[2];
    echo "<h4 class=print>" . $section_number . ".2 Test Data</h4>" .  $category[3];
    echo "<h4 class=print>" . $section_number . ".3 Tools</h4>" .  $category[4];
    echo "<h4 class=print>" . $section_number . ".4 Test Procedures</h4>";
    echo "<P>";
    $CONTENT .= "<h3 class=print>" . $section_number . ".0 Category: " . $category[0] . "</h3>";
    $CONTENT .= $category[1];
    $CONTENT .= "<h4 class=print>" . $section_number . ".1 Setup and Configuration</h4>" .  $category[2];
    $CONTENT .= "<h4 class=print>" . $section_number . ".2 Test Data</h4>" .  $category[3];
    $CONTENT .= "<h4 class=print>" . $section_number . ".3 Tools</h4>" .  $category[4];
    $CONTENT .= "<h4 class=print>" . $section_number . ".4 Test Procedures</h4>";
    $CONTENT .= "<P>";

  } else {
    echo "<h3 class=print>Category: " . $category[0] . "</h3>";
    $CONTENT .= "<h3 class=print>Category: " . $category[0] . "</h3>";
  }
}


//when I need to print a test case I call this function
function print_testcase($testcase) {
  global $CONTENT;
  if ($_GET['title'] == 'y') {
    echo "<table width=100% class=print>";
    echo "<tr><td class=printhdr>Test Case " . htmlspecialchars($testcase[5]) . ": " . $testcase[1] . "</td></tr>";
    echo "<tr><td class=print><u>Summary</u>: " .  htmlspecialchars(nl2br($testcase[2])) . "</td></tr>";
    echo "<tr><td class=print><u>Steps</u>:<br>" .  $testcase[3] . "</td></tr>";
    echo "<tr><td class=print><u>Expected Results</u>:<br>" .  $testcase[4] . "</td></tr>";
    echo "<tr><td class=print><u>Keywords</u>:<br>" . $testcase[6] . "</td></tr>";
    echo "</table><br>";
    $CONTENT .= "<table width=100% class=print>";
    $CONTENT .= "<tr><td class=printhdr>Test Case " . htmlspecialchars($testcase[5]) . ": " . $testcase[1] . "</td></tr>";
    $CONTENT .= "<tr><td class=print><u>Summary</u>: " .  htmlspecialchars(nl2br($testcase[2])) . "</td></tr>";
    $CONTENT .= "<tr><td class=print><u>Steps</u>:<br>" .  $testcase[3] . "</td></tr>";
    $CONTENT .= "<tr><td class=print><u>Expected Results</u>:<br>" .  $testcase[4] . "</td></tr>";
    $CONTENT .= "<tr><td class=print><u>Keywords</u>:<br>" . $testcase[6] . "</td></tr>";
    $CONTENT .= "</table><br>";

  } else if (($_GET['title'] == 'n') and ($_GET['summary'] == 'y')) {
    echo "<table width=100% class=print>";
    echo "<tr><td class=printhdr>Test Case " . htmlspecialchars($testcase[5]) . ": " . $testcase[1] . "</td></tr>";
    echo "<tr><td class=print><u>Summary</u>: " .  htmlspecialchars(nl2br($testcase[2])) . "</td></tr>";
    echo "</table><br>";
    $CONTENT .= "<table width=100% class=print>";
    $CONTENT .= "<tr><td class=printhdr>Test Case " . htmlspecialchars($testcase[5]) . ": " . $testcase[1] . "</td></tr>";
    $CONTENT .= "<tr><td class=print><u>Summary</u>: " .  htmlspecialchars(nl2br($testcase[2])) . "</td></tr>";
    $CONTENT .= "</table><br>";

  } else if (($_GET['title'] == 'n') and ($_GET['summary'] == 'n')) {
    echo "<table width=100% class=print>";
    echo "<tr><td class=printhdr>Test Case " . htmlspecialchars($testcase[5]) . ": " . $testcase[1] . "</td></tr>";
    echo "</table><br>";
    $CONTENT .= "<table width=100% class=print>";
    $CONTENT .= "<tr><td class=printhdr>Test Case " . htmlspecialchars($testcase[5]) . ": " . $testcase[1] . "</td></tr>";
    $CONTENT .= "</table><br>";

  }
}


//if the user wants to print the entire test plan they have chosen this if statement
if($_GET['edit'] == 'pro') {

  //Select the id and name from the com that was passed in
  if($_GET['type'] == 'project') {		
    $sqlMGTCOM = "select mgtcomponent.name,intro,scope,ref,method,lim,component.id from mgtcomponent,component where mgtcompid=mgtcomponent.id and component.projid=" . $_GET['proj'] . " order by mgtcomponent.name";

    // get project name for display
    $sqlTITLE = "select name from project where id =" . $_GET['proj'];
    $resultTITLE = mysql_query($sqlTITLE);
    $myrowTITLE = mysql_fetch_row($resultTITLE);
    print_header("Project: " . $myrowTITLE[0]);

  }elseif($_GET['type'] == 'product') {
    $sqlMGTCOM = "select mgtcomponent.name,intro,scope,ref,method,lim, prodid, mgtcomponent.id from mgtcomponent where  mgtcomponent.prodid=" . $_GET['proj'] . " order by mgtcomponent.name" ;

    // get product name for display
    $sqlTITLE = "select name from mgtproduct where id =" . $_GET['proj'];
    $resultTITLE = mysql_query($sqlTITLE);
    $myrowTITLE = mysql_fetch_row($resultTITLE);
    print_header("Product: " . $myrowTITLE[0]);
  }

  $resultMGTCOM = mysql_query($sqlMGTCOM);

  while($myrowMGTCOM = mysql_fetch_row($resultMGTCOM)) { //display components until we run out
    print_component($myrowMGTCOM, $myrowTITLE[0]);

    //Select the id and name from the com that was passed in
    if($_GET['type'] == 'project') {
      $sqlCOM = "select id,name from component where id=" . $myrowMGTCOM[6];
    } elseif($_GET['type'] == 'product') {
      $sqlCOM = "select id,name from mgtcomponent where id=" . $myrowMGTCOM[7];
    }
	    
    $resultCOM = mysql_query($sqlCOM);
    $myrowCOM = mysql_fetch_row($resultCOM); //display components until we run out
	    
    //Display all of the categories of the COM above
    if($_GET['type'] == 'project') {
      $sqlCAT = "select id,name from category where compid=" . $myrowCOM[0] . " order by CATorder, id";
    } elseif($_GET['type'] == 'product') {
      $sqlCAT = "select id,name,compid from mgtcategory where compid=" . $myrowCOM[0] . " order by CATorder, id";
    }	
	    
    $resultCAT = mysql_query($sqlCAT);
	    
    while ($myrowCAT = mysql_fetch_row($resultCAT)) { //display all the categories until we run out
      
      //Select the id and name from the com that was passed in	
      if($_GET['type'] == 'project') {
	$sqlMGTCAT = "select mgtcategory.name,objective,config,data,tools from mgtcategory,category where mgtcatid=mgtcategory.id and category.id=" . $myrowCAT[0];  
      } elseif($_GET['type'] == 'product') {
	$sqlMGTCAT = "select mgtcategory.name,objective,config,data,tools from mgtcategory,category where mgtcategory.id=" . $myrowCAT[0];
      }
		
      $resultMGTCAT = mysql_query($sqlMGTCAT);
      $myrowMGTCAT = mysql_fetch_row($resultMGTCAT); //display components until we run out
		
      print_category($myrowMGTCAT);
		
      if($_GET['type'] == 'project') {
	$sqlTC = "select id,title, summary, steps, exresult,mgttcid, keywords from testcase where catid=" . $myrowCAT[0] . " order by TCorder, mgttcid";
      } elseif($_GET['type'] == 'product') {
	$sqlTC = "select id,title, summary, steps, exresult, id, keywords from mgttestcase where catid=" . $myrowCAT[0] . " order by TCorder, id";
      }
	      
      $resultTC = mysql_query($sqlTC);
      
      while ($myrowTC = mysql_fetch_row($resultTC)) { //display all the components until we run out
	print_testcase($myrowTC);
      }
    }
  }
}

//if the user wants to print only a component they will enter here
if($_GET['edit'] == 'component')
{
  //Select the id and name from the com that was passed in
  
  if($_GET['type'] == 'project') {
    $sqlMGTCOM = "select mgtcomponent.name,intro,scope,ref,method,lim from mgtcomponent,component where mgtcompid=mgtcomponent.id and component.id=" . $_GET['com'] . " order by mgtcomponent.name";

    // get project id for the owner of this component
    $sqlProjId = "select projid from component where id =" . $_GET['com'];
    $sqlProjIdQ = mysql_query($sqlProjId);
    $myrowProjId = mysql_fetch_row($sqlProjIdQ);
	
    // get the name of the project for the project id
    $sqlProjName = "select name from project where id =" . $myrowProjId[0];
    $sqlProjNameQ = mysql_query($sqlProjName);
    $myrowName = mysql_fetch_row($sqlProjNameQ);

  }elseif($_GET['type'] == 'product') {
    $sqlMGTCOM = "select mgtcomponent.name,intro,scope,ref,method,lim from mgtcomponent,component where  mgtcomponent.id=" . $_GET['com'] . " order by mgtcomponent.name";
    
    // get product id for the owner of this component
    $sqlProdId = "select prodid from mgtcomponent where id =" . $_GET['com'];
    $sqlProdIdQ = mysql_query($sqlProdId);
    $myrowProdId = mysql_fetch_row($sqlProdIdQ);
    
    // get the name of the product for the product id
    $sqlProdName = "select name from mgtproduct where id =" . $myrowProdId[0];
    $sqlProdNameQ = mysql_query($sqlProdName);
    $myrowName = mysql_fetch_row($sqlProdNameQ);
  }

  $resultMGTCOM = mysql_query($sqlMGTCOM);
  $myrowMGTCOM = mysql_fetch_row($resultMGTCOM); //display components until we run out

  print_header("Component: " . $myrowMGTCOM[0]);
  print_component($myrowMGTCOM, $myrowName[0]);

	
  //Select the id and name from the com that was passed in
  if($_GET['type'] == 'project') {
    $sqlCOM = "select id,name from component where id=" . $_GET['com'];
  }elseif($_GET['type'] == 'product') {
    $sqlCOM = "select id,name from mgtcomponent where id=" . $_GET['com'];
  }

  $resultCOM = mysql_query($sqlCOM);
  $myrowCOM = mysql_fetch_row($resultCOM); //display components until we run out
	
  //Display all of the categories of the COM above
  if($_GET['type'] == 'project') {
    $sqlCAT = "select id,name from category where compid=" . $myrowCOM[0] . " order by CATorder, id";

  }elseif($_GET['type'] == 'product') {
    $sqlCAT = "select id,name from mgtcategory where compid=" . $myrowCOM[0] . " order by CATorder, id";
  }	
	
  $resultCAT = mysql_query($sqlCAT);

  while ($myrowCAT = mysql_fetch_row($resultCAT)) { //display all the categories until we run out
    
    //Select the id and name from the com that was passed in
    if($_GET['type'] == 'project') {
      $sqlMGTCAT = "select mgtcategory.name,objective,config,data,tools from mgtcategory,category where mgtcatid=mgtcategory.id and category.id=" . $myrowCAT[0];

    }elseif($_GET['type'] == 'product') {
      $sqlMGTCAT = "select mgtcategory.name,objective,config,data,tools from mgtcategory,category where mgtcategory.id=" . $myrowCAT[0];
    }

    $resultMGTCAT = mysql_query($sqlMGTCAT);
    $myrowMGTCAT = mysql_fetch_row($resultMGTCAT); //display components until we run out

    print_category($myrowMGTCAT);

    if($_GET['type'] == 'project') {
      $sqlTC = "select id,title, summary, steps, exresult,mgttcid, keywords from testcase where catid=" . $myrowCAT[0] . " order by TCorder, mgttcid";

    }elseif($_GET['type'] == 'product') {
      $sqlTC = "select id,title, summary, steps, exresult, id, keywords from mgttestcase where catid=" . $myrowCAT[0] . " order by TCorder, id";
    }
		
    $resultTC = mysql_query($sqlTC);

    while ($myrowTC = mysql_fetch_row($resultTC)) { //display all the components until we run out
      print_testcase($myrowTC);
    }
  }

//if the user wants to print only a category they will enter here
}elseif($_GET['edit'] == 'category') {

  if($_GET['type'] == 'project') {
    $sqlCAT = "select id,name,compid from category where id=" . $_GET['cat'] . " order by CATorder, id";

  }elseif($_GET['type'] == 'product') {
    $sqlCAT = "select id,name,compid from mgtcategory where id=" . $_GET['cat'] . " order by CATorder, id";
  }


  //Display all of the categories of the COM above
  $resultCAT = mysql_query($sqlCAT);
  $myrowCAT = mysql_fetch_row($resultCAT); //display all the components until we run out

  //I want to display the component name along with the category name
  if($_GET['type'] == 'project') {
      $sqlCOM = "select name,id from component where id=" . $myrowCAT[2];
  }elseif($_GET['type'] == 'product') {
    $sqlCOM = "select name,id from mgtcomponent where id=" . $myrowCAT[2];
  }
  
  $resultCOM = mysql_query($sqlCOM);
  $myrowCOM = mysql_fetch_row($resultCOM);

  //Select the id and name from the com that was passed in
  if($_GET['type'] == 'project') {
    $sqlMGTCOM = "select mgtcomponent.name,intro,scope,ref,method,lim from mgtcomponent,component where mgtcompid=mgtcomponent.id and component.id=" . $myrowCOM[1];

    $sqlProjId = "select projid from component where id =" . $myrowCOM[1];
    $sqlProjIdQ = mysql_query($sqlProjId);
    $myrowProjId = mysql_fetch_row($sqlProjIdQ);
		
    $sqlProjName = "select name from project where id =" . $myrowProjId[0];
    $sqlProjNameQ = mysql_query($sqlProjName);
    $myrowName = mysql_fetch_row($sqlProjNameQ);

  }elseif($_GET['type'] == 'product') {
    $sqlMGTCOM = "select mgtcomponent.name,intro,scope,ref,method,lim from mgtcomponent,component where  mgtcomponent.id=" . $myrowCOM[1];

    // get product id for the owner of this component
    $sqlProdId = "select prodid from mgtcomponent where id =" . $myrowCOM[1];
    $sqlProdIdQ = mysql_query($sqlProdId);
    $myrowProdId = mysql_fetch_row($sqlProdIdQ);
		
    // get the name of the product for the product id
    $sqlProdName = "select name from mgtproduct where id =" . $myrowProdId[0];
    $sqlProdNameQ = mysql_query($sqlProdName);
    $myrowName = mysql_fetch_row($sqlProdNameQ);

  }
	
  $resultMGTCOM = mysql_query($sqlMGTCOM);
  $myrowMGTCOM = mysql_fetch_row($resultMGTCOM); //display components until we run out

  //Select the id and name from the com that was passed in
  if($_GET['type'] == 'project') {
    $sqlMGTCAT = "select mgtcategory.name,objective,config,data,tools from mgtcategory,category where mgtcatid=mgtcategory.id and category.id=" . $myrowCAT[0];
  }elseif($_GET['type'] == 'product') {
    $sqlMGTCAT = "select mgtcategory.name,objective,config,data,tools from mgtcategory where mgtcategory.id=" . $myrowCAT[0];
  }
  
  $resultMGTCAT = mysql_query($sqlMGTCAT);
  $myrowMGTCAT = mysql_fetch_row($resultMGTCAT); //display components until we run out

  print_header("Category: " . $myrowMGTCAT[0]);
  print_component($myrowMGTCOM, $myrowName[0]);
  print_category($myrowMGTCAT);

  //Begin the table display
  if($_GET['type'] == 'project') {
    $sqlTC = "select id,title, summary, steps, exresult,mgttcid, keywords from testcase where catid=" . $myrowCAT[0] . " order by TCorder, mgttcid";
  }elseif($_GET['type'] == 'product') {
    $sqlTC = "select id,title, summary, steps, exresult, id, keywords from mgttestcase where catid=" . $myrowCAT[0] . " order by TCorder, id";
  }
	
  $resultTC = mysql_query($sqlTC);

  while ($myrowTC = mysql_fetch_row($resultTC)) {  //display all the components until we run out
    print_testcase($myrowTC);
  }

//if the user didn't pick anything this statement will be run
}elseif(!$_GET['edit']) { 

  echo "<table class=helptable width=100%>";
  echo "<tr><td class=helptablehdr><h2>Printing Test Cases</td></tr></table>";
  echo "<table class=helptable width=100%>";
  echo "<tr><td class=helptablehdr><b>Purpose:</td><td class=helptable><ul><li>This Page allows the user to print test cases by either their components or their categories</ul></td></tr>";
  echo "<tr><td class=helptablehdr><b>Getting Started:</td><td class=helptable>";	
  echo "<ol><li>Click on a component,category, or test cases to see all of the corresponding test cases below it.<li>EX:Clicking on a category will only show that categories test cases.";
  echo "<li>Use your browsers print functionality to actually print the test cases. <li>Note: Make sure to only print the right frame</ol></td></tr>";
  echo "</table>";
  $CONTENT .= "<table class=helptable width=100%>";
  $CONTENT .= "<tr><td class=helptablehdr><h2>Printing Test Cases</td></tr></table>";
  $CONTENT .= "<table class=helptable width=100%>";
  $CONTENT .= "<tr><td class=helptablehdr><b>Purpose:</td><td class=helptable><ul><li>This Page allows the user to print test cases by either their components or their categories</ul></td></tr>";
  $CONTENT .= "<tr><td class=helptablehdr><b>Getting Started:</td><td class=helptable>";
  $CONTENT .= "<ol><li>Click on a component,category, or test cases to see all of the corresponding test cases below it.<li>EX:Clicking on a category will only show that categories test cases.";
  $CONTENT .= "<li>Use your browsers print functionality to actually print the test cases. <li>Note: Make sure to only print the right frame</ol></td></tr>";
  $CONTENT .= "</table>";
}
?>
<?
	$CONTENT .= "<table></table>";
	$f1=fopen("report.html","w"); 
	fputs($f1,$CONTENT); 
	fclose($f1); 
?>
<a href="./print/generateDoc.php"><b>Generate Report(.doc format)</b></a>
