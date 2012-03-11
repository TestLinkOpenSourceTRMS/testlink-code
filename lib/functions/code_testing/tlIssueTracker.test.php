<?php

require_once('../../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

define('DBUG_ON',1);

$it = new tlIssueTracker($db);
new dBug($it);

$issueTrackerDomain = array_flip($it->getTypes());

$tprojectSet = array(32674,2,27);

/*
$dx = new stdClass();
$dx->name = 'Francisco2';
$dx->type = $issueTrackerDomain['MANTIS'];
$dx->cfg = " I'm Mantis ";
$info = $it->create($dx);
new dBug($info);
*/

$str = "<?xml version='1.0'?>";
$str = '';
$str .= "<issuetracker>" .
		"<dbhost>localhost</dbhost>" .
		"<dbname>mantis_tlorg</dbname>" .
		"<dbtype>mysql</dbtype>" .
		"<dbuser>root</dbuser>" .
		"<dbpassword>mysqlroot</dbpassword>" .
		"<hrefview>http://localhost:8080/development/mantisbt-1.2.5/my_view_page.php?id=</hrefview>" .
		"<hrefcreate>http://localhost:8080/development/mantisbt-1.2.5/</hrefcreate>" .
		"</issuetracker>";

$dx = new stdClass();
$dx->name = 'Francisco3';
$dx->type = $issueTrackerDomain['MANTIS'];
$dx->cfg = $str;
$info = $it->create($dx);
new dBug($info);

$info = $it->getByName('Francisco3');
new dBug($info);


die();

$links = $it->getLinks(4);
new dBug($links);

// $it->link(4,2);

$links = $it->getLinks(4);
new dBug($links);


$linkSet = $it->getLinkSet();
new dBug($linkSet);

$info = $it->delete(0);
new dBug($info);

$info = $it->delete('papap');
new dBug($info);

$info = $it->delete(-1);
new dBug($info);


$info = $it->delete(4);
new dBug($info);

$info = $it->delete(5);
new dBug($info);

die();


/*
$opt = array(null,array('output' => 'id'));
foreach($opt as $o)
{
	$info = $it->getByName('FMAN',$o);
	new dBug($info);
}

$opt = array(null,array('output' => 'id'));
foreach($opt as $o)
{
	$info = $it->getByID(2,$o);
	new dBug($info);
}


$dx = new stdClass();
$dx->name = 'FEFE';
$dx->type = $issueTrackerDomain['MANTIS'];
$dx->cfg = " I'm Mantis ";
$info = $it->create($dx);
new dBug($info);
*/

/*
$it->link(22,27);
$it->unlink(2,27);
$it->unlink(22,27);
*/

$dx = new stdClass();
$dx->id = 1;
$dx->name = 'FEFE';
$dx->type = $issueTrackerDomain['MANTIS'];
$dx->cfg = " I'm Mantis ";
$xx = $it->update($dx);

new dBug($xx);

$dx = new stdClass();
$dx->id = 1;
$dx->name = 'FARFANS';
$dx->type = $issueTrackerDomain['MANTIS'];
$dx->cfg = " I'm Mantis ";
$xx = $it->update($dx);

new dBug($xx);


$links = $it->getLinks(1);
new dBug($links);
		

?>