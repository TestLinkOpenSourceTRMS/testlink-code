<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * functions related to csv export
 *
 * @package 	TestLink
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: csv.inc.php,v 1.5 2009/06/25 19:37:53 havlat Exp $
 * @link 		http://www.teamst.org/
 *
 * @TODO havlatm: move both functions to appropriate object
 **/
 
/** @uses keywors.class.php, requirements.inc.php */ 
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
/** @uses requirements.inc.php */ 
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