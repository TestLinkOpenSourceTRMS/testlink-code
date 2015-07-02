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

// last test ok: 20121117
/*
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
		"<customFieldValues>\n" .
		"<remoteCustomFieldValue>\n" .
		"<customFieldID>\n" .
		"customfield_10800\n" .
		"</customFieldID>\n" .
        "<values><value>12</value></values>\n" .
		"</remoteCustomFieldValue>\n" .
		"</customFieldValues>\n" .
		"</attributes>\n" .
		"</issuetracker>\n";

array(7) { ["project"]=> string(4) "ZOFF" ["type"]=> int(1) 
["summary"]=> string(24) "Issue Via API 2013-02-04" ["description"]=> string(36) "Do Androids Dream of Electric Sheep?" ["components"]=> array(2) { [0]=> array(1) { ["id"]=> string(5) "10100" } [1]=> array(1) { ["id"]=> string(5) "10101" } } ["customFieldValues"]=> array(2) { [0]=> array(1) { ["remoteCustomFieldValue"]=> string(19) " customfield_10800 " } [1]=> array(1) { ["remoteCustomFieldValue"]=> string(0) "" } } ["issuetype"]=> int(1) } array(3) { ["status_ok"]=> bool(false) ["id"]=> int(-1) ["msg"]=> string(550) "Create JIRA Ticket FAILURE => com.atlassian.jira.rpc.exception.RemoteValidationException: Custom field ID 'null' is invalid. - serialized issue:a:7:{s:7:"project";s:4:"ZOFF";s:4:"type";i:1;s:7:"summary";s:24:"Issue Via API 2013-02-04";s:11:"description";s:36:"Do Androids Dream of Electric Sheep?";s:10:"components";a:2:{i:0;a:1:{s:2:"id";s:5:"10100";}i:1;a:1:{s:2:"id";s:5:"10101";}}s:17:"customFieldValues";a:2:{i:0;a:1:{s:22:"remoteCustomFieldValue";s:19:" customfield_10800 ";}i:1;a:1:{s:22:"remoteCustomFieldValue";s:0:"";}}s:9:"issuetype";i:1;}" } 		
*/

/*
- snip --
$issue_attributes = array('project' => 'FEEDBACK', 'assignee' => 'simon',
'type' => '1', 'summary' => 'test',
'customFieldValues' =>
array(array('customfieldId' => 'customfield_10011', 'value' =>
'a little'))
);
*/

/*
$cfg =  "<issuetracker>\n" .
		"<username>testlink.forum</username>\n" .
		"<password>forum</password>\n" .
		"<uribase>http://testlink.atlassian.net/</uribase>\n" .
		"<uriwsdl>http://testlink.atlassian.net/rpc/soap/jirasoapservice-v2?wsdl</uriwsdl>\n" .
		"<uriview>http://testlink.atlassian.net/browse/</uriview>\n" .
		"<uricreate>http://testlink.atlassian.net/secure/CreateIssue!default.jspa</uricreate>\n" .
        "<projectkey>ZOFF</projectkey>\n" .
        "<issuetype>1</issuetype>\n" .
		"</issuetracker>\n";
*/

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
		"<customFieldValues>\n" .
		"<id>\n" .
		"<customFieldID>\n" .
		"customfield_10800\n" .
		"</customFieldID>\n" .
        "<values><value>12</value><value>120</value></values>\n" .
		"</id>\n" .
		"</customFieldValues>\n" .
		"</attributes>\n" .
		"</issuetracker>\n";

/*
{ ["components"]       => object(SimpleXMLElement)#84 (1) { ["id"]=> array(2) { [0]=> string(5) "10100" [1]=> string(5) "10101" } } 
  ["customFieldValues"]=> object(SimpleXMLElement)#83 (1) 
                          { ["id"]=> object(SimpleXMLElement)#82 (2) 
                            { ["customFieldID"]=> string(19) " customfield_10800 " 
                              ["values"]=> object(SimpleXMLElement)#85 (1) 
                                           { ["value"]=> array(2) { [0]=> string(2) "12" [1]=> string(3) "120" } } } } } }

This is an issue to allow people to test integration
array(7) { ["project"]=> string(4) "ZOFF" ["type"]=> int(1) 
           ["summary"]=> string(24) "Issue Via API 2013-02-04" 
           ["description"]=> string(36) "Do Androids Dream of Electric Sheep?" 
           ["components"]=> array(2) { [0]=> array(1) { ["id"]=> string(5) "10100" } [1]=> array(1) { ["id"]=> string(5) "10101" } } 
           ["customFieldValues"]=> array(2) { [0]=> array(1) { ["id"]=> string(19) " customfield_10800 " } 
                                              [1]=> array(1) { ["id"]=> string(0) "" } } 
           ["issuetype"]=> int(1) } array(3) { ["status_ok"]=> bool(false) ["id"]=> int(-1) 
           ["msg"]=> string(508) "Create JIRA Ticket FAILURE => com.atlassian.jira.rpc.exception.RemoteValidationException: 
           Custom field ID 'null' is invalid. - 
serialized issue:a:7:{s:7:"project";s:4:"ZOFF";s:4:"type";i:1;s:7:"summary";s:24:"Issue Via API 2013-02-04";
s:11:"description";s:36:"Do Androids Dream of Electric Sheep?";
s:10:"components"       ;a:2:{i:0;a:1:{s:2:"id";s:5:"10100";}i:1;a:1:{s:2:"id";s:5:"10101";}}
s:17:"customFieldValues";a:2:{i:0;a:1:{s:2:"id";s:19:" customfield_10800 ";}i:1;a:1:{s:2:"id";s:0:"";}}s:9:"issuetype";i:1;}" } 


<item lastUpdated="2011-06-09T08:30:18-05:00" available="true">
      <category>bread</category>
      <category>chicken</category>
      <category>non-veg</category>
      <keyword>burger</keyword>
      <keyword>chicken</keyword>
      <assets>
        <title>Zinger Burger</title>
        <desc><![CDATA[The Burger we all love >_< !]]></desc>
        <image height="100" width="100" url="http://www.example.com/res/zinger.png" info="Zinger Burger"/>
      </assets>
      <price currency="USD">10</price>
      <price currency="INR">450</price>
      <trivia></trivia>
    </item>

    <Department>
  <Name>IT</Name>
  <Employees>
    <Employee>
      <Name>Bob</Name>
    </Employee>
    <Employee>
      <Name>Jim</Name>
    </Employee>
    <Employee>
      <Name>Mel</Name>
    </Employee>
  </Employees>
</Department>


*/


echo '<hr><br>';
echo "<b>Testing  BST Integration - jirasoapInterface </b>";
echo '<hr><br>';
echo "Configuration settings<br>";
echo "<pre><xmp>" . $cfg . "</xmp></pre>";

echo '<hr><br><br>';

// $safe_cfg = str_replace("\n",'',$cfg);
// echo $safe_cfg;
echo 'Creating INTERFACE<br>';

new dBug($itt);
$its = new jirasoapInterface(5,$cfg);

echo 'Connection OK?<br>';
var_dump($its->isConnected());

if( $its->isConnected() )
{
  echo 'Get Issue <br>';
  $zx = $its->getIssue('ZOFF-112');
  var_dump($zx);
  //echo $zx->asXML();

  echo '<br>';

	// echo '<b>Connected !</br></b>';
	// echo '<pre>';
	// var_dump($its->getStatusDomain());
	// echo '</pre>';
  //echo 'Get Issue Summary<br>';
  //	echo($its->getIssueSummary('ZOFF-16'));
  //echo '<br>';

	
// I've seen things you people wouldn't believe. 
// Attack ships on fire off the shoulder of Orion. 
// I watched C-beams glitter in the dark near the Tannhauser gate. 
// All those moments will be lost in time... like tears in rain... Time to die. 	
//
  //$issue = array('project' => 'ZOFF','summary' => 'My Firts ISSUE VIA API',
  //               'description' => 'Do Androids Dream of Electric Sheep?',
  //               'type' => 1, 'components' => array( array('id' => '10100'), array('id' => '10101')));
  // $zorro = $its->addIssueFromArray($issue);
  // var_dump($zorro);
  $issue = array('summary' => 'Issue Via API 2013-02-04','description' => 'Do Androids Dream of Electric Sheep?');
  //               'type' => 1, 'components' => array( array('id' => '10100'), array('id' => '10101')));
  
  $zorro = $its->addIssue($issue['summary'],$issue['description']);
  var_dump($zorro);


	/*
	$issue2check = array( array('issue' => 'TLJIRASOAPINTEGRATION-1', 'exists' => true),
	  					  array('issue' => 'TLJIRASOAPINTEGRATION-199999', 'exists' => false));

	*/



/*
["customFieldValues"]=> array(4) 
{ [0]=> object(stdClass)#85 (3) 
        { ["customfieldId"]=> string(17) "customfield_10800" ["key"]=> NULL ["values"]=> array(1) { [0]=> string(3) "123" } } 
  [1]=> object(stdClass)#82 (3) 
        { ["customfieldId"]=> string(17) "customfield_10000" ["key"]=> NULL ["values"]=> array(1) { [0]=> string(3) "117" } } 
  [2]=> object(stdClass)#83 (3) 
        { ["customfieldId"]=> string(17) "customfield_10200" ["key"]=> NULL ["values"]=> array(1) { [0]=> string(3) "117" } } 
  [3]=> object(stdClass)#84 (3) { ["customfieldId"]=> string(17) "customfield_10607" ["key"]=> NULL ["values"]=> array(1) { [0]=> NULL } } }
*/
}
?>