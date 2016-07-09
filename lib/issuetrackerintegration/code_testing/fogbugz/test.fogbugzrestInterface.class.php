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
		    "<password>qazwsxedc</password>\n" .
		    "<uribase>https://fman.fogbugz.com/</uribase>\n" .
		    "<project>TestLink Testing</project>\n" .
		    "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - fogbugzrestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new fogbugzrestInterface(18,$cfg);
echo '<br>' . __FILE__ . '<br>';
echo '<br> Dumping INTERFACE OBJECT <br>';
echo '<pre>';
var_dump($its);
echo '</pre>';

$xx = $its->getCfg();
//var_dump($xx);
// var_dump('<xmp><pre>' . $xx->asXML() . '</pre></xmp>');

if( $its->isConnected() )
{
  $xx = $its->getIssue(3);
  
  echo '<br>' . __FILE__ . '<br>';
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';
  
  /*
  $xx = $its->addIssue('ISSUE FROM PHP', 'Que miras bolu');
  echo '<br>' . __FILE__ . '<br>';
  echo '<pre>';
  var_dump($xx);
  echo '</pre>';
  */
  
}

/*
$xx->uriview = $xx->uribase . 'ffffff';
var_dump($xx);
var_dump('<xmp><pre>' . $xx->asXML() . '</pre></xmp>');
*/

?>