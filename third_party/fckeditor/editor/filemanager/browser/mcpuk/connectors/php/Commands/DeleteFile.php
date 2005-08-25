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
 * File Name: DeleteFile.php
 * 	Implements the DeleteFile command to delete a file
 * 	in the current directory. Output is in XML.
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
class DeleteFile {
	var $fckphp_config;
	var $type;
	var $cwd;
	var $actual_cwd;
	var $newfolder;
	
	function DeleteFile($fckphp_config,$type,$cwd) {
		$this->fckphp_config=$fckphp_config;
		$this->type=$type;
		$this->raw_cwd=$cwd;
		$this->actual_cwd=str_replace("//","/",($this->fckphp_config['UserFilesPath']."/$type/".$this->raw_cwd));
		$this->real_cwd=str_replace("//","/",($this->fckphp_config['basedir']."/".$this->actual_cwd));
		$this->filename=str_replace(array("..","/"),"",$_GET['FileName']);
	}
	
	function run() {
		$result1=false;
		$result2=true;
		
		$thumb=$this->real_cwd.'/.thumb_'.$this->filename;
		$result1=unlink($this->real_cwd.'/'.$this->filename);
		if (file_exists($thumb)) $result2=unlink($thumb);
		
		header ("content-type: text/xml");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		?>
<Connector command="DeleteFile" resourceType="<?php echo $this->type; ?>">
	<CurrentFolder path="<?php echo $this->raw_cwd; ?>" url="<?php echo $this->actual_cwd; ?>" />
	<?php
		if ($result1&&$result2) {
			$err_no=0;
		} else {
			$err_no=302;
		}
		
	?>
	<Error number="<?php echo "".$err_no; ?>" />
</Connector>
		<?php
	}
}

?>