<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: csv.inc.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2005/12/29 21:03:09 $ by $Author: schlundus $
 *
 * functions related to csv export
 *
**/
function exportDataToCSV($data,$sourceKeys,$destKeys,$bWithHeader = 0,$delimiter = ';')
{
	$csvContent = '';
	$newLine = "\r\n";
		
	if ($bWithHeader)
	{
		$header = implode(";",$destKeys);	
		$csvContent .= $header . $newLine;
	}
	$len = sizeof($sourceKeys);
	for($i = 0;$i < sizeof($data);$i++)
	{
		$values = $data[$i];
		$line = '';
		for($k = 0;$k < $len;$k++)
		{
			$value = $values[$sourceKeys[$k]];
			if (strpos($value,$delimiter) !== false || strpos($value,"\n") !== false)
				$value = '"'.str_replace( '"','""',$value).'"';
			if ($k)
				$line .= $delimiter; 
			$line .= $value;
		}
		$line .= $newLine;
		$csvContent .= $line;
	}	
	return $csvContent;
}

function importCSVData($fileName,$destKeys,$delimiter = ';',$bWithHeader = false)
{
	$handle = fopen ($fileName,"r"); 
	$retData = null;
	if ($handle)
	{
		$i = 0;
		while($data = fgetcsv($handle, TL_IMPORT_ROW_MAX, $delimiter))
		{ 
			if (!$i && $bWithHeader)
				continue;
			$num = count($data); 
			for ($c = 0;$c < $num;$c++)
			{ 
				$retData[$i][$destKeys[$c]] = $data[$c];
			} 
			$i++;
		}
	}
	return $retData;
}
?>