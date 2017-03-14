<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @Author		francisco.mancardi@gmail.com
 *
 */
$method='deleteTestProject';
$unitTestDescription="";
$devKey = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : $tlDevKey;

$args = array();
$args["devKey"] = $devKey;
$args["prefix"] = $tlTestCasePrefix;
$additionalInfo='';


echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug = $tlDebug;

$tlIdx++;
$answer = runTest($client,$method,$args,$tlIdx);