<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.php
 *
 *
**/
require_once('../../../../config.inc.php');
require_once('common.php');

/**
 * To test this module: 
 *  - Create an account on a gitlab server: i.e: https://gitlab.com
 *  - Get your "Private Token" or create an "Access Token" 
 *    (with API grants) from your profile/settings
 *  - Create a project (private/public or protected it doesn't matter) 
 *    and take its "Project ID" from project/settings
 *  - Setup your apikey (token), uribase (server url), 
 *    projectidentifier (Project ID) in cfg
*/

/* gitlab.com - francisco */
/*

$gitlabUrl = 'https://gitlab.com/';
$token ='icj6Uj4KXdpzXffUbNhn';
$projectID = 12904891;

$cfg =  "<codetracker>\n" .
        "<apikey>{$token}</apikey>\n".
        "<uribase>{$gitlabUrl}</uribase>\n".
        "<uriapi>{$gitlabUrl}api/4.0/</uriapi>\n".
        "<projectidentifier>{$projectID}</projectidentifier>\n".
        "<testscriptpath>code</testscriptpath>\n".
        "</codetracker>\n";
*/
/* git.tesisquare.com - francisco 
*/
$gitlabUrl = 'https://git.tesisquare.com/';
$token ='ToG9Q1UaYdZDWqeG3Uyw';
$projectID = 4641;
$path = 'test/product-tree/PT.01.SCM.Sourcing/';
$path = 'test/product-tree/PT.02.SCM.Procurement/';
$path = 'test/product-tree/';

$cfg =  "<codetracker>\n" .
        "<apikey>{$token}</apikey>\n".
        "<uribase>{$gitlabUrl}</uribase>\n".
        "<uriapi>{$gitlabUrl}api/4.0/</uriapi>\n".
        "<projectidentifier>{$projectID}</projectidentifier>\n".
        "<testscriptpath>{$path}</testscriptpath>\n".
        "</codetracker>\n";


echo '<hr><br>';
echo "<b>Testing gitlab rest  </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$system = new gitlabrestInterface(18,$cfg,'pandora');
$cfgObj = $system->getCfg();

$params = ['id' => $cfgObj->projectidentifier, 
           'path' => $cfgObj->testscriptpath];
$client = $system->getAPIClient();



echo '<pre> - getRepoFilesTreeFlat<br>';

// First Steps First level
$params['recursive'] = false;
$params['itemType'] = 'tree';
$main = $client->getRepoFilesTreeFlat($params);

var_dump($main);

$all = [];
foreach ($main as $path) {
  $params['itemType'] = 'blob';
  $params['recursive'] = true;
  $params['path'] = $path;

  $all[$path] = $client->getRepoFilesTreeFlat($params);
}

var_dump($all);

echo '</pre>';
