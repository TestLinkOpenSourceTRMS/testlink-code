<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.mantissoapInterface.class.php
 * @author		  Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

$cfg = "<issuetracker>\n" .
  		 "<username>u0113</username>\n" .
  		 "<password>tesi</password>\n" .
  		 "<uriwsdl>http://localhost:8080/development/closet/mantisbt-1.2.11/api/soap/mantisconnect.php?wsdl</uriwsdl>\n" .
  		 "<project>TestLinkACCESS</project>\n" .
  		 "<category>KATA</category>\n" .
  		 "</issuetracker>\n";

//  		 "<project>NET MON COCACOLA</project>\n" .
// NET MON COCACOLA
//  		 "<project>TestLinkACCESS</project>\n" .
//  		 "<category>BUGFROMSOAP</category>\n" .

echo '<hr><br>';
echo "<b>Testing  BST Integration - mantis SOAP </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new mantissoapInterface(1,$cfg);
var_dump($its);

if( $its->isConnected() )
{
	echo '<b>Connected !</br></b>';
  $zx = $its->addIssue('subject','description');
  var_dump($zx);
}
?>