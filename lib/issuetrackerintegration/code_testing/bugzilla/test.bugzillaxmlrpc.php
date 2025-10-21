<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 */
require_once '../../../../config.inc.php';
require_once 'common.php';

$cfg = "<issuetracker>\n" . "<username>testlink.helpme@gmail.com</username>\n" .
    "<password>testlink.helpme</password>\n" .
    "<uribase>http://bugzilla.mozilla.org/</uribase>\n" . "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - bugzillaxmlrpcInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new bugzillaxmlrpcInterface(185, $cfg);
echo '<br>Does issue 281579 exist? ' .
    ($its->checkBugIDExistence(281579) ? 'YES!!!' : 'Oh No!!!');
echo '<br>Does issue 999999 exist? ' .
    ($its->checkBugIDExistence(999999) ? 'YES!!!' : 'Oh No!!!');

?>
