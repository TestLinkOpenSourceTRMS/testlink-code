<?php
////////////////////////////////////////////////////////////////////////////////
//File:     csvSplit.php
//Author:   Chad Rosen
//Purpose:  This file provides methods for splitting lines in a csv file.
////////////////////////////////////////////////////////////////////////////////

function csv_split($line,$delim=',',$removeQuotes=true) {
#$line: the csv line to be split
#$delim: the delimiter to split by
#$removeQuotes: if this is false, the quotation marks won't be removed from the fields
  $fields = array();
   $fldCount = 0;
   $inQuotes = false;
  for ($i = 0; $i < strlen($line); $i++) {
       if (!isset($fields[$fldCount])) $fields[$fldCount] = "";
      $tmp = substr($line,$i,strlen($delim));
       if ($tmp === $delim && !$inQuotes) {
           $fldCount++;
           $i += strlen($delim)-1;
       } else if ($fields[$fldCount] == "" && $line[$i] == '"' && !$inQuotes) {
          if (!$removeQuotes) $fields[$fldCount] .= $line[$i];
          $inQuotes = true;
       } else if ($line[$i] == '"') {
          if ($line[$i+1] == '"') {
               $i++;
              $fields[$fldCount] .= $line[$i];
           } else {
             if (!$removeQuotes) $fields[$fldCount] .= $line[$i];
              $inQuotes = false;
           }
       } else {
          $fields[$fldCount] .= $line[$i];
       }
   }
   return $fields;
}

?>
