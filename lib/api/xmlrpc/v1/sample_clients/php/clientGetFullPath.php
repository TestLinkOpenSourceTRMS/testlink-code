<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetFullPath.php,v $
 *
 * @version $Revision: 1.1.6.1 $
 * @modified $Date: 2010/11/20 16:56:58 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal Revision
 * 20101120 - franciscom - test with nodeID -> Array
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getFullPath';
$unitTestDescription="Test - {$method}";
$idx=1;

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["nodeid"]=3312;
$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;
// --------------------------------------------------------------------------

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["nodeid"]=array(3312,3314,3316);
$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;

// --------------------------------------------------------------------------

// --------------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["nodeid"]=array(3333312,3314,3316);
$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args,$idx);
$idx++;
// --------------------------------------------------------------------------

// --------------------------------------------------------------------------
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