<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientUploadTestCaseAttachment.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2010/09/18 14:46:38 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='uploadTestCaseAttachment';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$attach = file_get_contents('./other/marilyn-monroe.jpg');
$attach = file_get_contents('./other/README');
$encoded = base64_encode($attach);
$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseid"]=4;
$args["title"] = 'a README TXT FILE';
$args["filename"] = 'README';
$args["content"] = $encoded;
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);
// ---------------------------------------------------------------------------------

$test_num=2;
$unitTestDescription="Test {$test_num} - {$method}";
$attach = file_get_contents('./other/marilyn-monroe.jpg');
$encoded = base64_encode($attach);
$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseid"]=4;
$args["title"] = 'Marilyn Monroe';
$args["filename"] = 'marilyn-monroe.jpg';
$args["content"] = $encoded;
$additionalInfo='';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;

runTest($client,$method,$args);

 	
?>