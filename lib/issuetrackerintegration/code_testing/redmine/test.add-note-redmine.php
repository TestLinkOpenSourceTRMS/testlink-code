<?php

// TEST USING RAW redmine interface.
// TestLink has a interface built using the RAW redmine interface
require_once '../../../../third_party/redmine-php-api/lib/redmine-rest-api.php';


// 20140908
$site = array(array('url' => 'http://192.168.1.174','apiKey' => 'e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8'),
			  array('url' => 'http://tl.m.redmine.org', 'apiKey' => 'b956de40bf8baf6af7344b759cd9471832f33922'),
			  array('url' => 'https://localhost:8443/redmine/', 'apiKey' => '81538efac88d05a1dbf77b80e793526dbd4921dd'),
			  array('url' => 'http://localhost:8888/redmine/', 'apiKey' => '81538efac88d05a1dbf77b80e793526dbd4921dd'));


$siteID = 2;
$red = new redmine($site[$siteID]['url'],$site[$siteID]['apiKey']);
$issueObj = $red->getIssue(3);
// var_dump($issueObj);
// die();

echo '<br>';
echo 'Summary(SUBJECT):' .(string)$issueObj->subject . '<br>';
echo 'Status: Name/ID' . (string)$issueObj->status['name'] . '/' . (int)$issueObj->status['id'] . '<br>';
echo '<br><hr><pre>';
echo '</pre>';

echo '<br>';
echo 'Trying to add a NOTE';
$issueXmlObj = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
$issueXmlObj->addChild('notes', htmlspecialchars('ciao Bello!!!'));
$red->addIssueNoteFromSimpleXML(3,$issueXmlObj);

