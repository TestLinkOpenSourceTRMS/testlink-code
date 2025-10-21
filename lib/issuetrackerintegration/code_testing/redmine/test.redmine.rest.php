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
require_once '../../../config.inc.php';
require_once 'common.php';

require_once '../../../third_party/lux-phpactiveresource/ActiveResource.php';

class Issue extends ActiveResource
{

    public $site = 'http://testlink.m.redmine.org/';

    public $request_format = 'xml';

    // REQUIRED!
    public $user = 'testlink.redmine';

    public $password = 'redmine2012';
}

// find issues
$issue = new Issue();

new dBug($issue);
$x = $issue->find('all');

var_dump($x);

?>
