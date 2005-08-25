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
 * File Name: GetFolders.php
 * 	Implements the GetFolders command, to list the folders 
 * 	in the current directory. Output is in XML
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
class GetFolders {
	var $fckphp_config;
	var $type;
	var $cwd;
	var $actual_cwd;
	
	function GetFolders($fckphp_config,$type,$cwd) {
		$this->fckphp_config=$fckphp_config;
		$this->type=$type;
		$this->raw_cwd=$cwd;
		$this->actual_cwd=str_replace("//","/",($fckphp_config['UserFilesPath']."/$type/".$this->raw_cwd));
		$this->real_cwd=str_replace("//","/",($this->fckphp_config['basedir']."/".$this->actual_cwd));
	}
	
	function run() {
		header ("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		?>
<Connector command="GetFolders" resourceType="<?php echo $this->type; ?>">
	<CurrentFolder path="<?php echo $this->raw_cwd; ?>" url="<?php echo $this->actual_cwd; ?>" />
	<Folders>
		<?php
			if ($dh=opendir($this->real_cwd)) {
				while (($filename=readdir($dh))!==false) {
					if (($filename!=".")&&($filename!="..")) {
						if (is_dir($this->real_cwd."/$filename")) {
							
							//check if$fckphp_configured not to show this folder
							$hide=false;
							for($i=0;$i<sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders']);$i++) 
								$hide=(ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders'][$i],$filename)?true:$hide);
							
							if (!$hide) echo "<Folder name=\"$filename\" />\n";
						}
					}
				}
				closedir($dh);
			}
		?>
	</Folders>
</Connector>
		<?php
	}
}

?>