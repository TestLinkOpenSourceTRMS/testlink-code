<?php
 /**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientGetTestCaseCustomFieldDesignValue.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2010/10/23 07:57:51 $ by $Author: franciscom $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 * @internal revisions
 * 20101023 - franciscom - added version number on call.
 * 			
 *
 */
 
require_once 'util.php';
require_once 'sample.inc.php';
show_api_db_sample_msg();

$method='getTestCaseCustomFieldDesignValue';
$test_num=1;
$unitTestDescription="Test {$test_num} - {$method}";

$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseexternalid"]='QAZ-1';
$args["testprojectid"]=455;
$args["customfieldname"]='M LIST';
$args["details"]='simple';
$args["version"]=3;

$additionalInfo = ' -> Ask for NON EXISTENT VERSION';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// -----------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseexternalid"]='QAZ-1';
$args["testprojectid"]=455;
$args["customfieldname"]='M LIST';
$args["details"]='simple';
$args["version"]= 2;

$additionalInfo = ' -> Must be GOOD call';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

// -----------------------------------------------------------------------
$args=array();
$args["devKey"]=DEV_KEY;
$args["testcaseexternalid"]='QAZ-1';
$args["testprojectid"]=455;
$args["customfieldname"]='M LIST';
$args["details"]='simple';
$args["version"]= 1;

$additionalInfo = ' -> Another GOOD call  but for a DIFFERENT Version ';

$debug=true;
echo $unitTestDescription;
echo $additionalInfo;

$client = new IXR_Client($server_url);
$client->debug=$debug;
runTest($client,$method,$args);

?>