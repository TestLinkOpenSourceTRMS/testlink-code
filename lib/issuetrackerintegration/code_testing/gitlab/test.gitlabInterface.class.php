<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.gitlabrestInterface.class.php
 * @author	jlguardi
 *
 * @internal revisions
 *
**/
require_once('../../../config.inc.php');
require_once('common.php');

/**
 * To test this module: 
 *  - Create an account on a gitlab server: i.e: https://gitlab.com
 *  - Get your "Private Token" or create an "Access Token" (with API grants) from your profile/settings
 *  - Create a project (private/public or protected it doesn't matter) and take its "Project ID" from project/settings
 *  - Setup your apikey (token), uribase (server url), projectidentifier (Project ID) in cfg
*/
$cfg =  "<issuetracker>\n" .
        "<apikey>REPLACE_ME</apikey>\n".
        "<uribase>https://gitlab.com</uribase>\n".
        "<projectidentifier>REPLACE_ME</projectidentifier>\n".
        "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - gitlabrestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new gitlabrestInterface(18,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());

{ 
  $today = date("Y-m-d H:i:s"); 
  $issue = array('summary' => 'Issue Via API' . $today,'description' => 'Some text');
  $resp = $its->addIssue($issue['summary'],$issue['description']);
  echo '<br>' . __FILE__ . '<br>';
  echo '<pre>';
  var_dump($resp);
  echo '</pre>';
}

if( $its->isConnected() )
{
  $resp = $its->getIssue(1);
  
  echo '<br>' . __FILE__ . '<br>';
  echo '<pre>';
  var_dump($resp);
  echo '</pre>';
  
}

?>
