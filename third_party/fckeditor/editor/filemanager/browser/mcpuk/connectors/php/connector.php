<?php 
/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * File Name: connector.php
 * 	Main connector file, implements the State Pattern to 
 * 	redirect requests to the appropriate class based on 
 * 	the command name passed.
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
//Errors in the config.php could still cause problems.
global $fckphp_config;
require_once "config.php";

error_reporting(E_ALL);
function errorHandler ($errno, $errstr, $errfile, $errline, $errcontext) {
	$reported=false;
	if (strpos($errstr,"var: Deprecated.")===false) {
		global $fckphp_config;
		if ($fckphp_config['Debug']===true && $fckphp_config['Debug_Errors']===true) {
			$oldData=implode("",file($fckphp_config['DebugOutput']));
			if ($fh=fopen($fckphp_config['DebugOutput'],"w")) {
				fwrite($fh,"\n".date("d/m/Y H:i:s")."\n");
				fwrite($fh,"PHP ERROR::: 
						Error Number: $errno
						Error Message: $errstr
						Error File: $errfile
						Error Line: $errline\n");
				if ($fckphp_config['Debug_Trace']) fwrite($fh,"		Error Context: ".print_r($errcontext,true)."\n");
				if ($fckphp_config['Debug_GET']) fwrite($fh,"\n\$_GET::\n".print_r($_GET,true)."\n");
				if ($fckphp_config['Debug_POST']) fwrite($fh,"\n\$_POST::\n".print_r($_POST,true)."\n");
				if ($fckphp_config['Debug_SERVER']) fwrite($fh,"\n\$_SERVER::\n".print_r($_SERVER,true)."\n");
				if ($fckphp_config['Debug_SESSIONS']) fwrite($fh,"\n\$_SESSIONS::\n".print_r($_SESSION,true)."\n");
				fwrite($fh,"\n-------------------------------------------------------\n\n\n");
				fwrite($fh,$oldData); $oldData="";
				fclose($fh);
				$reported=true;
			} 
		}
		
		if (!$reported) {
			//display error instead.
			echo("PHP ERROR::: <br />
					Error Number: $errno <br />
					Error Message: $errstr <br />
					Error File: $errfile <br />
					Error Line: $errline <br />");
				
			if ($fckphp_config['Debug_Trace']) echo "Error Context: ".print_r($errcontext,true)."\n";	
			if ($fckphp_config['Debug_GET']) echo "\$_GET::\n".print_r($_GET,true)."<br />\n";
			if ($fckphp_config['Debug_POST']) echo "\$_POST::\n".print_r($_POST,true)."<br />\n";
			if ($fckphp_config['Debug_SERVER']) echo "\$_SERVER::\n".print_r($_SERVER,true)."<br />\n";
			if ($fckphp_config['Debug_SESSIONS']) echo "\$_SESSIONS::\n".print_r($_SESSION,true)."<br />\n";
			echo "<br />\n<br />\n";
		}
	}
}
set_error_handler('errorHandler');

if (!isset($_SERVER['DOCUMENT_ROOT'])) $_SERVER["DOCUMENT_ROOT"] = $fckphp_config['basedir'];

if ($fckphp_config['Debug']===true && $fckphp_config['Debug_Output']) ob_start();
outputHeaders();

//These are the commands we may expect
$valid_commands=$fckphp_config['Commands'];
$valid_resource_types=$fckphp_config['ResourceTypes'];

//Get the passed data
$command=(
		((isset($_GET['Command']))&&($_GET['Command']!=""))?
			$_GET['Command']:
			""
		);
		
$type=(
		((isset($_GET['Type']))&&($_GET['Type']!=""))?
			$_GET['Type']:
			"File"
		);
		
$cwd=str_replace("..","",
		(
		((isset($_GET['CurrentFolder']))&&($_GET['CurrentFolder']!=""))?
			$_GET['CurrentFolder']:
			"/"
		)
		);
		
$cwd=str_replace("..","",$cwd);

$extra=(
		((isset($_GET['ExtraParams']))&&($_GET['ExtraParams']!=""))?
			$_GET['ExtraParams']:
			""
		);

if (in_array($command,$valid_commands)) {

	if ($fckphp_config['auth']['Req']) {
		require_once "./Auth/".$fckphp_config['auth']['HandlerClass'].".php";
		
		$auth=new Auth();
		$fckphp_config=$auth->authenticate($extra,$fckphp_config);
		if ($fckphp_config['authSuccess']!==true) {
			header ("content-type: text/xml");
			echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
			?>
<Connector command="authentication_failed" resourceType="authentication_failed">
	<CurrentFolder path="authentication_failed" url="authentication_failed" />
	<Error number="-1" />
</Connector><?php
			if ($fckphp_config['Debug']===true  && $fckphp_config['Debug_Output']) recordOutput();
			exit(0);
		}
	}

	//bit of validation
	if (!in_array($type,$valid_resource_types)) {
		echo "Invalid resource type.";
		if ($fckphp_config['Debug']===true  && $fckphp_config['Debug_Output']) recordOutput();
		exit(0);
	}
	
	require_once "Commands/$command.php";

	$action=new $command($fckphp_config,$type,$cwd);

	$action->run();
	if ($fckphp_config['Debug']===true && $fckphp_config['Debug_Output']) recordOutput();
	
} else {
	//No reason for me to be here.
	echo "Invalid command.";
	echo str_replace("\n","<br />",print_r($_GET,true));
	if ($fckphp_config['Debug']===true  && $fckphp_config['Debug_Output']) recordOutput();
	exit(0);
}


function recordOutput() {
	global $fckphp_config;

	if ($fckphp_config['Debug']===true  && $fckphp_config['Debug_Output']) {
		$contents=ob_get_contents();
		if (strlen($contents)>0) {
			$oldData=implode("",file($fckphp_config['DebugOutput']));
			if ($fh=fopen($fckphp_config['DebugOutput'],"w")) {
				fwrite($fh,"\n".date("d/m/Y H:i:s")."\n");
				if ($fckphp_config['Debug_GET']) fwrite($fh,"\n\$_GET::\n".print_r($_GET,true)."\n");
				if ($fckphp_config['Debug_POST']) fwrite($fh,"\n\$_POST::\n".print_r($_POST,true)."\n");
				if ($fckphp_config['Debug_SERVER']) fwrite($fh,"\n\$_SERVER::\n".print_r($_SERVER,true)."\n");
				if ($fckphp_config['Debug_SESSIONS']) fwrite($fh,"\n\$_SESSIONS::\n".print_r($_SESSION,true)."\n");
				fwrite($fh,$contents);
				fwrite($fh,"\n-------------------------------------------------------\n\n\n");
				fwrite($fh,$oldData); $oldData="";
				fclose($fh);
			}
		}
		ob_flush();
	}
}

function outputHeaders() {

	//Anti browser caching headers
	//Borrowed from fatboy's implementation  (fatFCK@code247.com)
	
	// ensure file is never cached
	// Date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	// always modified
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	
	// HTTP/1.0
	header("Pragma: no-cache");
}
?> 
