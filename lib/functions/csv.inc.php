<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: csv.inc.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/04/07 20:15:26 $ by $Author: schlundus $
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

function importCSVData($fileName,$destKeys,$delimiter = ';',$bWithHeader = false,$bSkipHeader = true)
{
	$handle = fopen ($fileName,"r"); 
	$retData = null;
	if ($handle)
	{
		$i = 0;
		$idx = $destKeys;
		while($data = fgetcsv($handle, TL_IMPORT_ROW_MAX, $delimiter))
		{ 
			if (!$i)
			{
				if ($bWithHeader && !$bSkipHeader)
				{
					$idx = null;
					foreach($destKeys as $k => $targetKey)
					{
						if (is_int($k))
						{
							$needle = $targetKey;
							$dest = $needle;
						}
						else
						{
							$needle = $k;
							$dest = $targetKey;
						}
						$t = array_search($needle, $data);	
						$idx[$t] = $dest;
					}
					$i++;
					continue;
				}
			}
			foreach($idx as $c => $key)
			{ 
				$retData[$i][$idx[$c]] = $data[$c];
			} 
			$i++;
		}
	}
	return $retData;
}
?>