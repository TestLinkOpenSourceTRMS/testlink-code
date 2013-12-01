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
require_once('../../../../../config.inc.php');
require_once('common.php');

$it_mgr = new tlIssueTracker($db);
$itt = $it_mgr->getTypes();

// <id>10101</id>
$oneCFSimpleValue = "<customField>\n" .
				     "<customfieldId>\n" .
				     "customfield_10800\n" .
				     "</customfieldId>\n" .
		             "<values><value>12345</value></values>\n" .
				     "<customfieldId>\n" .
				   "</customField>\n";

$oneCFMultiListValue = "<customField>\n" .
					     "<customfieldId>\n" .
					     "customfield_10900\n" .
					     "</customfieldId>\n" .
			             "<values><value>Ducati</value><value>Yamaha Factory Racing</value></values>\n" .
					   "</customField>\n";


$oneCFMultiListValue = "<customField>\n" .
					     "<customfieldId>\n" .
					     "customfield_10900\n" .
					     "</customfieldId>\n" .
			             "<values><value>Ducati</value><value>Yamaha Factory Racing</value></values>\n" .
					   "</customField>\n";

$cfmod03 = "<customField>\n" .
		     "<customfieldId>\n" .
		     "customfield_10800\n" .
		     "</customfieldId>\n" .
             "<values><value>111</value></values>\n" .
           "</customField>\n" .  

           "<customField>\n" .   // <<<< PAY ATTENTION
		     "<customfieldId>\n" .
		     "customfield_10900\n" .
		     "</customfieldId>\n" .
             "<values><value>Ducati</value></values>\n" .
		   "</customField>\n";

// ELEM IN INPUTobject(SimpleXMLElement)#81 (1) { ["customField"]=> array(2) { [0]=> object(SimpleXMLElement)#78 (2) { ["customfieldId"]=> string(19) " customfield_10800 " ["values"]=> object(SimpleXMLElement)#82 (1) { ["value"]=> string(3) "111" } } [1]=> object(SimpleXMLElement)#79 (2) { ["customfieldId"]=> string(19) " customfield_10900 " ["values"]=> object(SimpleXMLElement)#82 (1) { ["value"]=> string(6) "Ducati" } } } } 
// getCustomFieldsAttribute
// AFTER get obj vars ACCESS IN INPUT
// array(2) { [0]=> object(SimpleXMLElement)#79 (2) { ["customfieldId"]=> string(19) " customfield_10800 " ["values"]=> object(SimpleXMLElement)#82 (1) { ["value"]=> string(3) "111" } } [1]=> object(SimpleXMLElement)#78 (2) { ["customfieldId"]=> string(19) " customfield_10900 " ["values"]=> object(SimpleXMLElement)#82 (1) { ["value"]=> string(6) "Ducati" } } } 
// IO SONO ITEM
// object(SimpleXMLElement)#79 (2) { ["customfieldId"]=> string(19) " customfield_10800 " ["values"]=> object(SimpleXMLElement)#82 (1) { ["value"]=> string(3) "111" } } ID:


// ELEM IN INPUTobject(SimpleXMLElement)#81 (1) { ["customField"]=> object(SimpleXMLElement)#78 (2) { ["customfieldId"]=> string(19) " customfield_10800 " ["values"]=> object(SimpleXMLElement)#79 (1) { ["value"]=> string(3) "111" } } } 
// getCustomFieldsAttribute
// AFTER get obj vars ACCESS IN INPUT
// object(SimpleXMLElement)#78 (2) { ["customfieldId"]=> string(19) " customfield_10800 " ["values"]=> object(SimpleXMLElement)#79 (1) { ["value"]=> string(3) "111" } } 
//IO SONO ITEM
//object(SimpleXMLElement)#82 (0) { } ID:

//AFTER get obj vars ACCESS IN INPUT
//array(1) { [0]=> object(SimpleXMLElement)#78 (2) { ["customfieldId"]=> string(19) " customfield_10800 " ["values"]=> object(SimpleXMLElement)#79 (1) { ["value"]=> string(3) "111" } } } 
//IO SONO ITEM
//object(SimpleXMLElement)#78 (2) { ["customfieldId"]=> string(19) " customfield_10800 " ["values"]=> object(SimpleXMLElement)#79 (1) { ["value"]=> string(3) "111" } } ID:


$cfmod04 = "<customField>\n" .
		     "<customfieldId>\n" .
		     "customfield_10800\n" .
		     "</customfieldId>\n" .
             "<values><value>111</value></values>\n" .
		   "</customField>\n";

$oneOneCFMultiListValue = "<customField>\n" .
					     "<customfieldId>\n" .
					     "customfield_10900\n" .
					     "</customfieldId>\n" .
			             "<values><value>Yamaha Factory Racing</value></values>\n" .
					   "</customField>\n";

$cfmod05 = "<customField>\n" .
  	       "<customfieldId>\n" .
		     "customfield_10900\n" .
		     "</customfieldId>\n" .
             "<values><value>Ducati</value></values>\n" .
		   "</customField>\n";


$cfmod06 = "<customField>\n" .
		     "<customfieldId>\n" .
		     "customfield_10800\n" .
		     "</customfieldId>\n" .
             "<values><value>111</value></values>\n" .
		   "</customField>\n";

$cf = $oneCFMultiListValue;


$cfg =  "<issuetracker>\n" .
		"<username>testlink.forum</username>\n" .
		"<password>forum</password>\n" .
		"<uribase>http://testlink.atlassian.net/</uribase>\n" .
		"<uriwsdl>http://testlink.atlassian.net/rpc/soap/jirasoapservice-v2?wsdl</uriwsdl>\n" .
		"<uriview>http://testlink.atlassian.net/browse/</uriview>\n" .
		"<uricreate>http://testlink.atlassian.net/secure/CreateIssue!default.jspa</uricreate>\n" .
        "<projectkey>ZOFF</projectkey>\n" .
        "<issuetype>1</issuetype>\n" .
		"<attributes><components><id>10100</id><id>10101</id></components>\n" .
		"<customFieldValues>\n" . $cf . "</customFieldValues>\n" .
		"</attributes>\n" .
		"</issuetracker>\n";

echo '<hr><br>';
echo "<b>Testing  BST Integration - jirasoapInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";
echo '<hr><br><br>';
echo 'Creating INTERFACE<br>';
$its = new jirasoapInterface(5,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());

if( $its->isConnected() )
{
  $today = date("Y-m-d H:i:s");	
  $issue = array('summary' => 'Issue Via API' . $today,'description' => 'Do Androids Dream of Electric Sheep?');
  $zorro = $its->addIssue($issue['summary'],$issue['description']);
  echo '<pre>';
  var_dump($zorro);
  echo '</pre>';
}
