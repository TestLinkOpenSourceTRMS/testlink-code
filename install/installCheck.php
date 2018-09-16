<?php 
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Verify environment
 * Note: information is passed via $_SESSION
 * 
 * @filesource	installCheck.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009,2012 TestLink community 
 *
 * @internal revisions
 * @since 1.9.6
 **/
require_once('..' . DIRECTORY_SEPARATOR . 'config.inc.php');
require_once('..' . DIRECTORY_SEPARATOR . 'lib'. DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'common.php');
require_once('..' . DIRECTORY_SEPARATOR . 'lib'. DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'configCheck.php');

if( !isset($_SESSION) )
{ 
  session_start();
}

$inst_phase = 'checking';  // global variable -> absolutely wrong use as usual, used on installHead.inc	
$msg='';
include 'installHead.inc';
?>
    <section class="col-lg-6 col-md-8 col-sm-10 col-xs-12 col-lg-offset-3 col-md-offset-2 col-sm-offset-1 tl-box-main">
      <p>TestLink will carry out a number of checks to see if everything's ready to start the setup.</p>
      <?php
        // Check before DB installation
        $inst_type = isset($_GET['type']) ? $_GET['type'] : '';
        $errors = 0;
        reportCheckingSystem($errors);
        reportCheckingWeb($errors);
        reportCheckingPermissions($errors,$inst_type);
      ?>
    </section>
    <section class="col-lg-6 col-md-8 col-sm-10 col-xs-12 col-lg-offset-3 col-md-offset-2 col-sm-offset-1 tl-box-footer"></section>
    <section class="col-lg-6 col-md-8 col-sm-10 col-xs-12 col-lg-offset-3 col-md-offset-2 col-sm-offset-1 tl-box-main">
    <?php
      if($errors > 0) {
        // Stop process because of error
    ?>
      <p class="text-warning">
        Unfortunately, TestLink scripted setup cannot continue at the moment, due to the above
        <?php echo $errors > 1 ? $errors." " : "" ; ?>
        error<?php echo $errors > 1 ? "s" : "" ; ?>. 
      </p>
      <p class="text-danger">
        Please correct the error<?php echo $errors > 1 ? "s" : "" ; ?>
      </p>
      <p>
	Try again (reload page). If you need help figuring out how to fix the
	problem<?php echo $errors > 1 ? "s" : "" ; ?>, 
        please read Installation manual and visit
        <a href="http://www.testlink.org" target="_blank">TestLink Forums [click here]</a>.
      </p>
    <?php
      } else { // checking OK
    ?>
      <br />
      <form action="installDbInput.php">
        <p><input type="submit" id="submit" value="Continue" class="form-control btn btn-primary" /></p>
      </form>
      <p class="text-success tl-desc text-center">Your system is prepared for TestLink configuration (no fatal problem found).</p>
    <?php 
      } // else end - checking OK 
    ?>
    </section>

<?php
include 'installFooter.inc';
?>
