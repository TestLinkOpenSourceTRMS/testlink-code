<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* Type: function
* Name: date_diff
* Version: 2.0
* Date: June 22, 2008
* Author: Matt DeKok
* Purpose: factor difference between two dates in days, weeks,
*          or years
* Input: date1 = "mm/dd/yyyy" or "yyyy/mm/dd" or "yyyy-mm-dd"
*        date2 = "mm/dd/yyyy" or "yyyy/mm/dd" or "yyyy-mm-dd" or $smarty.now
*        assign = name of variable to assign difference to
*        interval = "days" (default), "weeks", "years"
* Examples: {date_diff date1="5/12/2003" date2=$smarty.now interval="weeks"}
*           {date_diff date1="5/12/2003" date2="5/10/2008" assign="diff"}{$diff}
* -------------------------------------------------------------
*/
function smarty_function_date_diff($params, &$smarty) {
   $date1 = mktime(0,0,0,1,1,2000);
   $date2 = mktime(0,0,0,date("m"),date("d"),date("Y"));
   $assign = null;
   $interval = "days";
   
   extract($params);

   $i = 1/60/60/24;
   if($interval == "weeks") {
      $i = $i/7;
   } elseif($interval == "years") {
      $i = $i/365.25;
   }
   
   $date1 = ((is_string($date1))?strtotime($date1):$date1);
   $date2 = ((is_string($date2))?strtotime($date2):$date2);
   
   if($assign != null) {
      $smarty->assign($assign,floor(($date2 - $date1)*$i));
   } else {
      return floor(($date2 - $date1)*$i);
   }
}
?> 