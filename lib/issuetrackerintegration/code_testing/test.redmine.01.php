<?php
require_once '../../../third_party/redmine-php-api/lib/redmine-rest-api.php';

$site = array(array('url' => 'http://192.168.1.174','apiKey' => 'e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8'),
			  array('url' => 'http://tl.m.redmine.org', 'apiKey' => 'b956de40bf8baf6af7344b759cd9471832f33922'));

$red = new redmine($site[0]['url'],$site[0]['apiKey']);
$issueObj = $red->getIssue(3);
var_dump($issueObj);
die();

echo '<br>';
echo 'Summary(SUBJECT):' .(string)$issueObj->subject . '<br>';
echo 'Status: Name/ID' . (string)$issueObj->status['name'] . '/' . (int)$issueObj->status['id'] . '<br>';
echo '<br><hr><pre>';
echo '</pre>';
?>