<?php
/** 
*   TestLink Open Source Project - http://testlink.sourceforge.net/
* 
*   @version  $Id: gettestcasesummary.php,v 1.1.6.2 2010/12/15 21:48:13 mx-julian Exp $
*   @author   Francisco Mancardi
* 
*   Used on Add/Remove test case to test plan feature, to display summary via ExtJS tooltip
*
* @internal revisions
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

// take care of proper escaping when magic_quotes_gpc is enabled
$_REQUEST=strings_stripSlashes($_REQUEST);

$tcase_mgr = new testcase($db);
$tcase_id = isset($_REQUEST['tcase_id']) ? $_REQUEST['tcase_id']: null;
$tcversion_id = isset($_REQUEST['tcversion_id']) ? $_REQUEST['tcversion_id']: 0;
$info = '';
if( !is_null($tcase_id) )
{
  if($tcversion_id > 0 )
  { 
    $tcase = $tcase_mgr->get_by_id($tcase_id,$tcversion_id);
    if(!is_null($tcase))
    {
      $tcase = $tcase[0];
    } 
  }
  else
  {
    $tcase = $tcase_mgr->get_last_version_info($tcase_id);
  } 
  $info = $tcase['summary'];
    
  // <p> and </p> tag at the beginning and the end of summary cause visualization
  // errors -> remove them and add <br> to get a similar effect
  $info = str_replace("<p>","",$info);
  $info = str_replace("</p>","<br>",$info);
    
  if ($info == "") 
  {
    $info = lang_get("empty_tc_summary");
  }
  else
  {
    $info = '<b>' . lang_get('summary') . '</b><br>' . $info;  
  } 
}
echo $info;