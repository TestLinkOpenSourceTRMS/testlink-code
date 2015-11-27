<?php
// require_once '../../../../third_party/redmine-php-api/lib/redmine-rest-api.php';

// $email = 'francisco.mancardi@gmail.com';
// $url = 'http://tl.m.redmine.org';
// $url = 'http://fman.m.redmine.org';
// $url = 'http://tl.m.redmine.org';
// $apiKey = 'b956de40bf8baf6af7344b759cd9471832f33922';
// $username = 'tl';
// $password = 'redmine2012';

require_once('../../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();





//$site = array(array('url' => 'http://192.168.1.174','apiKey' => 'e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8'),
// $site = array(array('url' => 'http://192.168.1.2','apiKey' => 'e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8'),
$site = array(array('url' => 'http://192.168.1.74','apiKey' => 'e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8'),
			  array('url' => 'http://tl.m.redmine.org', 'project_id' => 'tl-rest',
              'apiKey' => 'b956de40bf8baf6af7344b759cd9471832f33922'),
        array('url' => 'http://127.0.0.1:8085/redmine', 'project_id' => 'fedora-20',
              'apiKey' => '630e3a09b365757458c4039257f7ee57e87cec5d'),
        array('url' => 'https://127.0.0.1:8443/redmine', 'project_id' => 'fedora-20',
              'apiKey' => '630e3a09b365757458c4039257f7ee57e87cec5d'));

// e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8
// curl -v -H "Content-Type: application/xml" -X POST --data "@issue.xml" -H "X-Redmine-API-Key: e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8" http://192.168.1.2/issues.xml

$targetSite = 3;

$cfg = '' . "\n" .
'<issuetracker>' . "\n" .
'<apikey>' . $site[$targetSite]['apiKey'] . '</apikey>' . "\n" .
'<uribase>' . $site[$targetSite]['url'] . '</uribase>' . "\n" .
'<projectidentifier>' . $site[$targetSite]['project_id'] . 
'</projectidentifier>' . "\n" .
 '<custom_fields type="array">' . "\n" .
 '           <custom_field id="1" name="CF-STRING-OPT">' . "\n" .
 '               <value>SALAME</value>' . "\n" .
 '           </custom_field>' . "\n" .
 '           <custom_field id="2" name="CF-STRING-MANDATORY">' . "\n" .
 '               <value>STRF</value>' . "\n" .
 '           </custom_field>' . "\n" .
 '           <custom_field id="3" name="CF-LIST-OPT" multiple="true">' . "\n" .
 '               <value type="array">' . "\n" .
 '                   <value>ALFA</value>' . "\n" .
 '               </value>' . "\n" .
 '           </custom_field>' . "\n" .
 '       </custom_fields>' . "\n" .
    '</issuetracker>';

echo '<pre><xmp>';var_dump($cfg);echo '</xmp></pre>';

// var_dump($itt);
$its = new redminerestInterface(15,$cfg);
var_dump($its->getCfg());

echo 'ADD?';

$its->addIssue('SUMMARY ','TEST DESCR');


