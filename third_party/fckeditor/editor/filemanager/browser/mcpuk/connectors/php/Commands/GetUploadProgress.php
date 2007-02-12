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
 * File Name: GetUploadProgress.php
 * 	Implements the GetFolders command, to list the folders 
 * 	in the current directory. Output is in XML
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
class GetUploadProgress {
	var $fckphp_config;
	var $type;
	var $cwd;
	var $actual_cwd;
	var $uploadID;
	
	function GetUploadProgress($fckphp_config,$type,$cwd) {
		$this->fckphp_config=$fckphp_config;
		$this->type=$type;
		$this->raw_cwd=$cwd;
		$this->actual_cwd=str_replace("//","/",($fckphp_config['UserFilesPath']."/$type/".$this->raw_cwd));
		$this->real_cwd=str_replace("//","/",($this->fckphp_config['basedir']."/".$this->actual_cwd));
		$this->uploadID=$_GET['uploadID'];
		$this->refreshURL=$_GET['refreshURL'];
		
	}
	
	function run() {
		if (isset($this->refreshURL)&&($this->refreshURL!="")) {
			//Continue monitoring
			$uploadProgress=file($this->refreshURL);
			$url=$this->refreshURL;
		} else {
			//New download
			$uploadProgressHandler=$this->fckphp_config['uploadProgressHandler'];
			if ($uploadProgressHandler=='') {
				//Progresshandler not specified, return generic response
		?>
<Connector command="GetUploadProgress" resourceType="<?php echo $this->type; ?>">
	<CurrentFolder path="<?php echo $this->raw_cwd; ?>" url="<?php echo $this->actual_cwd; ?>" />
	<Progress max="2" value="1" />
	<RefreshURL url="" />
</Connector>
		<?php
				exit(0);
			}
			
			$url=$uploadProgressHandler."?iTotal=0&iRead=0&iStatus=1&sessionid=".$this->uploadID."&dtnow=".time()."&dtstart=".time();
			
			$_SESSION[$this->uploadID]=$url;
			$uploadProgress=file($url);
			
		}
		
		$uploadProgress2=implode("\n",$uploadProgress);
		
		$parser = xml_parser_create();
		xml_parse_into_struct($parser, $uploadProgress2, $vals, $index);
		
		$refreshURL=isset($vals[$index['REFRESHURL'][0]]['value'])?$vals[$index['REFRESHURL'][0]]['value']:"";
		$totalBytes=isset($vals[$index['TOTALBYTES'][0]]['value'])?$vals[$index['TOTALBYTES'][0]]['value']:0;
		$readBytes=isset($vals[$index['READBYTES'][0]]['value'])?$vals[$index['READBYTES'][0]]['value']:0;
		$status=isset($vals[$index['STATUS'][0]]['value'])?$vals[$index['STATUS'][0]]['value']:1;
		
		header ("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		?>
<Connector command="GetUploadProgress" resourceType="<?php echo $this->type; ?>">
	<CurrentFolder path="<?php echo $this->raw_cwd; ?>" url="<?php echo $this->actual_cwd; ?>" />
	<Progress max="<?php echo $totalBytes; ?>" value="<?php echo $readBytes; ?>" />
	<RefreshURL url="<?php echo htmlentities($refreshURL); ?>" />
</Connector>
		<?php
		xml_parser_free($parser);
	}
}

?>