<?

////////////////////////////////////////////////////////////////////////////////
//File:     copyMoveResults.php
//Author:   Chad Rosen
//Purpose:  This page takes the result of the copy/move page and performs the database
//          actions necessary.
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

require_once("../functions/refreshLeft.php"); //This adds the function that refreshes the left hand frame

if($_POST['submit'] == 'moveCOM') //user wants to move a component to another project
{
	echo "Component has been moved";

	$sqlMoveCopy = "update mgtcomponent set prodid='" . $_POST['moveCopy'] . "' where id='" . $_POST['id'] . "'";
	$resultMoveCopy = mysql_query($sqlMoveCopy);

	echo "<hr>Click <a href='manage/archiveData.php?&edit=component&data=" . $_POST['id'] .  "'>here</a> to return to Component just edited";

}elseif($_POST['submit'] == 'moveCAT') //user wants to move a category to another component
{
	echo "Category has been moved";
	$sqlMoveCopy = "update mgtcategory set compid='" . $_POST['moveCopy'] . "' where id='" . $_POST['id'] . "'";
	$resultMoveCopy = mysql_query($sqlMoveCopy);

	echo "<hr>Click <a href='manage/archiveData.php?&edit=category&data=" . $_POST['id'] .  "'>here</a> to return to Category just edited";

}elseif($_POST['submit'] == 'moveTC') //user wants to move a test case to another category
{
	echo "Test Case has been moved";
	$sqlMoveCopy = "update mgttestcase set catid='" . $_POST['moveCopy'] . "' where id='" . $_POST['id'] . "'";
	$resultMoveCopy = mysql_query($sqlMoveCopy);

	echo "<hr>Click <a href='manage/archiveData.php?&edit=testcase&data=" . $_POST['id'] .  "'>here</a> to return to TestCase just edited";

}elseif($_POST['submit'] == 'copyCOM')
{
	echo "Component has been copied";

	//Select all of the component data so that we can insert it later

	$sqlCopyCom = "select id, name, intro, scope, ref, method, lim from mgtcomponent where id='" . $_POST['id'] . "'";

	$resultCopyCom = mysql_query($sqlCopyCom);

	$myrowCopyCom = mysql_fetch_row($resultCopyCom);

	//Insert the component data from above into the component table
	
	$sqlInsertCom = "insert into mgtcomponent (name,intro,scope,ref,method,lim,prodid) values ('" . mysql_escape_string($myrowCopyCom[1]) . "','" . mysql_escape_string($myrowCopyCom[2]) . "','" . mysql_escape_string($myrowCopyCom[3]) . "','" . mysql_escape_string($myrowCopyCom[4]) . "','" . mysql_escape_string($myrowCopyCom[5]) . "','" . mysql_escape_string($myrowCopyCom[6]) . "','" . $_POST['moveCopy'] . "')";
	
	$resultInsertCom = mysql_query($sqlInsertCom);
	
	//grab COM id so that we can use it as a foreign key in the category table
	
	$comID =  mysql_insert_id(); //Grab the id of the category just entered
	//Select the category info so that we can copy it

	$sqlCopyCat = "select name,objective,config,data,tools,compid,CATorder,id from mgtcategory where compid='" . $myrowCopyCom[0] . "'";

	$resultCopyCat = mysql_query($sqlCopyCat);

	while($myrowCopyCat = mysql_fetch_row($resultCopyCat))
	{

		//Insert the category info

		$sqlInsertCat = "insert into mgtcategory (name,objective,config,data,tools,compid,CATorder) values ('" . mysql_escape_string($myrowCopyCat[0]) . "','" . mysql_escape_string($myrowCopyCat[1]) . "','" . mysql_escape_string($myrowCopyCat[2]) . "','" . mysql_escape_string($myrowCopyCat[3]) . "','" . mysql_escape_string($myrowCopyCat[4]) . "','" . $comID . "','" . mysql_escape_string($myrowCopyCat[6]) . "')";

		$resultInsertCAT = mysql_query($sqlInsertCat);
		
		//grab the category id so that we can use it as the foreign key

		$catID =  mysql_insert_id(); //Grab the id of the category just entered

		//Select the test case data so that we can add it in later

		$sqlMoveCopy= "select title, summary, steps, exresult, keywords, version, author, TCorder from mgttestcase where catid='" . $myrowCopyCat[7] . "'";

		$resultMoveCopy = mysql_query($sqlMoveCopy);

		while($myrowMoveCopy = mysql_fetch_row($resultMoveCopy))
		{
			//Insert the data from above into the test case table

			$sqlInsert = "insert into mgttestcase (title,summary,steps,exresult,keywords,catid,version,author,TCorder) values ('". mysql_escape_string($myrowMoveCopy[0])  . "','" . mysql_escape_string($myrowMoveCopy[1]) . "','". mysql_escape_string($myrowMoveCopy[2]) . "','" . mysql_escape_string($myrowMoveCopy[3]) . "','" . mysql_escape_string($myrowMoveCopy[4]) ."','". $catID ."','". mysql_escape_string($myrowMoveCopy[6]) ."','". mysql_escape_string($myrowMoveCopy[7]) . "','". mysql_escape_string($myrowMoveCopy[8]) . "')";

			$resultInsert = mysql_query($sqlInsert);


		}//end the insertion of test cases

	}//end the insertion of categories

	echo "<hr>Click <a href='manage/archiveData.php?&edit=component&data=" . $comID.  "'>here</a> to return to Component just edited";

	$highLight = "&edit=component&data=" . $comID;

}elseif($_POST['submit'] == 'copyCAT')
{
	echo "Category has been copied";

	//Select the category info so that we can copy it

	$sqlCopyCat = "select name,objective,config,data,tools,compid,CATorder,id from mgtcategory where id='" . $_POST['id'] . "'";
	$resultCopyCat = mysql_query($sqlCopyCat);
	$myrowCopyCat = mysql_fetch_row($resultCopyCat);

	//Insert the category info

	$sqlInsertCat = "insert into mgtcategory (name,objective,config,data,tools,compid,CATorder) values ('" . mysql_escape_string($myrowCopyCat[0]) . "','" . mysql_escape_string($myrowCopyCat[1]) . "','" . mysql_escape_string($myrowCopyCat[2]) . "','" . mysql_escape_string($myrowCopyCat[3]) . "','" . mysql_escape_string($myrowCopyCat[4]) . "','" . $_POST['moveCopy'] . "','" . mysql_escape_string($myrowCopyCat[6]) . "')";
	
	$resultInsertCAT = mysql_query($sqlInsertCat);
	
	//grab the category id so that we can use it as the foreign key

	$catID =  mysql_insert_id(); //Grab the id of the category just entered

	//Select the test case data so that we can add it in later

	$sqlMoveCopy= "select title, summary, steps, exresult, keywords, version, author, TCorder from mgttestcase where catid='" . $myrowCopyCat[7] . "'";

	$resultMoveCopy = mysql_query($sqlMoveCopy);

	while($myrowMoveCopy = mysql_fetch_row($resultMoveCopy))
	{
		//Insert the data from above into the test case table

		$sqlInsert = "insert into mgttestcase (title,summary,steps,exresult,keywords,catid,version,author,TCorder) values ('". mysql_escape_string($myrowMoveCopy[0])  . "','" . mysql_escape_string($myrowMoveCopy[1]) . "','". mysql_escape_string($myrowMoveCopy[2]) . "','" . mysql_escape_string($myrowMoveCopy[3]) . "','" . mysql_escape_string($myrowMoveCopy[4]) ."','". $catID ."','". mysql_escape_string($myrowMoveCopy[6]) ."','". mysql_escape_string($myrowMoveCopy[7]) . "','". mysql_escape_string($myrowMoveCopy[8]) . "')";

		$resultInsert = mysql_query($sqlInsert);


	}

	echo "<hr>Click <a href='manage/archiveData.php?&edit=category&data=" . $catID .  "'>here</a> to return to Category just edited";

	$highLight = "&edit=category&data=" . $catID;

}elseif($_POST['submit'] == 'copyTC')
{

	echo "Test Case has been copied";

	//Select the test case data so that we can add it in later

	$sqlMoveCopy= "select title, summary, steps, exresult, keywords, catid, version, author, TCorder from mgttestcase where id='" . $_POST['id'] . "'";

	$resultMoveCopy = mysql_query($sqlMoveCopy);

	$myrowMoveCopy = mysql_fetch_row($resultMoveCopy);

	//Insert the data from above into the test case table

	$sqlInsert = "insert into mgttestcase (title,summary,steps,exresult,keywords,catid,version,author,TCorder) values ('". mysql_escape_string($myrowMoveCopy[0])  . "','" . mysql_escape_string($myrowMoveCopy[1]) . "','". mysql_escape_string($myrowMoveCopy[2]) . "','" . mysql_escape_string($myrowMoveCopy[3]) . "','" . mysql_escape_string($myrowMoveCopy[4]) ."','". $_POST['moveCopy'] ."','". mysql_escape_string($myrowMoveCopy[6]) ."','". mysql_escape_string($myrowMoveCopy[7]) . "','". mysql_escape_string($myrowMoveCopy[8]) . "')";

	$resultInsert = mysql_query($sqlInsert);

	$tcID =  mysql_insert_id(); //Grab the id of the test case just entered

	echo "<hr>Click <a href='manage/archiveData.php?&edit=testcase&data=" . $tcID .  "'>here</a> to return to Test Case just edited";

	$highLight = "&edit=testcase&data=" . $tcID;

}

	if($_POST['submit'] == 'moveCOM')
	{
		$highLight = "&edit=component&data=" . $_POST['id'];
	}
	else if($_POST['submit'] == 'moveCAT')
	{
		$highLight = "&edit=category&data=" . $_POST['id'];	
	}
	else if($_POST['submit'] == 'moveTC')
	{
		$highLight = "&edit=testcase&data=" . $_POST['id'];
	}


	$page =  $basehref . "/manage/archiveLeft.php?product=" . $product . $highLight;

	refreshFrame($page); //call the function below to refresh the left frame
