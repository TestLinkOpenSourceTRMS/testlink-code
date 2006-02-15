<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: import.inc.php,v 1.15 2006/02/15 08:49:19 franciscom Exp $
* 
* @author Martin Havlat
*
* Functions for Import TCs
*
* 20050828 - scs - changes for importing tc to a specific category
* 20050831 - fm - reduce global coupling
* 20051004 - fm - interface changes
* 20051104 - scs - import didnt work, fixed
* 20060108 - fm  - refactoring
*
*/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../testcases/archive.inc.php");
require_once("../keywords/keywords.inc.php");

/**
* Create table to show imported data at first
*
* @param string $location
* @param int catIDForImport optional parameter for importing tc directly to a specific catID
*/
function showTcImport($location,$catIDForImport = 0)
{
	$overview = "<table class=\"simple\" width=\"80%\">";

	$handle = fopen ($location,"r");
	if ($handle)
	{
		$oldCom = null;
		$oldCat = null;
		if (!$catIDForImport)
		{
			while ($data = fgetcsv($handle, TL_IMPORT_ROW_MAX, ","))
			{
				$arrayCom = $data[0];
				$arrayCat = $data[1];
				$arrayTC = $data[2];
		
				$arrayTC = stripQuotes($arrayTC); 
				if(strcmp($arrayCom,$oldCom))
				{ 	
					$overview .= "<tr><td bgcolor='#CCCCCC' width='3'>COM:</td><td bgcolor='#CCCCCC'>" . 
					             $arrayCom . "</td></tr>";
		
					if(strcmp($arrayCat,$oldCat))
					{
						$overview .= "<tr><td bgcolor='#99CCFF' width='3'>CAT:</td><td  bgcolor='#99CCFF'>" . 
						             $arrayCat . "</td></tr>";
						$overview .= "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . 
						             $arrayTC . "</td></tr>";
					}
				}
				else
				{
					if (!strcmp($arrayCat,$oldCat))
					{
						$overview .= "<tr><td bgcolor='#99CCFF' width='3'>CAT:</td><td  bgcolor='#99CCFF'>" . 
						             $arrayCat . "</td></tr>";
					}
					$overview .= "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . 
					             $arrayTC . "</td></tr>"; 
				}
				$oldCom = $arrayCom;
				$oldCat = $arrayCat;
			}
		}
		else
		{
			while ($data = fgetcsv($handle, TL_IMPORT_ROW_MAX, ","))
			{
				$arrayTC = $data[0];
				$overview .= "<tr><td bgcolor=\"#FFFFCC\" width=\"3\">TC:</td><td bgcolor=\"#FFFFCC\">" . 
				             $arrayTC . "</td></tr>";
			}
		}
		fclose ($handle);
	}
		
	$overview .= "</table>";
	return $overview;
}


//20050828 - scs - added optional parameter for the offset in data where the keywords beging
function buildKeywordListAndInsertKeywords(&$db,$data,$prodID,$slice = 6)
{
	//Grabbing the Key information from the excel sheets
	$keywords = null;
	$keywords = array_slice($data,$slice);

	//Need to reinitialize the keys variable
	if (sizeof($keywords))
	{
		
		for($i = 0;$i < sizeof($keywords);$i++)
		{
			$prodKeywords = null;
			$keyword = $keywords[$i];
			$keyword = str_replace(array('"',','),array("",""),$keyword);
			$keywords[$i] = $keyword;
			
			// 20051004 - fm - interface changes
			$prodKeywords=getProductKeywords($db,$prodID,$keyword);
			if (!sizeof($prodKeywords))
			{
				addNewKeyword($db,$prodID,$keywords[$i],null);
			}	
		}
		$keywords = implode(",",$keywords).",";
	}
	else
	{
		$keywords = null;
	}	
	return $keywords;
}

/**
* Import TCs from CSV
*
* @param 
* @param $prodID
* @param $user_id
* @param int catIDForImport optional parameter for importing tc directly to a specific catID
*
* 20050831 - fm - reduce Global Coupling
*/
function exeTcImport(&$db,$fileLocation,$prodID, $user_id, $catIDForImport = 0)
{
	//command to open a csv for read
	$handle = fopen($fileLocation, "r");

	//Need to grab the first row of data
	$data = fgetcsv ($handle, TL_IMPORT_ROW_MAX, ",");

	//Data taken from the csv
	//Removing the quotation marks around the stings
	//only stip out quotes if they are really there (M$ Excel CVS export compatibility)
	//replace any M$ Excel CVS single quotes "'" inside key with double "''"
  $qta_fields=sizeof($data);
	for($i = 0; $i < $qta_fields ;$i++)
	{
		$data[$i] = stripQuotes($data[$i]);
	}
	
	$arrayCom = $data[0];
	$arrayCat = $data[1];
	$arrayTC = $data[2];
	$arraySummary = $data[3];
	$arrayTCSteps = $data[4];
	$arrayResults = $data[5];

	if ($catIDForImport)
	{
		$arrayTC      = $data[0];
		$arraySummary = $data[1];
		$arrayTCSteps = $data[2];
		$arrayResults = $data[3];	
    $key_index=4;
	  $catID = $catIDForImport;
	}	
	else
	{
		$key_index=6;

		//Insert arrayCom into component where projID == projIDSubmit 
		$ret = insertProductComponent($db,$prodID,$arrayCom,null,null,null,null,null);
		$comID = $ret['id'];
		
		//Select comID from component where comName == arrayCom store as comID
		$catID = insertComponentCategory($db,$comID,$arrayCat,null,null,null,null);
	}
	$keys = buildKeywordListAndInsertKeywords($db,$data,$prodID,$key_index);
	$tcID = insertTestcase($db,$catID,$arrayTC,$arraySummary,
	                       $arrayTCSteps,$arrayResults,$user_id,null,$keys);
	
	
	//Store all the old vales into a new array
	$oldCom = $arrayCom;
	$oldComNumber = $comID;
	$oldCat = $arrayCat;
	$oldCatNumber = $catID;

	//Next start the loop!!
	while ($data = fgetcsv ($handle, TL_IMPORT_ROW_MAX, ","))
	{
  	$qta_fields=sizeof($data);
		for($i = 0;$i < $qta_fields ;$i++)
		{
			$data[$i] = stripQuotes($data[$i]);
		}
	  
		if ($catIDForImport)
		{
			$arrayTC = $data[0];
			$arraySummary = $data[1];
			$arrayTCSteps = $data[2];
			$arrayResults = $data[3];
			$keys = buildKeywordListAndInsertKeywords($db,$data,$prodID,4);
			$catID=$catIDForImport;
			$keys_index=4;
		}
		else
		{
			$arrayCom = $data[0];
			$arrayCat = $data[1];
			$arrayTC = $data[2];
			$arraySummary = $data[3];
			$arrayTCSteps = $data[4];
			$arrayResults = $data[5];
			$keys_index=6;
			
			if($arrayCom == $oldCom)
			{
				$catID = $oldCatNumber;
				if($arrayCat != $oldCat)
				{
					$catID = insertComponentCategory($db,$oldComNumber,$arrayCat,null,null,null,null);
				} 
			}
			else
			{
				// 20051001 - am - changes in insertProductComponent() regarind to bug 150
				$ret = insertProductComponent($db,$prodID,$arrayCom,null,null,null,null,null);
				$comID = $ret['id'];
				$catID = insertComponentCategory($db,$comID,$arrayCat,null,null,null,null);
			}
			$keys = buildKeywordListAndInsertKeywords($db,$data,$prodID,$keys_index);
			$tcID = insertTestcase($db,$catID,$arrayTC,$arraySummary,
			                       $arrayTCSteps,$arrayResults,$user_id,null,$keys);
	
			$oldCom = $arrayCom;
			$oldComNumber = $comID;
			$oldCat = $arrayCat;
			$oldCatNumber = $catID;
		}
	}
	
	//Close the CSV
	fclose ($handle);
	return "Data Imported";
}

function stripQuotes($data)
{
	// strip out possible quotes at beginning and end of string
	$data = preg_replace("/^['\"](.*?)['\"]$/","\\1", $data); 
	return $data;
}
?>