<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.kaitenrestInterface.class.php
 * @author	vinoron
 *
**/
require_once('../../../config.inc.php');
require_once('common.php');

/**
 * To test this module: 
 *  - Create an account on a https://kaiten.io
 *  - Create a Board, and get the ID from the context menu
 *  - Setup your apikey, uribase (https://yourcompany.kaiten.io), 
 *                       boardId (Board ID) in cfg
 */

$cfg =  "<issuetracker>\n" .
        "<apikey>YOR APIKEY HERE</apikey>\n".
        "<uribase>https://yourcompany.kaiten.io</uribase>\n".
        "<boardid>REPLACE_ME</boardid>\n".
        "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - kaitenrestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new kaitenrestInterface(23,$cfg,'KAITEN');

echo 'Connection OK?<br>';
var_dump($its->isConnected());

$issueId = null;
if( $its->isConnected() ) { 
  $today = date("Y-m-d H:i:s"); 
  $issue = array('summary' => 'New issue card Via API' . $today,'description' => 'Some text');
  $resp = $its->addIssue($issue['summary'],$issue['description']);
  echo '<br>' . __FILE__ . '<br>';
  echo '<pre>';
  var_dump($resp);
  echo '</pre>';
  $issueId = isset($resp['id']) ? $resp['id'] : null;
}

if( $its->isConnected() )
{
  $resp = $its->getIssue($issueId);
  
  echo '<br>' . __FILE__ . '<br>';
  echo '<pre>';
  var_dump($resp);
  echo '</pre>';
  
}

?>
