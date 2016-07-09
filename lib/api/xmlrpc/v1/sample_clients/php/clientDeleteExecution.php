<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientDeleteExecution.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2010/07/11 17:56:33 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method="deleteExecution";

$unitTestDescription="Test - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["executionid"]=-19;

$debug=true;
$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);
// ---------------------------------------------------------------------------------------

?>