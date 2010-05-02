<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Basic description of steps and license confirmation
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: installIntro.php,v 1.2 2010/05/02 14:30:13 franciscom Exp $
 *
 * @internal Revisions:
 * 	20100502 - franciscom - BUGID 3411
 */
session_start();

$inst_phase = 'license';
$inst_type = isset($_SESSION['installationType']) ? $_SESSION['installationType'] : 'new';
$_SESSION['title'] = "TestLink {$_SESSION['testlink_version']} ";

if (isset($_GET['type']))
{
	$_SESSION['installation_type'] = $_GET['type'];
	
	switch($_SESSION['installation_type'])
	{
		case 'new':
			$_SESSION['title'] .= " - New installation"; 
			$_SESSION['isNew'] = TRUE; 
		break;
		
		case 'upgrade_1.8_to_1.9':
		default:
			$_SESSION['title'] .= " - Upgrade"; 
			$_SESSION['isNew'] = FALSE; 
		break;
	
	} 
}
else
{
	header( 'Location: index.php' );
	exit;
}

include 'installHead.inc';
?>
<div class="tlStory">
<p><b>TestLink</b> is developed and shared under GPL license. You are welcome to share your changes
with community. Please, confirm your understanding below.</p>

<div class="tlBox" style="height: 300px;">
	<b>The GNU General Public License is a Free Software license. </b>
	<p />
	Like any Free Software license, it grants to you the four following freedoms:<p />
	<ul style="text-align: justify;">
	<li>The freedom to run the program for any purpose. </li>
	<li>The freedom to study how the program works and adapt it to your needs. </li>
	<li>The freedom to redistribute copies so you can help your neighbor. </li>
	<li>The freedom to improve the program and release your improvements to the public, so that the whole community benefits. </li>
	</ul>
	<p />
	
	You may exercise the freedoms specified here provided that you comply with the express conditions of this license. The principal conditions are:<p />
	<ul style="text-align: justify;">
	<li>You must conspicuously and appropriately publish on each copy distributed an appropriate copyright notice and disclaimer of warranty and keep intact all the notices that refer to this License and to the absence of any warranty; and give any other recipients of the Program a copy of the GNU General Public License along with the Program. Any translation of the GNU General Public License must be accompanied by the GNU General Public License. </li>
	<li>If you modify your copy or copies of the program or any portion of it, or develop a program based upon it, you may distribute the resulting work provided you do so under the GNU General Public License. Any translation of the GNU General Public License must be accompanied by the GNU General Public License. </li>
	<li>If you copy or distribute the program, you must accompany it with the complete corresponding machine-readable source code or with a written offer, valid for at least three years, to furnish the complete corresponding machine-readable source code. </li>
	<li>Any of these conditions can be waived if you get permission from the copyright holder.</li>
	<li>Your fair use and other rights are in no way affected by the above.</li>
	</ul>
	<p />
	The above is a summary of the  GNU General Public License. By proceeding, you are agreeing to the GNU General Public Licence, not the above. The above is simply a summary of the GNU General Public Licence, and it's accuracy is not guaranteed. It is strongly recommended you read the <a href="http://www.gnu.org/copyleft/gpl.html" target="_blank">GNU General Public License</a> in full before proceeding, which can also be found in the LICENCE file distributed with TestLink.
</div>

<script>
function ableButton() {
	check = document.getElementById("licenseOK");
	button = document.getElementById("submit");
	
	if(check.checked==true) {
		button.disabled = false;	
	} else {
		button.disabled = true;	
	}
}
</script>
<p>
  	<form action="installCheck.php">
	<div style="float:right;"><input type="submit" id="submit" value="Continue" 
			disabled="disabled" /></div>
	<div><input type="checkbox" id="licenseOK" name="licenseOK" onClick="ableButton()" />
	<label for="licenseOK">I agree to the terms set out in this license.</label>
	</div></form>
<p>

</div>
<?php include 'installFooter.inc'; ?>