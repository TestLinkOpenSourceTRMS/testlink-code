<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * @filesource	test.jirasoapInterface.class.php
 * @author		Francisco Mancardi
 *
 * @internal revisions
 *
**/
require_once('../../../config.inc.php');
require_once('common.php');

require_once('../../../third_party/lux-phpactiveresource/ActiveResource.php');

class Issue extends ActiveResource 
{
    // var $site = 'http://testlink.redmine:redmine2012@testlink.m.redmine.org/';
    var $site = 'http://testlink.m.redmine.org/';
    var $request_format = 'xml'; // REQUIRED!
    var $user = 'testlink.redmine';
    var $password = 'redmine2012';
}

// find issues
$issue = new Issue();

new dBug($issue);
$x = $issue->find('all');

var_dump($x);

//$issues = $issueMgr->find(d);
//echo '<pre>' . var_dump($issues) . '</pre>';
//$issuesQty = count($issues);
//for ($idx=0; $idx < $issuesQty; $idx++) 
//{
//    echo $issues[$idx]->subject;
//}

//// find and update an issue
//$issue->find (1);
//echo $issue->subject;
//$issue->set ('subject', 'This is the new subject')->save ();

//// delete an issue
//$issue->find (1);
//$issue->destroy ();
?>