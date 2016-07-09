<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	clientGetExecCountersByBuild.php
 *
 * @Author: francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';

show_api_db_sample_msg();
$method='getExecCountersByBuild';

echo '<h2>Simple client to test method:' . $method . '()</h2>';


$args=array();
$args["devKey"]=DEV_KEY;
$args["testplanid"]=337058;
$client = new IXR_Client($server_url);
$client->debug=true;
runTest($client,$method,$args);

?>