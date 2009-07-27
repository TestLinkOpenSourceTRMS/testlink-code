<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetFullPath.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2009/07/27 07:23:19 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getFullPath';
$unitTestDescription="Test - {$method}";
$idx=1;

$args=array();
$args["devKey"]=DEV_KEY;
$args["nodeid"]='A';
$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["nodeid"]=-1;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["nodeid"]=1;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["nodeid"]=419;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;

?>