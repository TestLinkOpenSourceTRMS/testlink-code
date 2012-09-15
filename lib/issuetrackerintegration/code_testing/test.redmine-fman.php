<?php
require_once '../../../third_party/redmine-php-api/lib/redmine-rest-api.php';

// $email = 'francisco.mancardi@gmail.com';
// $url = 'http://tl.m.redmine.org';
// $url = 'http://fman.m.redmine.org';
// $url = 'http://tl.m.redmine.org';
// $apiKey = 'b956de40bf8baf6af7344b759cd9471832f33922';
// $username = 'tl';
// $password = 'redmine2012';

$site = array(array('url' => 'http://192.168.1.174','apiKey' => 'e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8'),
			  array('url' => 'http://tl.m.redmine.org', 'apiKey' => 'b956de40bf8baf6af7344b759cd9471832f33922'));
			  

$red = new redmine($site[0]['url'],$site[0]['apiKey']);
$issueObj = $red->getIssue(3);

echo '<br>';
echo 'Summary(SUBJECT):' .(string)$issueObj->subject . '<br>';
echo 'Status: Name/ID' . (string)$issueObj->status['name'] . '/' . (int)$issueObj->status['id'] . '<br>';
echo '<br><hr><pre>';
echo '</pre>';

die();

/*
20120328 20:44
object(redmine)#1 (4) { ["url"]=> string(25) "http://fman.m.redmine.org" 
["apiKey"]=> string(40) "b956de40bf8baf6af7344b759cd9471832f33922" 
["curl"]=> resource(3) of type (curl) ["headers":"redmine":private]=> array(0) { } } 
/issues.xml?key=b956de40bf8baf6af7344b759cd9471832f33922
http://fman.m.redmine.org/issues.xml?key=b956de40bf8baf6af7344b759cd9471832f33922object(SimpleXMLElement)#2 (2) 
{ ["@attributes"]=> array(4) { ["limit"]=> string(2) "25" ["type"]=> string(5)
 "array" ["total_count"]=> string(1) "1" ["offset"]=> string(1) "0" } 
 ["issue"]=> object(SimpleXMLElement)#3 (14) { ["id"]=> string(1) "1"
  ["project"]=> object(SimpleXMLElement)#4 (1) { ["@attributes"]=> array(2)
   { ["name"]=> string(11) "fman-prj001" ["id"]=> string(1) "1" } }
   ["tracker"]=> object(SimpleXMLElement)#5 (1) { ["@attributes"]=> array(2) 
   { ["name"]=> string(3) "Bug" ["id"]=> string(1) "1" } } 
   ["status"]=> object(SimpleXMLElement)#6 (1) { ["@attributes"]=> array(2) 
   { ["name"]=> string(3) "New" ["id"]=> string(1) "1" } } ["priority"]=> object(SimpleXMLElement)#7 (1) 
   { ["@attributes"]=> array(2) { ["name"]=> string(6) "Urgent" ["id"]=> string(1) "6" } }
    ["author"]=> object(SimpleXMLElement)#8 (1) { ["@attributes"]=> array(2) 
    { ["name"]=> string(13) "Redmine Admin" ["id"]=> string(1) "2" } } ["subject"]=> string(11) 
    "fman-prj001" ["description"]=> string(11) "fman-prj001" ["start_date"]=> string(10) "2012-03-28" 
    ["due_date"]=> object(SimpleXMLElement)#9 (0) { } ["done_ratio"]=> string(1) "0" 
    ["estimated_hours"]=> object(SimpleXMLElement)#10 (0) { } ["created_on"]=> string(25) 
    "2012-03-28T20:41:21+02:00" ["updated_on"]=> string(25) "2012-03-28T20:41:21+02:00" } } 
*/    
?>