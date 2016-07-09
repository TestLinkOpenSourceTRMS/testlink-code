<?php
require_once '../../../../config.inc.php';

require_once '../../../../lib/functions/common.php';
require_once '../../../../third_party/redmine-php-api/lib/redmine-rest-api.php';

// $email = 'francisco.mancardi@gmail.com';
// $url = 'http://tl.m.redmine.org';
// $url = 'http://fman.m.redmine.org';
// $url = 'http://tl.m.redmine.org';
// $apiKey = 'b956de40bf8baf6af7344b759cd9471832f33922';
// $username = 'tl';
// $password = 'redmine2012';

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

$targetSite = 1;

$pxy = new stdClass();
$pxy->proxy = config_get('proxy');
var_dump($pxy);

$red = new redmine($site[$targetSite]['url'],
                   $site[$targetSite]['apiKey'],$pxy);

echo 'Target Installation:' . $site[$targetSite]['url'] . '<br>';
$result = $red->getProjects();
var_dump($result);
