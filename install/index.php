<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Navigation for installation scripts
 *
 * @package     TestLink
 * @copyright   2007,2018 TestLink community
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
  <link href="../gui/themes/default/images/favicon.ico" rel="icon" type="image/gif"/>
  <!-- Bootstrap Metas -->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Reset CSS for Bootstrap+HTML5 -->
  <link rel="stylesheet" href="../gui/themes/tmpl-bs/css/reset.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../gui/themes/tmpl-bs/css/bootstrap.min.css">
  <link rel="stylesheet" href="../gui/themes/tmpl-bs/css/bootstrap-theme.min.css">
  <!-- Testlink CSS -->
  <style type="text/css">@import url('../gui/themes/tmpl-bs/css/template.css');</style>
</head>

<body id="tl-container" class="container-fluid">

  <section id="tl-section" class="row">

    <header id="tl-header" class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-md-offset-2 col-sm-offset-1 tl-title tl-box-header">
      <h1 class="text-center tl-title">
        TestLink <?php echo $_SESSION['testlink_version'] ?>
      </h1>
    </header>
    <section class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-md-offset-2 col-sm-offset-1 tl-box-footer"></section>
    <section class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-md-offset-2 col-sm-offset-1 tl-box-main">
      <h2 class="tl-title">How to install?</h2>
      <p><i>
        TestLink is a complicated piece of software, and has always been released under an Open Source license, and this will continue into the far future. It has cost thousands of hours to develop, test and support TestLink. If you find TestLink valuable, we would appreciate if you would consider buying a support agreement or requesting custom development.
      </i></p>
      <p><button class="btn btn-primary btn-block" onclick="location.href='installIntro.php?type=new'">Install</button></p>
    </section>
    <section class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-md-offset-2 col-sm-offset-1 tl-box-footer"></section>
    <section class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-md-offset-2 col-sm-offset-1 tl-box-main">
      <h2 class="tl-title">How to migrate?</h2>
      <p>
        Migration from 
        <span class="tl-text-bold"><?php echo $prev_ver ?></span> 
        to 
        <span class="tl-text-bold"><?php echo $_SESSION['testlink_version'] ?></span> 
        require Database changes that has to be done MANUALLY. Please read 
        <a target="_blank" href="https://github.com/TestLinkOpenSourceTRMS/testlink-code/blob/master/README.md">README</a> 
        file provided with installation.
      </p>
      <p class="alert alert-info">
        For information about Migration from older version please read 
        <a target="_blank" href="https://github.com/TestLinkOpenSourceTRMS/testlink-code/blob/testlink_1_9/README.md#5-upgrade-and-migration">README</a> 
        file provided with installation.
      </p>
      <p class="alert alert-info tl-note">
        Please read Section on README file or go to
        <a href="<?php echo 'http://' .$forum_url ?>">
          <?php echo 'http://' .$forum_url ?>
          (Forum: TestLink 1.9.4 and greater News,changes, etc)
        </a>
      </p>
      <p class="alert alert-warning tl-note">
        Open
        <a target="_blank" href="../docs/testlink_installation_manual.pdf">Installation manual</a>
        for more information or troubleshooting. You could also look at
        <a target="_blank" href="https://github.com/TestLinkOpenSourceTRMS/testlink-code/blob/master/README.md">README</a>
        or
        <a target="_blank" href="https://github.com/TestLinkOpenSourceTRMS/testlink-code/blob/testlink_1_9/CHANGELOG">Changes Log</a>.
        You are welcome to visit our
        <a target="_blank" href="http://forum.testlink.org">forum</a>
        to browse or discuss.
      </p>
    </section>
    <section class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-md-offset-2 col-sm-offset-1 tl-box-footer"></section>
    <section class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-md-offset-2 col-sm-offset-1 tl-box-main">
      <h2 class="text-center tl-title">
        Documentation & Contributions
        <br />
        <span class="text-center tl-desc">Some user contributed with videos (YouTube)</span>
      </h2>
      <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 col-lg-offset-2">
         <iframe src="https://www.youtube.com/embed/NOvTWZvc2x8" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
         <p class="text-center"><a href="https://www.youtube.com/watch?v=NOvTWZvc2x8" target="#">Installation of "Testlink" & Creating project.</a></p>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
        <iframe src="https://www.youtube.com/embed/P2zWScVjuag" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        <p class="text-center"><a href="https://www.youtube.com/watch?v=P2zWScVjuag" target="#">TestLink Test Management Tool Tutorial</a></p>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 col-lg-offset-2">
        <iframe src="https://www.youtube.com/embed/7xH1LKQU1TA" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        <p class="text-center"><a href="https://www.youtube.com/watch?v=7xH1LKQU1TA" target="#">Introduction to TestLink</a></p>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
        <iframe src="https://www.youtube.com/embed/P2zWScVjuag" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        <p class="text-center"><a href="https://www.youtube.com/watch?v=6s48WGuX2WE" target="#">TestLink Walkthrough</a></p>
      </div>
    </section>
    <footer id="tl-footer" class="col-lg-8 col-md-8 col-sm-10 col-xs-12 col-md-offset-2 col-sm-offset-1 tl-box-footer"></footer>

  </section>

  <!-- Jquery 3.3.1 -->
  <script type="text/javascript" src="../gui/themes/tmpl-bs/js/jquery.min.js"></script>
  <!-- Bootstrap JS 3.3.7 -->
  <script type="text/javascript" src="../gui/themes/tmpl-bs/js/bootstrap.min.js"></script>
  <!-- Testlink JS -->
  <script type="text/javascript" src="../gui/themes/tmpl-bs/js/template.js"></script>
</body>
</html>
