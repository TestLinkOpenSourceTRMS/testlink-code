<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	execPrint.php 
 * @package 	  TestLink
 * @author		  Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2005-2016, TestLink community 
 * @link 		    http://www.testlink.org
 *
 */

require_once("../../config.inc.php");
require_once("../../cfg/reports.cfg.php"); 
require_once("print.inc.php"); 
require_once("common.php");

// This way can be called without _SESSION, this is useful for accessing
// from external systems without login
testlinkInitPage($db,false,true);
$templateCfg = templateConfiguration();

$tree_mgr = new tree($db);
$args = init_args();

$gui = new stdClass();
$gui->goback_url = !is_null($args->goback_url) ? $args->goback_url : ''; 
$gui->page_title = '';

if($args->deleteAttachmentID >0)
{
  deleteAttachment($db,$args->deleteAttachmentID);
}  

// Struture defined in printDocument.php	
$printingOptions = array('toc' => 0,'body' => 1,'summary' => 1, 'header' => 0,'headerNumbering' => 0,
	                     'passfail' => 0, 'author' => 1, 'notes' => 1, 'requirement' => 1, 'keyword' => 1, 
	                     'cfields' => 1, 'displayVersion' => 1, 'displayDates' => 1, 
	                     'docType' => SINGLE_TESTCASE, 'importance' => 1);

$level = 0;
$tplanID = 0;
$prefix = null;
$text2print = '';
$text2print .= renderHTMLHeader($gui->page_title,$_SESSION['basehref'],SINGLE_TESTCASE,
                                array('gui/javascript/testlink_library.js'));

$text2print .= renderExecutionForPrinting($db,$_SESSION['basehref'],$args->id,$_SESSION['currentUser']);

echo $text2print;

/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $args = new stdClass();
  $args->id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
  $args->deleteAttachmentID = isset($_REQUEST['deleteAttachmentID']) ? intval($_REQUEST['deleteAttachmentID']) : 0;

  $args->goback_url = null;
  return $args;
}