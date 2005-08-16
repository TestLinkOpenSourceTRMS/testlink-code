<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: license.php,v 1.2 2005/08/16 17:59:48 franciscom Exp $ */
session_start();
foreach($_POST as $key => $val) {
	$_SESSION[$key] = $val;
}

// 20050808 - fm
$inst_type = $_SESSION['installationType'];


//print_r($_POST); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>TestLink &raquo; Install</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <style type="text/css">
             @import url('./css/style.css');
			 
		 ul li { margin-top: 7px; }
        </style>
</head>	

<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;TestLink</span></td>
    <td align="right"><span class="headers">Installation - <?php echo $inst_type; ?></span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2"><table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">

<p>
Usage of this software is subject to the GPL license. To help you understand what the GPL licence is and how it affects your ability to use the software, we have provided the following summary:
</p>

<p>
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
</p>
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
		</td>
      </tr>
    </table></td>
  </tr>
  <tr class="fancyRow2">
  	<form action="installNewDB.php" method="post">
		<td class="border-top-bottom" style="padding:0px"><input type="checkbox" id="licenseOK" name="licenseOK" onClick="ableButton()" /><label for="licenseOK">I agree to the terms set out in this license.</label></td>
		<td class="border-top-bottom smallText" align="right" style="padding:0px"><input type="submit" id="submit" value="Proceed" disabled="disabled" class="button" style="border: 0px;" /></td>
	</form>
  </tr>
</table>
</body>
</html>