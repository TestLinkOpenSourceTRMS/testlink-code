<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.createIssue.jiraOnDeman.jirarestInterface.class.php
 * @author		Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

// http://testlink.atlassian.net/rest/api/latest/user/search/?username=admin
$username = 'testlink.forum';
$password = 'forum';
$uribase = 'https://testlink.atlassian.net/';
$uriapi = 'https://testlink.atlassian.net/rest/api/latest/';
$projectkey = 'ZOFF';


$cfg =  "<issuetracker>\n" .
        "<username>{$username}</username>\n" .
        "<password>{$password}</password>\n" .
        "<uribase>{$uribase}</uribase>\n" .
        "<uriapi>{$uriapi}</uriapi>\n" .
        "<projectkey>{$projectkey}</projectkey>\n" .
        "<issuetype>1</issuetype>\n" .
        "</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - jirarestInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";
echo '<hr><br><br>';
echo 'Creating INTERFACE<br>';
$its = new jirarestInterface(7,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());

if( $its->isConnected() )
{

  $summary = 'Will try to create via REST RAW';
  $description = 'I WAS ABLE to create via REST RAW!!!';
  $issue = array('fields' =>
                 array('project' => array('key' => (string)$projectkey),
                       'summary' => $summary,
                       'description' => $description,
                       'issuetype' => array( 'id' => 1)
                      )
                );

  $zorro = $its->getAPIClient()->createIssue($issue);
  echo 'Test - Create an ISSUE VIA REST RAW<br>'; 
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';

  // ====================================================================
  $summary = 'Will try to create via REST TestLink Interface';
  $description = 'I WAS ABLE to create via REST TestLink Interface ****';
  $zorro = $its->addIssue($summary,$description);
  echo 'Test - Create an ISSUE VIA REST TestLink Interface<br>'; 
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';



 
}
