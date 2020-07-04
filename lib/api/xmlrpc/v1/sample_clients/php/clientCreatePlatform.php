<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  clientCreatePlatform.php
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 *
 * [SERVER]/lib/api/xmlrpc/v1/sample_clients/php/clientCreatePlatform.php
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method = 'createPlatform';
$unitTestDescription="Test - {$method}";
$idx=1;

$args=array();
$args["devKey"]='dev01';
$args["testprojectname"]='GAGA';
$args["platformname"] = "Nian";
$args["notes"] = "Blue Notte XX";

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;