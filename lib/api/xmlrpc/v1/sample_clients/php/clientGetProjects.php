<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetProjects.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2010/07/10 15:18:04 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getProjects';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
$answer = runTest($client,$method,$args);
new dBug($answer);

$items_qty = count($answer);
foreach($answer as $item)
{
	if( isset($item['name']) )
	{
		echo 'name:' . htmlentities($item['name']) . '<br>';	
		echo 'name:' . htmlentities(utf8_decode($item['name'])) . '<br>';	
	}
}
?>