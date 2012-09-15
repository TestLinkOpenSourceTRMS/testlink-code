<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.fogbugzrestInterface.class.php
 * @author		Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

$cfg =  "<issuetracker>\n" .
		"<username>francisco.mancardi@gmail.com</username>\n" .
		"<password>testlink.fogbugz</password>\n" .
		"<uribase>https://testlink.fogbugz.com/</uribase>\n" .
		"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - fogbugzrestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new fogbugzrestInterface(18,$cfg);
var_dump($its);

$xx = $its->getCfg();
var_dump($xx);
var_dump('<xmp><pre>' . $xx->asXML() . '</pre></xmp>');

/*
$xx->uriview = $xx->uribase . 'ffffff';
var_dump($xx);
var_dump('<xmp><pre>' . $xx->asXML() . '</pre></xmp>');
*/

?>