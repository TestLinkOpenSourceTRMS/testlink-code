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

}elseif($_POST['submit'] == 'moveCAT') //user wants to move a category to another component
{
	echo "Category has been moved";
	$sqlMoveCopy = "update mgtcategory set compid='" . $_POST['moveCopy'] . "' where id='" . $_POST['id'] . "'";
	$resultMoveCopy = mysql_query($sqlMoveCopy);

}elseif($_POST['submit'] == 'moveTC') //user wants to move a test case to another category
{
	echo "Test Case has been moved";
	$sqlMoveCopy = "update mgttestcase set catid='" . $_POST['moveCopy'] . "' where id='" . $_POST['id'] . "'";
	$resultMoveCopy = mysql_query($sqlMoveCopy);


}elseif($_POST['submit'] == 'copyCOM')
{
	echo "Component has been copied";

	//Select all of the component data so that we can insert it later

	$sqlCopyCom = "select id, name, intro, scope, ref, method, lim from mgtcomponent where id='" . $_POST['id'] . "'";

	$resultCopyCom = mysql_query($sqlCopyCom);

	$myrowCopyCom = mysql_fetch_row($resultCopyCom);

	//Insert the component data from above into the component table
	
	$myrowCopyCom[0] = mysql_escape_string($myrowCopyCom[0]);
	$myrowCopyCom[1] = mysql_escape_string($myrowCopyCom[1]); 
	$myrowCopyCom[2] = mysql_escape_string($myrowCopyCom[2]);
	$myrowCopyCom[3] = mysql_escape_string($myrowCopyCom[3]);
	$myrowCopyCom[4] = mysql_escape_string($myrowCopyCom[4]);
	$myrowCopyCom[5] = mysql_escape_string($myrowCopyCom[5]);
	$myrowCopyCom[6] = mysql_escape_string($myrowCopyCom[6]);

	$sqlInsertCom = "insert into mgtcomponent (name,intro,scope,ref,method,lim,prodid) values ('" . $myrowCopyCom[1] . "','" . $myrowCopyCom[2] . "','" . $myrowCopyCom[3] . "','" . $myrowCopyCom[4] . "','" . $myrowCopyCom[5] . "','" . $myrowCopyCom[6] . "','" . $_POST['moveCopy'] . "')";
	
	$resultInsertCom = mysql_query($sqlInsertCom);
	
	//grab COM id so that we can use it as a foreign key in the category table
	
	$comID =  mysql_insert_id(); //Grab the id of the category just entered
	//Select the category info so that we can copy it

	$sqlCopyCat = "select name,objective,config,data,tools,compid,CATorder,id from mgtcategory where compid='" . $myrowCopyCom[0] . "'";

	$resultCopyCat = mysql_query($sqlCopyCat);

	while($myrowCopyCat = mysql_fetch_row($resultCopyCat))
	{

		//Insert the category info
	
		$myrowCopyCat[0] = mysql_escape_string($myrowCopyCat[0]);
		$myrowCopyCat[1] = mysql_escape_string($myrowCopyCat[1]); 
		$myrowCopyCat[2] = mysql_escape_string($myrowCopyCat[2]);
		$myrowCopyCat[3] = mysql_escape_string($myrowCopyCat[3]);
		$myrowCopyCat[4] = mysql_escape_string($myrowCopyCat[4]);
		$myrowCopyCat[5] = mysql_escape_string($myrowCopyCat[5]);
		$myrowCopyCat[6] = mysql_escape_string($myrowCopyCat[6]);

		$sqlInsertCat = "insert into mgtcategory (name,objective,config,data,tools,compid,CATorder) values ('" . $myrowCopyCat[0] . "','" . $myrowCopyCat[1] . "','" . $myrowCopyCat[2] . "','" . $myrowCopyCat[3] . "','" . $myrowCopyCat[4] . "','" . $comID . "','" . $myrowCopyCat[6] . "')";

		$resultInsertCAT = mysql_query($sqlInsertCat);
		
		//grab the category id so that we can use it as the foreign key

		$catID =  mysql_insert_id(); //Grab the id of the category just entered

		//Select the test case data so that we can add it in later

		$sqlMoveCopy= "select title, summary, steps, exresult, keywords, version, author, TCorder from mgttestcase where catid='" . $myrowCopyCat[7] . "'";

		$resultMoveCopy = mysql_query($sqlMoveCopy);

		while($myrowMoveCopy = mysql_fetch_row($resultMoveCopy))
		{
			$myrowMoveCopy[0] = mysql_escape_string($myrowMoveCopy[0]);
			$myrowMoveCopy[1] = mysql_escape_string($myrowMoveCopy[1]); 
			$myrowMoveCopy[2] = mysql_escape_string($myrowMoveCopy[2]);
			$myrowMoveCopy[3] = mysql_escape_string($myrowMoveCopy[3]);
			$myrowMoveCopy[4] = mysql_escape_string($myrowMoveCopy[4]);
			$myrowMoveCopy[5] = mysql_escape_string($myrowMoveCopy[5]);
			$myrowMoveCopy[6] = mysql_escape_string($myrowMoveCopy[6]);
			$myrowMoveCopy[7] = mysql_escape_string($myrowMoveCopy[7]);
			$myrowMoveCopy[8] = mysql_escape_string($myrowMoveCopy[8]);
		
			//Insert the data from above into the test case table

			$sqlInsert = "insert into mgttestcase (title,summary,steps,exresult,keywords,catid,version,author,TCorder) values ('". $myrowMoveCopy[0]  . "','" . $myrowMoveCopy[1] . "','". $myrowMoveCopy[2] . "','" . $myrowMoveCopy[3] . "','" . $myrowMoveCopy[4] ."','". $catID ."','". $myrowMoveCopy[6] ."','". $myrowMoveCopy[7] . "','". $myrowMoveCopy[8] . "')";

			$resultInsert = mysql_query($sqlInsert);


		}//end the insertion of test cases

	}//end the insertion of categories

}elseif($_POST['submit'] == 'copyCAT')
{
	echo "Category has been copied";

	//Select the category info so that we can copy it

	$sqlCopyCat = "select name,objective,config,data,tools,compid,CATorder,id from mgtcategory where id='" . $_POST['id'] . "'";
	$resultCopyCat = mysql_query($sqlCopyCat);
	$myrowCopyCat = mysql_fetch_row($resultCopyCat);

	//Insert the category info

	$myrowCopyCat[0] = mysql_escape_string($myrowCopyCat[0]);
	$myrowCopyCat[1] = mysql_escape_string($myrowCopyCat[1]); 
	$myrowCopyCat[2] = mysql_escape_string($myrowCopyCat[2]);
	$myrowCopyCat[3] = mysql_escape_string($myrowCopyCat[3]);
	$myrowCopyCat[4] = mysql_escape_string($myrowCopyCat[4]);
	$myrowCopyCat[5] = mysql_escape_string($myrowCopyCat[5]);
	$myrowCopyCat[6] = mysql_escape_string($myrowCopyCat[6]);

	$sqlInsertCat = "insert into mgtcategory (name,objective,config,data,tools,compid,CATorder) values ('" . $myrowCopyCat[0] . "','" . $myrowCopyCat[1] . "','" . $myrowCopyCat[2] . "','" . $myrowCopyCat[3] . "','" . $myrowCopyCat[4] . "','" . $_POST['moveCopy'] . "','" . $myrowCopyCat[6] . "')";
	
	$resultInsertCAT = mysql_query($sqlInsertCat);
	
	//grab the category id so that we can use it as the foreign key

	$catID =  mysql_insert_id(); //Grab the id of the category just entered

	//Select the test case data so that we can add it in later

	$sqlMoveCopy= "select title, summary, steps, exresult, keywords, version, author, TCorder from mgttestcase where catid='" . $myrowCopyCat[7] . "'";

	$resultMoveCopy = mysql_query($sqlMoveCopy);

	while($myrowMoveCopy = mysql_fetch_row($resultMoveCopy))
	{
		$myrowMoveCopy[0] = mysql_escape_string($myrowMoveCopy[0]);
		$myrowMoveCopy[1] = mysql_escape_string($myrowMoveCopy[1]); 
		$myrowMoveCopy[2] = mysql_escape_string($myrowMoveCopy[2]);
		$myrowMoveCopy[3] = mysql_escape_string($myrowMoveCopy[3]);
		$myrowMoveCopy[4] = mysql_escape_string($myrowMoveCopy[4]);
		$myrowMoveCopy[5] = mysql_escape_string($myrowMoveCopy[5]);
		$myrowMoveCopy[6] = mysql_escape_string($myrowMoveCopy[6]);
		$myrowMoveCopy[7] = mysql_escape_string($myrowMoveCopy[7]);
		$myrowMoveCopy[8] = mysql_escape_string($myrowMoveCopy[8]);
		
		//Insert the data from above into the test case table

		$sqlInsert = "insert into mgttestcase (title,summary,steps,exresult,keywords,catid,version,author,TCorder) values ('". $myrowMoveCopy[0]  . "','" . $myrowMoveCopy[1] . "','". $myrowMoveCopy[2] . "','" . $myrowMoveCopy[3] . "','" . $myrowMoveCopy[4] ."','". $catID ."','". $myrowMoveCopy[6] ."','". $myrowMoveCopy[7] . "','". $myrowMoveCopy[8] . "')";

		$resultInsert = mysql_query($sqlInsert);


	}

}elseif($_POST['submit'] == 'copyTC')
{

	echo "Test Case has been copied";

	//Select the test case data so that we can add it in later

	$sqlMoveCopy= "select title, summary, steps, exresult, keywords, catid, version, author, TCorder from mgttestcase where id='" . $_POST['id'] . "'";

	$resultMoveCopy = mysql_query($sqlMoveCopy);

	$myrowMoveCopy = mysql_fetch_row($resultMoveCopy);

	//Insert the data from above into the test case table

	$myrowMoveCopy[0] = mysql_escape_string($myrowMoveCopy[0]);
	$myrowMoveCopy[1] = mysql_escape_string($myrowMoveCopy[1]); 
	$myrowMoveCopy[2] = mysql_escape_string($myrowMoveCopy[2]);
	$myrowMoveCopy[3] = mysql_escape_string($myrowMoveCopy[3]);
	$myrowMoveCopy[4] = mysql_escape_string($myrowMoveCopy[4]);
	$myrowMoveCopy[5] = mysql_escape_string($myrowMoveCopy[5]);
	$myrowMoveCopy[6] = mysql_escape_string($myrowMoveCopy[6]);
	$myrowMoveCopy[7] = mysql_escape_string($myrowMoveCopy[7]);
	$myrowMoveCopy[8] = mysql_escape_string($myrowMoveCopy[8]);

	$sqlInsert = "insert into mgttestcase (title,summary,steps,exresult,keywords,catid,version,author,TCorder) values ('". $myrowMoveCopy[0]  . "','" . $myrowMoveCopy[1] . "','". $myrowMoveCopy[2] . "','" . $myrowMoveCopy[3] . "','" . $myrowMoveCopy[4] ."','". $_POST['moveCopy'] ."','". $myrowMoveCopy[6] ."','". $myrowMoveCopy[7] . "','". $myrowMoveCopy[8] . "')";

	$resultInsert = mysql_query($sqlInsert);

}

	//Refresh the left frame
	
	$page = $basehref . "/manage/archiveLeft.php?product=" . $_SESSION['product'];


	refreshFrame($page); //call the function below to refresh the left frame
