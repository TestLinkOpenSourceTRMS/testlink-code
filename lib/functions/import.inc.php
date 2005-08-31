<?
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: import.inc.php,v 1.5 2005/08/31 15:49:26 franciscom Exp $
* 
* @author Martin Havlat
*
* Functions for Import TCs
*
* @ author: francisco mancardi - 20050810
* deprecated $_SESSION['product'] removed 
*
* 20050828 - scs - changes for importing tc to a specific category
*
* 20050831 - fm - reduce global coupling
*/
require_once('../../config.inc.php');
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
	$overview = "<table class='simple' width='80%'>";

	//command to open a csv for read
	$handle = fopen ($location,"r");

	$oldCom = null;
	$oldCat = null;
	//Need to grab the first row of data
	if (!$catIDForImport)
	{
		while ($data = fgetcsv ($handle, TL_IMPORT_ROW_MAX, ",")) {
			$arrayCom = $data[0];
			$arrayCat = $data[1];
			$arrayTC = $data[2];
	
			//Strips off quotation marks (") needed to import data correctly
			$arrayTC = preg_replace("/^['\"](.*?)['\"]$/","\\1", $arrayTC); // strip out possible quotes at beginning and end of string
			if(strcmp($arrayCom,$oldCom) != 0){ //Is the current value equal to the old value?
				$overview .= "<tr><td bgcolor='#CCCCCC' width='3'>COM:</td><td bgcolor='#CCCCCC'>" . $arrayCom . "</td></tr>"; //No
	
				if(strcmp($arrayCat,$oldCat) != 0){ //Is the current value equal to the old value?
					$overview .= "<tr><td bgcolor='#99CCFF' width='3'>CAT:</td><td  bgcolor='#99CCFF'>" . $arrayCat . "</td></tr>"; //No
					$overview .= "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . $arrayTC . "</td></tr>"; //display TC
				}
	
			} else {
	
				if (strcmp($arrayCat,$oldCat) == 0)	{
					$overview .= "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . $arrayTC . "</td></tr>";
				} else {
					$overview .= "<tr><td bgcolor='#99CCFF' width='3'>CAT:</td><td  bgcolor='#99CCFF'>" . $arrayCat . "</td></tr>"; //No
					$overview .= "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . $arrayTC . "</td></tr>"; //display TC
				}
			}
			$oldCom = $arrayCom;
			$oldCat = $arrayCat;
		}
	}
	else
	{
		while ($data = fgetcsv ($handle, TL_IMPORT_ROW_MAX, ","))
		{
			$arrayTC = $data[0];
			$overview .= "<tr><td bgcolor='#FFFFCC' width='3'>TC:</td><td bgcolor='#FFFFCC'>" . $arrayTC . "</td></tr>"; //display TC
		}
	}

	fclose ($handle);
	
	$overview .= "</table>";
	return $overview;
}
//20050828 - scs - added optional parameter for the offset in data where the keywords beging
function buildKeywordListAndInsertKeywords($data,$prodID,$slice = 6)
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
			
			getProductKeywords($prodID,$prodKeywords,$keyword);
			if (!sizeof($prodKeywords))
				addNewKeyword($prodID,$keywords[$i],null);
		}
		$keywords = implode(",",$keywords).",";
	}
	else
		$keywords = null;
		
	return $keywords;
}

/**
* Import TCs from CSV
*
* @param 
* @param $prodID
* @param $login_name
* @param int catIDForImport optional parameter for importing tc directly to a specific catID
*
* 20050831 - fm - reduce Global Coupling
*/
function exeTcImport($fileLocation,$prodID, $login_name, $catIDForImport = 0)
{
	//command to open a csv for read
	$handle = fopen($fileLocation, "r");

	//Need to grab the first row of data
	$data = fgetcsv ($handle, TL_IMPORT_ROW_MAX, ",");

	//Data taken from the csv
	//Removing the quotation marks around the stings
	//Harry: only stip out quotes if they are really there (M$ Excel CVS export compatibility)
	//Harry: replace any M$ Excel CVS single quotes "'" inside key with double "''"
	for($i = 0;$i < sizeof($data);$i++)
		$data[$i] = stripQuotes($data[$i]);
	
	$arrayCom = $data[0];
	$arrayCat = $data[1];
	$arrayTC = $data[2];
	$arraySummary = $data[3];
	$arrayTCSteps = $data[4];
	$arrayResults = $data[5];


	if (!$catIDForImport)
	{
		$keys = buildKeywordListAndInsertKeywords($data,$prodID);
		//Insert arrayCom into component where projID == projIDSubmit 
		$comID = insertProductComponent($prodID,$arrayCom,null,null,null,null,null);
		
		//Select comID from component where comName == arrayCom store as comID
		$catID = insertComponentCategory($comID,$arrayCat,null,null,null,null);
	
		$tcID = insertTestcase($catID,$arrayTC,$arraySummary,$arrayTCSteps,$arrayResults,$login_name,null,$keys);
	}
	else
	{
		$arrayTC = $data[0];
		$arraySummary = $data[1];
		$arrayTCSteps = $data[2];
		$arrayResults = $data[3];	

		$keys = buildKeywordListAndInsertKeywords($data,$prodID,4);
		$tcID = insertTestcase($catIDForImport,$arrayTC,$arraySummary,$arrayTCSteps,$arrayResults,$login_name,null,$keys);
	}	
	//Store all the old vales into a new array
	$oldCom = $arrayCom;
	$oldComNumber = $comID;
	$oldCat = $arrayCat;
	$oldCatNumber = $catID;

	//Next start the loop!!
	while ($data = fgetcsv ($handle, TL_IMPORT_ROW_MAX, ","))
	{
		for($i = 0;$i < sizeof($data);$i++)
			$data[$i] = stripQuotes($data[$i]);
	
		if ($catIDForImport)
		{
			$arrayTC = $data[0];
			$arraySummary = $data[1];
			$arrayTCSteps = $data[2];
			$arrayResults = $data[3];
			$keys = buildKeywordListAndInsertKeywords($data,$prodID,4);
			$tcID = insertTestcase($catIDForImport,$arrayTC,$arraySummary,$arrayTCSteps,$arrayResults,$login_name,null,$keys);
		}
		else
		{
			$arrayCom = $data[0];
			$arrayCat = $data[1];
			$arrayTC = $data[2];
			$arraySummary = $data[3];
			$arrayTCSteps = $data[4];
			$arrayResults = $data[5];
	
			$keys = buildKeywordListAndInsertKeywords($data,$prodID);
			
			if($arrayCom == $oldCom)
			{
				if($arrayCat == $oldCat)
					$tcID = insertTestcase($oldCatNumber,$arrayTC,$arraySummary,$arrayTCSteps,$arrayResults,$login_name,null,$keys);
				else
				{
					$catID = insertComponentCategory($oldComNumber,$arrayCat,null,null,null,null);
					$tcID = insertTestcase($catID,$arrayTC,$arraySummary,$arrayTCSteps,$arrayResults,$login_name,null,$keys);
				}
			}
			else
			{
				$comID = insertProductComponent($prodID,$arrayCom,null,null,null,null,null);
				$catID = insertComponentCategory($comID,$arrayCat,null,null,null,null);
				$tcID = insertTestcase($catID,$arrayTC,$arraySummary,$arrayTCSteps,$arrayResults,$login_name,null,$keys);
			}
	
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
	$data = preg_replace("/^['\"](.*?)['\"]$/","\\1", $data); // strip out possible quotes at beginning and end of string

	return $data;
}
?>