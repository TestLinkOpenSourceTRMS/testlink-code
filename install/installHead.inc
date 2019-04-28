<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Installation script - html header and overview
 * 
 * @package   TestLink
 * @author    Martin Havlat
 * @copyright   2009,2017 TestLink community 
 * @version     installHead.inc
 *
 *
 */
function getPhaseAttr($inst_phase, $phase)
{
  if ($phase == $inst_phase)
  {
    echo ' style="font-weight: bold;"';
  }
}

?>
<!DOCTYPE html>
<head>
  <title><?php echo $_SESSION['title'] ?></title>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <link href="../gui/themes/default/images/favicon.ico" rel="icon" type="image/gif"/>
  <link href="../gui/themes/default/css/testlink.css" rel="stylesheet" type="text/css" />
    <style type="text/css">@import url('./css/style.css');</style>
</head> 
<body>
<div class="tlPager">
<h1><img src="./img/dot.gif" alt="Dot" style="margin: 0px 10px;" />
    TestLink <?php echo $_SESSION['title'] ?></h1>
<div class="tlLiner">&nbsp;</div>
<div class="tlPhases">
<ul>
  <li<?php echo getPhaseAttr($inst_phase,'license')?> >Acceptance of License</li>
  <li<?php echo getPhaseAttr($inst_phase,'checking')?> >Verification of System and 
      configuration requirements</li>
  <li<?php echo getPhaseAttr($inst_phase,'dbaccess')?> >Definition of DB access</li>
  <li<?php echo getPhaseAttr($inst_phase,'dbprocess')?> >
  <?php if ($_SESSION['isNew'])
  echo "Create DB, testlink DB user, structures and default data & create configuration file.";
  else echo "Update DB structures and migrate data."; ?>
  </li>
  <li<?php echo getPhaseAttr($inst_phase,'final') ?>>Verify the procedure result and continue 
      to TestLink login.</li>
</ul>
</div>
<div class="tlLiner">&nbsp;</div>
