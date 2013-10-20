<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.youtrackrestInterface.class.php
 * @author		Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../config.inc.php');
require_once('common.php');


$itfName = 'youtrackrestInterface';
$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

/*
$cfg = "<!-- Template " . __CLASS__ . " -->\n" .
"<issuetracker>\n" .
"<username>testlink.youtrackincloud</username>\n" .
"<password>youtrackincloud2012</password>\n" .
"<uribase>http://testlink.myjetbrains.com/youtrack</uribase>\n" .
"<urirest>http://testlink.myjetbrains.com/youtrack/rest/</urirest>\n" .
"<uriview>http://testlink.myjetbrains.com/youtrack/issue/</uriview>\n" .
"<uricreate>http://testlink.myjetbrains.com/youtrack/dashboard#newissue=yes</uricreate>\n" .
"</issuetracker>\n";

$cfg = "<!-- Template " . __CLASS__ . " -->\n" .
"<issuetracker>\n" .
"<username>testlink.integration</username>\n" .
"<password>integration.testlink</password>\n" .
"<uribase>http://testlink.myjetbrains.com/youtrack</uribase>\n" .
"</issuetracker>\n";

"<urirest>http://fman.myjetbrains.com/youtrack/rest/</urirest>\n" .
"<uriview>http://fman.myjetbrains.com/youtrack/issue/</uriview>\n" .
"<uricreate>http://fman.myjetbrains.com/youtrack/dashboard#newissue=yes</uricreate>\n" .


*/
$cfg = "<!-- Template " . __CLASS__ . " -->\n" .
"<issuetracker>\n" .
"<username>testlink.forum@gmail.com</username>\n" .
"<password>qazwsxedc</password>\n" .
"<uribase>http://fman.myjetbrains.com/youtrack</uribase>\n" .
"<project>fpo</project>\n" .
"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - $itfName </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

$its = new $itfName($itfName,$cfg);

$issueID = "fpo-1";
$issue = $its->getIssue($issueID);
echo '<pre>' . var_dump($issue) . '</pre>';
new dBug($issue);


$status = $its->getIssueStatusCode($issueID);
echo 'status:' . $status . '<br>'; 

// var_dump($its);
$client = $its->getAPIClient();
$yy = $client->get_state_bundle('States');
echo '<xmp><pre>' . var_dump($yy) . '</pre></xmp>';


/*
$project = 'fpo';
$assignee = '';
$summary = 'Hope this will be issue number 2';
$description = ' Hell';
$priority = $type = $subsystem = $state = $affectsVersion = $fixedVersion=$fixedInBuild='';

$xx = $client->create_issue($project, $assignee, $summary, $description, $priority, 
                      $type, $subsystem, $state, $affectsVersion, $fixedVersion, $fixedInBuild);

*/

$xx = $its->addIssue('ROTOLO', 'non è asado');
new dBug($xx);
die();


//$zz = $client->get_project_custom_field('krm','Priority');

//$zz = $client->get_enum_bundle('Types');

// $zz = $client->get_project_custom_fields('krm');
//echo '<pre>' . var_dump($zz) . '</pre>';
//die();
//$zz = $client->get_project_custom_field('YS','State');
//echo '<pre>' . var_dump($zz) . '</pre>';

// die();


//$yy = $client->get_project_issue_states('YS');
//echo '<pre>' . var_dump($yy) . '</pre>';

//$yykrm = $client->get_project_issue_states('krm');
//echo '<pre>' . var_dump($yykrm) . '</pre>';

?>