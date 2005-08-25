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
 * File Name: GetFoldersAndFiles.php
 * 	Implements the GetFoldersAndFiles command, to list
 * 	files and folders in the current directory.
 * 	Output is in XML
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
class GetFoldersAndFiles {
	var $fckphp_config;
	var $type;
	var $cwd;
	var $actual_cwd;
	
	function GetFoldersAndFiles($fckphp_config,$type,$cwd) {
		$this->fckphp_config=$fckphp_config;
		$this->type=$type;
		$this->raw_cwd=$cwd;
		$this->actual_cwd=str_replace("//","/",($fckphp_config['UserFilesPath']."/$type/".$this->raw_cwd));
		$this->real_cwd=str_replace("//","/",($this->fckphp_config['basedir']."/".$this->actual_cwd));
	}
	
	function run() {

		header ("Content-Type: application/xml; charset=utf-8");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		?>
<!DOCTYPE Connector [

<?php include "dtd/iso-lat1.ent";?>
	
	<!ELEMENT Connector	(CurrentFolder,Folders,Files)>
		<!ATTLIST Connector command CDATA "noname">
		<!ATTLIST Connector resourceType CDATA "0">
		
	<!ELEMENT CurrentFolder	(#PCDATA)>
		<!ATTLIST CurrentFolder path CDATA "noname">
		<!ATTLIST CurrentFolder url CDATA "0">
		
	<!ELEMENT Folders	(#PCDATA)>
	
	<!ELEMENT Folder	(#PCDATA)>
		<!ATTLIST Folder name CDATA "noname_dir">
		
	<!ELEMENT Files		(#PCDATA)>
		
	<!ELEMENT File		(#PCDATA)>
		<!ATTLIST File name CDATA "noname_file">
		<!ATTLIST File size CDATA "0">
] >
		
<Connector command="GetFoldersAndFiles" resourceType="<?php echo $this->type; ?>">
	<CurrentFolder path="<?php echo $this->raw_cwd; ?>" url="<?php echo $this->fckphp_config['urlprefix'] . $this->actual_cwd; ?>" />
	<Folders>
<?php
			$files=array();
			if ($dh=opendir($this->real_cwd)) {
				while (($filename=readdir($dh))!==false) {
					
					if (($filename!=".")&&($filename!="..")) {
						if (is_dir($this->real_cwd."/$filename")) {
							//check if$fckphp_configured not to show this folder
							$hide=false;
							for($i=0;$i<sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders']);$i++) 
								$hide=(ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFolders'][$i],$filename)?true:$hide);
							
							if (!$hide) echo "\t\t<Folder name=\"$filename\" />\n";
							
						} else {
							array_push($files,$filename);
						}
					}
				}
				closedir($dh); 
			}
			echo "\t</Folders>\n";
			echo "\t<Files>\n";

			for ($i=0;$i<sizeof($files);$i++) {
				
				$lastdot=strrpos($files[$i],".");
				$ext=(($lastdot!==false)?(substr($files[$i],$lastdot+1)):"");
				
				if (in_array(strtolower($ext),$this->fckphp_config['ResourceAreas'][$this->type]['AllowedExtensions'])) {
				
					//check if$fckphp_configured not to show this file
					$editable=$hide=false;
					for($j=0;$j<sizeof($this->fckphp_config['ResourceAreas'][$this->type]['HideFiles']);$j++) 
						$hide=(ereg($this->fckphp_config['ResourceAreas'][$this->type]['HideFiles'][$j],$files[$i])?true:$hide);
					
					if (!$hide) {
						if ($this->fckphp_config['ResourceAreas'][$this->type]['AllowImageEditing']) 
							$editable=$this->isImageEditable($this->real_cwd."/".$files[$i]);
						
						echo "\t\t<File name=\"".htmlentities($files[$i])."\" size=\"".ceil(filesize($this->real_cwd."/".$files[$i])/1024)."\" editable=\"" . ( $editable?"1":"0" ) . "\" />\n";	
					}

				}
				
			}
		
			echo "\t</Files>\n"; 
			echo "</Connector>\n";
	}
	
	
	function isImageEditable($file) {
		$fh=fopen($file,"r");
		if ($fh) {
			$start4=fread($fh,4);
			fclose($fh);
			
			$start3=substr($start4,0,3);
			
			if ($start4=="\x89PNG") { //PNG
				return (function_exists("imagecreatefrompng") && function_exists("imagepng"));
				
			} elseif ($start3=="GIF") { //GIF
				return (function_exists("imagecreatefromgif") && function_exists("imagegif"));
				
			} elseif ($start3=="\xFF\xD8\xFF") { //JPEG
				return (function_exists("imagecreatefromjpeg")&& function_exists("imagejpeg"));
				
			} elseif ($start4=="hsi1") { //JPEG
				return (function_exists("imagecreatefromjpeg")&& function_exists("imagejpeg"));
				
			} else {
				return false;
			}
			
		} else {
			return false;
		}
	}
	
	
}

?>