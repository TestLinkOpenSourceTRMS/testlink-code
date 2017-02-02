<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Navigation for installation scripts
 *
 * @package     TestLink
 * @copyright   2007,2017 TestLink community
 * @filesource  index.php
 *
 * @internal revisions
 */

if(!isset($tlCfg))
{
  $tlCfg = new stdClass();  
} 
require_once("../cfg/const.inc.php");

session_start();
$_SESSION['session_test'] = 1;
$_SESSION['testlink_version'] = TL_VERSION;

$prev_ver = '1.9.3/4/5/6/7/8/9/10/11/12/13/14/15/16 ';
$forum_url = 'forum.testlink.org';
?>
<!DOCTYPE html>
<head>
  <title>Testlink <?php echo $_SESSION['testlink_version'] ?> Installation procedure</title>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <link href="../gui/themes/default/images/favicon.ico" rel="icon" type="image/gif"/>
  <style type="text/css">@import url('./css/style.css');</style>
</head>

<body>
<div class="tlPager">
<h1><img src="./img/dot.gif" alt="Dot" style="margin: 0px 10px;" />
    TestLink <?php echo $_SESSION['testlink_version'] ?> Installation</h1>
<div class="tlLiner">&nbsp;</div>
<div class="tlStory">
    <p>You are installing TestLink <?php echo $_SESSION['testlink_version'] ?> </p>
    <p><b>Migration from <?php echo $prev_ver ?>  to  <?php echo $_SESSION['testlink_version'] ?> require Database changes that has to be done MANUALLY.
          Please read README file provided with installation.</b></p> 
    <p><b>For information about Migration from older version please read README file provided with installation.</b></p> 
    <p><b>Please read Section on README file or go to <?php echo 'http://' .$forum_url ?> (Forum: TestLink 1.9.4 and greater News,changes, etc)</b> </p>
    <p>Open <a target="_blank" href="../docs/testlink_installation_manual.pdf">Installation manual</a>
    for more information or troubleshooting. You could also look at
    <a href="../README">README</a> or <a href="../CHANGELOG">Changes Log</a>.
    You are welcome to visit our <a target="_blank" href="http://forum.testlink.org">
    forum</a> to browse or discuss.
    </p>
    <p><h3>Some user contributed videos (You Tube)</h3></p>
    <b>
    <a href="https://www.youtube.com/watch?v=NOvTWZvc2x8" target="#">Installation of "Testlink" & Creating project.</a><br>
    <a href="https://www.youtube.com/watch?v=P2zWScVjuag" target="#">TestLink Test Management Tool Tutorial</a><br>
    <a href="https://www.youtube.com/watch?v=7xH1LKQU1TA" target="#">Introduction to TestLink</a><br>
    <a href="https://www.youtube.com/watch?v=6s48WGuX2WE" target="#">TestLink Walkthrough</a><br>
    </b>

    <p><ul><li><a href="installIntro.php?type=new">New installation</a></li></ul></p>

    <br>
    <i>
    TestLink is a complicated piece of software, and has always been released under 
    an Open Source license, and this will continue into the far future. 
    <br>It has cost thousands of hours to develop, test and support TestLink. 
    <br>If you find TestLink valuable, we would appreciate if you would consider 
    buying a support agreement or requesting custom development.    
    </i>
</div>
<div class="tlLiner">&nbsp;</div>
</div>
</body>
</html>