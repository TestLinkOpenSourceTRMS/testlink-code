<?

////////////////////////////////////////////////////////////////////////////////
//File:     keyword.php
//Author:   Chad Rosen
//Purpose:  This page handles the search based on keyword
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");

  session_start();
  doDBConnect();
  doHeader();

	require_once("../functions/csvsplit.php");

	$sql = "select keywords from project, component, category, testcase where project.id = 14 and project.id = component.projid and component.id = category.compid and category.id = testcase.catid";

	$result = mysql_query($sql);

	//$result3 = "none";

	while ($myrow = mysql_fetch_row($result)) 
		{

			$keyArray = csv_split($myrow[0]);

			$result2 = array_merge ($result2, $keyArray);


		}//END WHILE


		//


		//print_r($result2);

		$result3 = array_unique ($result2);

		//print_r($result3);

		$i=0;

		foreach ($result3 as $key)
	    {
	
			$result4[$i] = $key;
			$i++;

		}

		//print_r($result4);
		
		echo "<select name=bob>";



		for ($i = 0; $i < count($result4); $i++)
		{

			echo "<option>" . $result4[$i] . "</option>";


		}

		echo "</select>";

?>
