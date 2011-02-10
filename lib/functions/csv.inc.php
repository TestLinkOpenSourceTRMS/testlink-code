<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * functions related to import/export using CSV format
 *
 * @package TestLink
 * @author TestLink Community
 * @copyright 2009, TestLink community 
 * @version CVS: $Id $
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/table.class.php?view=markup
 * @link http://www.teamst.org
 * @TODO havlatm: move both functions to appropriate object
 * 
 * @internal revisions
 *	20110206 - franciscom - BUGID 4210 - importCSVData() - added more debug info
 *										 return structure changed.
 *
 *	20100508 - franciscom - BUGID 3447 - importCSVData() 
 *
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
//                     if the number is not verified the line is discarded.
//
/** @uses requirements.inc.php */ 
function importCSVData($fileName,$fieldMappings, $options = null)
{
	$debugMe = false; //true;
	$my['options'] = array( 'delimiter' => ';', 'fieldQty' => 0, 'processHeader' => false);
    $my['options'] = array_merge($my['options'], (array)$options);

	$handle = fopen ($fileName,"r"); 
	$check_syntax = $my['options']['fieldQty'] > 0;
	$do_import = 1;
	
	// parsedCounter: count lines on file that has been parsed => exclude comment lines
	// syntaxError: array: index -> line number on file that has been skipped due to syntax
	//							check problems.
	// info: map with lines that can be processed by caller
	//
	$retVal = array('userFeedback' => array('parsedCounter' => 0, 'syntaxError' => array()), 
					'info' => null);
	
	if ($handle)
	{
		$lineNumber = 0;
		$idx = 0;
		$isHeaderLine = true;
		$keyMappings = $fieldMappings;
		
		$debugMsg = 'DEBUG::' . basename(__FILE__);
		
		if( $debugMe )
		{
			echo $debugMsg . ' OPTIONS: processHeader=' . 
				(($my['options']['processHeader']) ? 'true' : 'false' ) . '<br>';
		}
		
		while( $data = fgetcsv($handle, TL_IMPORT_ROW_MAX, $my['options']['delimiter']) )
		{ 
			$lineNumber++;

			// ignore line that start with comment char, leading blanks are ignored
			$firstChunk = trim($data[0]);
			$positionCheck = strpos($firstChunk,'#');
			$processLine = ($positionCheck === false || $positionCheck != 0);
            
			if( $debugMe )
			{
            	echo $debugMsg . ':: Line: ' . $lineNumber . '=>' . 
            		 (($processLine) ? 'OK to process' : 'Skipped') .'<br>'; 
        	}    	
        	
			if( $processLine )
			{
				$retVal['userFeedback']['parsedCounter']++;
				
				if( $isHeaderLine && $my['options']['processHeader'] )
				{
					// Get format information from first line, and rebuild with this 
					// information the keyMappings, using fieldMappings
					//
					$isHeaderLine = false;
					$keyMappings = null;
					foreach($fieldMapping as $k => $targetKey)
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
						$keyMappings[$t] = $dest;
					}
				}
	        	else
	        	{
					if( $check_syntax)
					{
						$fieldsQty = count($data);
						if( !($do_import = ($fieldsQty == $my['options']['fieldQty'])))
						{
							$msg = 'Field count:' . $fieldsQty . ' Required Field count: ' . 
								 	$my['options']['fieldQty']; 
							
							$retVal['userFeedback']['syntaxError'][$lineNumber] = $msg;
							
							if($debugMe)
							{
								echo $debugMsg . 'Syntax Check Failure - Line  ' . $lineNumber . 
									 $msg . ' - SKIPPED ' . '<br>';
							}		 	
						}	
					}
					
					if( $do_import )
					{ 
						foreach($keyMappings as $fieldPos => $fieldKey)
						{ 
							$retVal['info'][$idx][$fieldKey] = $data[$fieldPos];
						} 
						$idx++;
					}
	        	}
			}
		} // end while
	}
	return $retVal;
}
?>