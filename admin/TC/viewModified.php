<?php

////////////////////////////////////////////////////////////////////////////////
//File:     viewModified.php
//Author:   Ken Mamitsuka
//Purpose:  This file presents a list of all test cases that have been modified
//          at the product level since they were last imported into a project.
////////////////////////////////////////////////////////////////////////////////

require_once("../../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();
  doNavBar();

?>

<LINK REL='stylesheet' TYPE='text/css' HREF='kenny.css'>
<script language='javascript' src='functions/popTestCase.js'></script>
<script language='javascript'>
    function checkAll()
    {
		var ml = document.myform;
		var len = ml.elements.length;
		
		for (var i = 0; i < len; i++) {
			var e = ml.elements[i];
			if (e.type == "checkbox") {
				e.checked=true;
			}
		}
    }

    function uncheckAll()
    {
		var ml = document.myform;
		var len = ml.elements.length;
		
		for (var i = 0; i < len; i++) {
			var e = ml.elements[i];
			if (e.type == "checkbox") {
				e.checked=false;
			}
		}
    }
</script>

<?

$project = $_SESSION['project']; //store the project number

$sqlComp = "select id from component where projid = " . $project;

$resultComp = @mysql_query($sqlComp);

// set a counter to see if there are any test cases that are old
$tcCounter=0;

// walk through the project test cases
while($rowComp = mysql_fetch_array($resultComp)){

	$sqlCat = "select id from category where compid =" . $rowComp[0];

	$resultCat = @mysql_query($sqlCat);

	while($rowCat = mysql_fetch_array($resultCat)){

		$sqlTC = "select id from testcase where catid=" . $rowCat[0];

		$resultTC = @mysql_query($sqlTC);

		while($rowTC = mysql_fetch_array($resultTC)){

			// pass $tcCounter so the proper headers are added if there is at least one tc...
			if(displayTC($rowTC[0],$tcCounter)) {
				$tcCounter=1;
			}
		}
	}

}

if (!$tcCounter) {
	echo "<br><b>All test cases in this project are current.</b>\n";
} else {
	echo "</table>\n";
	echo "</form>";
}

function TCHeader()
{

	echo "<table width='100%' class=navtable >\n";
		
	echo "  <tr>\n";
	echo "    <td width='12.5%'><b>TCID</td>\n";
	echo "    <td width='12.5%'><b>Component</td>\n";
	echo "    <td width='12.5%'><b>Category</td>\n";
	echo "    <td width='12.5%'><b>Test Case</td>\n";
	echo "    <td width='12.5%'><b>Status</td>\n";
	echo "    <td width='12.5%'><b>Prod Ver</td>\n";
	echo "    <td width='12.5%'><b>Proj Ver</td>\n";
	echo "    <td width='12.5%'><b>Update</td>\n";
	echo "  </tr>\n";

}

function displayTC($id, $tcCounter)
{
	$diffVersion=0;

	$sql = "select category.name, component.name, testcase.id, title, summary,steps,exresult, active, version, mgttcid,TCorder from testcase,component,category where testcase.id='" . $id . "' and component.id=category.compid and category.id=testcase.catid order by TCorder";

	$result = @mysql_query($sql);

	while($row = mysql_fetch_array($result)){

	//Assign values from the test case query

		$id = $row['id'];
		$title = $row['title'];
		$summary = $row['summary'];
		$steps = $row['steps'];
		$exresult = $row['exresult'];
		$active = $row['active'];
		$version = $row['version'];
		$mgtID = $row['mgttcid'];
		$TCorder = $row['TCorder'];
		$compName = $row[1];
		$catName = $row[0];

		$sqlMgt = "select mgtcomponent.name, mgtcategory.name,title,summary,steps,exresult,version,TCorder from mgttestcase,mgtcomponent,mgtcategory where mgttestcase.id='" . $mgtID . "' and mgtcomponent.id=mgtcategory.compid and mgtcategory.id=mgttestcase.catid";

		$mgtResult = @mysql_query($sqlMgt);

		$mgtRow = mysql_fetch_array($mgtResult);

		$mgtTitle = $mgtRow['title'];
		$mgtSummary = $mgtRow['summary'];
		$mgtSteps = $mgtRow['steps'];
		$mgtExresult = $mgtRow['exresult'];
		$mgtVersion = $mgtRow['version'];
		$mgtTCorder = $mgtRow['TCorder'];
		$mgtCompName = $row[1];
		$mgtCatName = $row[0];
		
		if ($version != $mgtVersion) {
			// add headers if it's the first
			if (!$tcCounter) {
				echo "<Form name='myform' Method='POST' ACTION='admin/TC/updateModified.php?data=" . $_GET['data'] . "'>";
				echo "<table width=100%><tr>";
				echo "  <td align=left><input type='submit' name='updateSelected' Value='Update Checked Test Cases'></td>";
				echo "  <td align=right>";
				echo "    <input type='button' name='CheckAll' value='Check All' onclick=checkAll();>";
				echo "    <input type='button' name='UncheckAll' value='Uncheck All' onclick=uncheckAll();>";
				echo "  </td></tr></table><br>";
				TCHeader();
			}

			echo "  <tr>\n";
			echo "    <td class=tctable onclick=javascript:open_tc('./viewTestCases.php?id=" . $id . "');><b><font color=blue>" . $mgtID . "</font></td>\n";
			echo "    <td class=tctable><b>" . $compName . "</td>\n";
			echo "    <td class=tctable><b>" . $catName . "</td>\n";
			echo "    <td class=tctable><b>" . $title . "</td>\n";
			
			if($mgtVersion == "") //check to see if the test case was deleted and not old
			{
				//if it is deleted set status to deleted and the version to ---
				$status = "deleted";
				$mgtVersion = "---";
			
			}else
			{
				$status = "updated";
				
			}
			
			echo "    <td class=tctable><b>" . $status . "</td>\n";
			echo "    <td class=tctable><b>" . $mgtVersion . "</td>\n";
			echo "    <td class=tctable><b>" . $version . "</td>\n";
			echo "    <td class=tctable>\n";
			echo "      <input type=hidden name=tcid" . $id . " value=" . $id . ">\n";
			echo "      <input type=checkbox name=update" . $id . " value=yes" . $id . ">\n";
			echo "      <input type=hidden name=break" . $id . " value=break>\n";
			echo "    </td>\n";
			echo "  </tr>\n";

			$diffVersion = 1;
		}
	}//end while

	return $diffVersion;
}


?>