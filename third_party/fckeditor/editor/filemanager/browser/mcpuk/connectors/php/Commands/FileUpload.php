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
 * File Name: FileUpload.php
 * 	Implements the FileUpload command,
 * 	Checks the file uploaded is allowed, 
 * 	then moves it to the user data area. 
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
class FileUpload {
	var $fckphp_config;
	var $type;
	var $cwd;
	var $actual_cwd;
	var $newfolder;
	
	function FileUpload($fckphp_config,$type,$cwd) {
		$this->fckphp_config=$fckphp_config;
		$this->type=$type;
		$this->raw_cwd=$cwd;
		$this->actual_cwd=str_replace("//","/",($this->fckphp_config['UserFilesPath']."/$type/".$this->raw_cwd));
		$this->real_cwd=str_replace("//","/",($this->fckphp_config['basedir']."/".$this->actual_cwd));
	}
	
	function cleanFilename($filename) {
		$n_filename="";
		
		//Check that it only contains valid characters
		for($i=0;$i<strlen($filename);$i++) if (in_array(substr($filename,$i,1),$this->fckphp_config['FileNameAllowedChars'])) $n_filename.=substr($filename,$i,1);
		
		//If it got this far all is ok
		return $n_filename;
	}
	
	function run() {
		//If using CGI Upload script, get file info and insert into $_FILE array
		if 	(
				(sizeof($_FILES)==0) && 
				isset($_GET['file']) && 
				isset($_GET['file']['NewFile']) && 
				is_array($_GET['file']['NewFile'])
			) {
			if (isset($_GET['file']['NewFile']['name'])&&$_GET['file']['NewFile']['size']&&$_GET['file']['NewFile']['tmp_name']) {
				$_FILES['NewFile']['name']=basename(str_replace("\\","/",$_GET['file']['NewFile']['name']));
				$_FILES['NewFile']['size']=$_GET['file']['NewFile']['size'];
				$_FILES['NewFile']['tmp_name']=$_GET['file']['NewFile']['tmp_name'];
			} else {
				$disp="202,'Incomplete file information from upload CGI'";
			}
		}
		
// 		if (isset($_FILES['NewFile'])&&isset($_FILES['NewFile']['name'])&&($_FILES['NewFile']['name']!=""))
// 			$_FILES['NewFile']['name']=$_FILES['NewFile']['name']; //$this->cleanFilename($_FILES['NewFile']['name']);
		
		$typeconfig=$this->fckphp_config['ResourceAreas'][$this->type];
		
		header ("content-type: text/html");
		if (sizeof($_FILES)>0) {
			if (array_key_exists("NewFile",$_FILES)) {
				if ($_FILES['NewFile']['size']<($typeconfig['MaxSize']*1024)) {

					$filename=basename(str_replace("\\","/",$_FILES['NewFile']['name']));
					
					$lastdot=strrpos($filename,".");
					
					if ($lastdot!==false) {
						$ext=substr($filename,($lastdot+1));
						$filename=substr($filename,0,$lastdot);
						
						if (in_array(strtolower($ext),$typeconfig['AllowedExtensions'])) {
						
							$test=0;
							$dirSizes=array();
							$globalSize=0;
							$failSizeCheck=false;
							if ($this->fckphp_config['DiskQuota']['Global']!=-1) {
								foreach ($this->fckphp_config['ResourceTypes'] as $resType) {
									
									$dirSizes[$resType]=
										$this->getDirSize(
											$this->fckphp_config['basedir']."/".$this->fckphp_config['UserFilesPath']."/$resType");
									
									if ($dirSizes[$resType]===false) {
										//Failed to stat a directory, fall out
										$failSizeCheck=true;
										$msg="\\nUnable to determine the size of a folder.";
										break;
									}
									$globalSize+=$dirSizes[$resType];
								}
								
								$globalSize+=$_FILES['NewFile']['size'];
								
								if (!$failSizeCheck) {
									if ($globalSize>($this->fckphp_config['DiskQuota']['Global']*1048576)) {
										$failSizeCheck=true;
										$msg="\\nYou are over the global disk quota.";
									}
								}
							}
							
							if (($typeconfig['DiskQuota']!=-1)&&(!$failSizeCheck)) {
								if ($this->fckphp_config['DiskQuota']['Global']==-1) {
									$dirSizes[$this->type]=
										$this->getDirSize(
											$this->fckphp_config['basedir']."/".$this->fckphp_config['UserFilesPath']."/".$this->type);
								}
								
								if (($dirSizes[$this->type]+$_FILES['NewFile']['size'])>
									($typeconfig['DiskQuota']*1048576)) {
									$failSizeCheck=true;	
									$msg="\\nYou are over the disk quota for this resource type.";
								}
							}
							
							if ((($this->fckphp_config['DiskQuota']['Global']!=-1)||($typeconfig['DiskQuota']!=-1))&&$failSizeCheck) {
								//Disk Quota over
								$disp="202,'Over disk quota, ".$msg."'";
							} else {
						
								if (file_exists($this->real_cwd."/$filename.$ext")) {
									$taskDone=false;
									
									//File already exists, try renaming
									//If there are more than 200 files with
									//	the same name giveup
									for ($i=1;(($i<200)&&($taskDone==false));$i++) {
										if (!file_exists($this->real_cwd."/$filename($i).$ext")) {
											if (is_uploaded_file($_FILES['NewFile']['tmp_name'])) {
												if 
												(move_uploaded_file($_FILES['NewFile']['tmp_name'],($this->real_cwd."/$filename($i).$ext"))) {
													chmod(($this->real_cwd."/$filename($i).$ext"),0777);
													$disp="201,'..$filename($i).$ext'";
												} else {
													$disp="202,'Failed to upload file, internal error.'";
												}
											} else {
												if 
												(rename($_FILES['NewFile']['tmp_name'],($this->real_cwd."/$filename($i).$ext"))) {
													chmod(($this->real_cwd."/$filename($i).$ext"),0777);
													$disp="201,'$filename($i).$ext'";
												} else {
													$disp="202,'Failed to upload file, internal error.'";
												}
											}
											$taskDone=true;	
										}
									}
									if ($taskDone==false) {
										$disp="202,'Failed to upload file, internal error..'";
									}
								} else {
									//Upload file
									if (is_uploaded_file($_FILES['NewFile']['tmp_name'])) {
										if (move_uploaded_file($_FILES['NewFile']['tmp_name'],($this->real_cwd."/$filename.$ext"))) {
											chmod(($this->real_cwd."/$filename.$ext"),0777);
											$disp="0";
										} else {
											$disp="202,'Failed to upload file, internal error...'";
										}
									} else {
										if (rename($_FILES['NewFile']['tmp_name'],($this->real_cwd."/$filename.$ext"))) {
											chmod(($this->real_cwd."/$filename.$ext"),0777);
											$disp="0";
										} else {
											$disp="202,'Failed to upload file, internal error...'";
										}
									}
								}
							}
						} else {
							//Disallowed file extension
							$disp="202,'Disallowed file type.'";
						}
						
					} else {
						//No file extension to check
						$disp="202,'Unable to determine file type of file'";
					}	
					
				} else {
					//Too big
					$disp="202,'This file exceeds the maximum upload size.'";
				}
			} else {
				//No file uploaded with field name NewFile
				$disp="202,'Unable to find uploaded file.'";
			}
		} else {
			//No files uploaded
			
			//Should really send something back saying
			//invalid file, but this breaks the filemanager 
			//with firefox, so for now we'll just exit
			exit(0);
			//$disp="202";
		}

		?>
		<html>
		<head>
			<title>Upload Complete</title>
		</head>
		<body>
		<script type="text/javascript">
			window.parent.frames['frmUpload'].OnUploadCompleted(<?php echo $disp; ?>) ;
		</script>
		</body>
		</html>
		<?php
		
	}
	
	function getDirSize($dir) {
		$dirSize=0;
		if ($dh=@opendir($dir)) {
			while ($file=@readdir($dh)) {
				if (($file!=".")&&($file!="..")) {
					if (is_dir($dir."/".$file)) {
						$tmp_dirSize=$this->getDirSize($dir."/".$file);
						if ($tmp_dirSize!==false) $dirSize+=$tmp_dirSize;
					} else {
						$dirSize+=filesize($dir."/".$file);
					}
				}
			}
			@closedir($dh);
		} else {
			return false;
		}
		
		return $dirSize;
	}
}

?>