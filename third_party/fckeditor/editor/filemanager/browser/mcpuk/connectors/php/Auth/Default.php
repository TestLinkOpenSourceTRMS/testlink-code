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
 * File Name: Default.php
 * 	Im not very clued up on authentication but even i can see that anyone 
 * 	who can spoof an IP could perform a replay attack on this, but its 
 * 	better than nothing. 
 * 	There is a 1 hour time out on tokens to help this slightly.
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
class Auth {
	
	function authenticate($data,$fckphp_config) {

		//Hold relevant$fckphp_config vars locally
		$key=$fckphp_config['auth']['Handler']['SharedKey'];
		$fckphp_config['authSuccess']=false;
		
		//Decrypt the data passed to us
		$decData="";
		for ($i=0;$i<strlen($data)-1;$i+=2) $decData.=chr(hexdec($data[$i].$data[$i+1]));
		
		$decArray=explode("|^SEP^|",$decData);
		
		if (sizeof($decArray)==4) {
			//0 = Timestamp
			//1 = Client IP
			//2 = Username
			//3 = MD5
			if ($decArray[3]==md5($decArray[0]."|^SEP^|".$decArray[1]."|^SEP^|".$decArray[2].$key)) {
				if (time()-$decArray[0]<3600) { //Token valid for max of 1 hour
					if ($_SERVER['REMOTE_ADDR']==$decArray[1]) {
						
						//Set the file root to the users individual one
						$top=str_replace("//","/",$fckphp_config['basedir'].'/'.$fckphp_config['UserFilesPath']."/users");
						$fckphp_config['UserFilesPath']=$fckphp_config['UserFilesPath']."/users/".$decArray[2];
						$up=str_replace("//","/",$fckphp_config['basedir'].'/'.$fckphp_config['UserFilesPath']);
						
						if (!file_exists($top)) {
							mkdir($top,0777) or die("users folder in UserFilesPath does not exist and could not be created.");
							chmod($top,0777);
						}
						
						//Create folder if it doesnt exist
						if (!file_exists($up)) {
							mkdir($up,0777) or die("users/".$decArray[2]." folder in UserFilesPath does not exist and could not be created.");
							chmod($up,0777); //Just for good measure
						}
						
						//Create resource area subfolders if they dont exist
						foreach ($fckphp_config['ResourceTypes'] as $value) {
							if (!file_exists("$up/$value")) {
								mkdir("$up/$value",0777) or die("users/".$decArray[2]."/$value folder in UserFilesPath does not exist and could not be created.");
								chmod("$up/$value",0777); //Just for good measure
							}
						}
						$fckphp_config['authSuccess']=true;
					} else {
						//Not same client as auth token is for
					}
				} else {
					//Token more than an hour old
				}
			} else {
				//Data integrity failed
			}
		} else {
			//Not enough data (decryption failed?)
		}
		
		return $fckphp_config;
	}
}
?>