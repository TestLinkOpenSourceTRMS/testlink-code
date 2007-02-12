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
 * File Name: RenameFolder.php
 * 	Implements the DeleteFile command to delete a file
 * 	in the current directory. Output is in XML
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
class RenameFolder {
	var $fckphp_config;
	var $type;
	var $cwd;
	var $actual_cwd;
	var $newfolder;
	
	function RenameFolder($fckphp_config,$type,$cwd) {
		$this->fckphp_config=$fckphp_config;
		$this->type=$type;
		$this->raw_cwd=$cwd;
		$this->actual_cwd=str_replace("//","/",($fckphp_config['UserFilesPath']."/$type/".$this->raw_cwd));
		$this->real_cwd=str_replace("//","/",($this->fckphp_config['basedir']."/".$this->actual_cwd));
		$this->foldername=str_replace(array("..","/"),"",$_GET['FolderName']);
		$this->newname=str_replace(array("..","/"),"",$this->checkName($_GET['NewName']));
	}
	
	function checkName($name) {
		$newName="";
		for ($i=0;$i<strlen($name);$i++) {
			if (in_array($name[$i],$this->fckphp_config['DirNameAllowedChars'])) $newName.=$name[$i];
		}
		return $newName;
	}
	
	function run() {
		$result1=false;

		
		if ($this->newname!='') {
			$result1=rename($this->real_cwd.'/'.$this->foldername,$this->real_cwd.'/'.$this->newname);
		}
		
		header ("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		?>
<Connector command="RenameFolder" resourceType="<?php echo $this->type; ?>">
	<CurrentFolder path="<?php echo $this->raw_cwd; ?>" url="<?php echo $this->actual_cwd; ?>" />
	<?php
		if ($result1) {
			$err_no=0;
		} else {
			$err_no=602;
		}
		
	?>
	<Error number="<?php echo "".$err_no; ?>" />
</Connector>
		<?php
	}
}

?>