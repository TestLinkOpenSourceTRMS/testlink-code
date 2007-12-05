<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: csv.inc.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2007/12/05 21:25:14 $ by $Author: schlundus $
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

// 20061014 - franciscom
// added [$num_fields] number of fields a line must have to be valid
//                     if the number is not verified the line is discarded silently.
//
function importCSVData($fileName,$destKeys,$delimiter = ';',$num_fields=0,
                       $bWithHeader = false,$bSkipHeader = true)
{
  
	$handle = fopen ($fileName,"r"); 
	$retData = null;
	$check_syntax=$num_fields > 0;
	$do_import=1;
	
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
	
	    // ---------------------------------------------		
	    if( $check_syntax)
	    {
	      $do_import=(count($data)==$num_fields );
	    }
      if( $do_import )
      { 
			  foreach($idx as $c => $key)
			  { 
				  $retData[$i][$idx[$c]] = $data[$c];
			  } 
			  $i++;
			}
			// ---------------------------------------------
		}
	}
	return $retData;
}

?>