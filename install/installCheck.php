<?php 
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Verify environment
 * Note: informations are passed via $_SESSION
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: installCheck.php,v 1.3.2.1 2010/11/20 10:37:38 franciscom Exp $
 *
 * @internal Revisions:
 * 20101120 - franciscom - BUGID 4030 - fixed Typo error
 * 20080914 - franciscom - check_php_resource_settings() 
 * 20080219 - franciscom - fixed dir permission checking
 * 
 **/

//require_once("installUtils.php");
require_once('..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'functions'.DIRECTORY_SEPARATOR.'configCheck.php');

if( !isset($_SESSION) )
{ 
  session_start();
}

$inst_phase = 'checking';  // global variable -> absolutely wrong use as usual, used on installHead.inc	
$msg='';
include 'installHead.inc';
?>
<div class="tlStory">

<p>TestLink will carry out a number of checks to see if everything's ready to start 
	the setup.</p>
<table>

<?php
// Check before DB installation
$inst_type = $_GET['type']; // BUGID 4030
$errors = 0;
reportCheckingSystem($errors);
reportCheckingWeb($errors);
reportCheckingPermissions($errors,$inst_type);
?>
	</table>
</div>
<div class="tlLiner">&nbsp;</div>
<div class="tlStory">

<?php if($errors > 0) {
	// Stop process because of error
?>
	<p>Unfortunately, TestLink scripted setup cannot continue at the moment, due to the above 
	<?php echo $errors > 1 ? $errors." " : "" ; ?>error<?php echo $errors > 1 ? "s" : "" ; ?>. 
	<br />Please correct the error<?php echo $errors > 1 ? "s" : "" ; ?>, 
	and try again (reload page). If you need help figuring out how to fix the 
	problem<?php echo $errors > 1 ? "s" : "" ; ?>, please read Installation manual and
	visit <a href="http://www.teamst.org" target="_blank">TestLink Forums [click here]</a>.
	</p>
</div>
<?php

} else { // checking OK
?>
  	
	<div style="float:right;"><form action="installDbInput.php">
		<input type="submit" id="submit" value="Continue" />
	</form></div>
	<div>
	<p class="success">Your system is prepared for TestLink configuration (no fatal problem found).</p>
	</div>
</div>
<?php 
} // else end - checking OK 

include 'installFooter.inc';
?>