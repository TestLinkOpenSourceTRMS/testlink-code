<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  mainPage.php
 * 
 * Page has two functions: navigation and select Test Plan
 *
 * This file is the first page that the user sees when they log in.
 * Most of the code in it is html but there is some logic that displays
 * based upon the login. 
 * There is also some javascript that handles the form information.
 *
 **/

require_once('../../config.inc.php');
require_once('common.php');
require_once('mainPageCommon.php');

testlinkInitPage($db,TRUE);
$args = initArgs($db);

$log = "/tmp/trace.log";
if ($args->projectView) {
  file_put_contents($log, "\n in file: " . __FILE__,FILE_APPEND);
  require_once('project/projectView.php');
} else {
  main($db,$args);
}
