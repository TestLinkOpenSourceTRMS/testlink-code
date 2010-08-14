<?php
/**
 * Created by PhpStorm.
 * User: Sergey Andreev (sergey.andreev@jetbrains.com])
 * Date: Jun 17, 2010
 */


/** The Username for YouTrack login */
define('YOUTRACK_USERNAME', "testlink");

/** The Password for YouTrack login */
define('YOUTRACK_PASSWORD', "testlink");

/** URL to YouTrack service */
define('YOUTRACK_URL', "http://www.youtrackroot.com/");


/** Do not change setting below */
define('BUG_TRACK_REST', "rest/");
define('BUG_TRACK_SHOW_ISSUE', "issue/");
define('BUG_TRACK_NEW_ISSUE', "dashboard#newissue=yes");
define('BUG_TRACK_REST_LOGIN', "user/login");
define('BUG_TRACK_REST_STATES', "project/states");

//Set the bug tracking system Interface to YouTrack
//-----------------------------------------------------------------------------------------
/* The following parameters are not in use. */
define('BUG_TRACK_DB_TYPE', '[Not in Use]');
define('BUG_TRACK_DB_NAME', '[Not in Use]');
define('BUG_TRACK_DB_CHARSET', '[Not in Use]');
define('BUG_TRACK_DB_USER', '[Not in Use]');
define('BUG_TRACK_DB_PASS', '[Not in Use]');
//-----------------------------------------------------------------------------------------
?>