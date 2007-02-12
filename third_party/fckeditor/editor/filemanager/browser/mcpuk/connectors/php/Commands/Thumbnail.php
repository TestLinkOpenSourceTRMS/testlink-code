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
 * File Name: Thumbnail.php
 * 	Implements the Thumbnail command, to return
 * 	a thumbnail to the browser for the sent file,
 * 	if the file is an image an attempt is made to
 * 	generate a thumbnail, otherwise an appropriate
 * 	icon is returned.
 * 	Output is image data
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
include "helpers/iconlookup.php";

class Thumbnail {
	var $fckphp_config;
	var $type;
	var $cwd;
	var $actual_cwd;
	var $filename;
	
	function Thumbnail($fckphp_config,$type,$cwd) {
		$this->fckphp_config=$fckphp_config;
		$this->type=$type;
		$this->raw_cwd=$cwd;
		$this->actual_cwd=str_replace("//","/",($fckphp_config['UserFilesPath']."/$type/".$this->raw_cwd));
		$this->real_cwd=str_replace("//","/",($this->fckphp_config['basedir']."/".$this->actual_cwd));
		$this->filename=str_replace(array("..","/"),"",$_GET['FileName']);
	}
	
	function run() {
		//$mimeIcon=getMimeIcon($mime);
		$fullfile=$this->real_cwd.'/'.$this->filename;
		$thumbfile=$this->real_cwd.'/.thumb_'.$this->filename;
		$icon=false;
		
		if (file_exists($thumbfile)) {
			$icon=$thumbfile;
		} else {
			$mime=$this->getMIME($fullfile);
			$ext=strtolower($this->getExtension($this->filename));	
			
			if ($this->isImage($mime,$ext))
				{
				
				//Try and find a thumbnail, else try to generate one
				//	else send generic picture icon.
				
				if ($this->isJPEG($mime,$ext)) {
					$result=$this->resizeFromJPEG($fullfile);
					
				} elseif ($this->isGIF($mime,$ext)) {
					$result=$this->resizeFromGIF($fullfile);
					
				} elseif ($this->isPNG($mime,$ext)) {
					$result=$this->resizeFromPNG($fullfile);
				}
				
				if ($result!==false) {
					if (function_exists("imagejpeg")) {
						imagejpeg($result,$thumbfile,70);
						chmod($thumbfile,0777);
						$icon=$thumbfile;
					} elseif (function_exists("imagepng")) {
						imagepng($result,$thumbfile);
						chmod($thumbfile,0777);
						$icon=$thumbfile;
					} elseif (function_exists("imagegif")) {
						imagegif($result,$thumbfile);
						chmod($thumbfile,0777);
						$icon=$thumbfile;
					} else {
						$icon=iconLookup($mime,$ext);
					}
					
				} else {
					$icon=iconLookup($mime,$ext);
				}
			} else {
				$icon=iconLookup($mime,$ext);
			}
		}
		
		
		$iconMime=$this->image2MIME($icon);
		if ($iconMime==false) $iconMime="image/jpeg";
		
		header("Content-type: $iconMime",true);
		readfile($icon);
		
	}
	
	function getMIME($file) {
		$mime="text/plain";
		
		//If mime magic is installed
		if (function_exists("mime_content_type")) {
			$mime=mime_content_type($file);
		} else {
			$mime=$this->image2MIME($file);
		}
		
		return strtolower($mime);
	}
	
	function image2MIME($file) {
		$fh=fopen($file,"r");
		if ($fh) {
			$start4=fread($fh,4);
			$start3=substr($start4,0,3);
			
			if ($start4=="\x89PNG") {
				return "image/png";
			} elseif ($start3=="GIF") {
				return "image/gif";
			} elseif ($start3=="\xFF\xD8\xFF") {
				return "image/jpeg";
			} elseif ($start4=="hsi1") {
				return "image/jpeg";
			} else {
				return false;
			}
			
			unset($start3);unset($start4);
			fclose($fh);
		} else {
			return false;
		}
	}
	
	
	function isImage($mime,$ext) {
		if (
			($mime=="image/gif")||
			($mime=="image/jpeg")||
			($mime=="image/jpg")||
			($mime=="image/pjpeg")||
			($mime=="image/png")||
			($ext=="jpg")||
			($ext=="jpeg")||
			($ext=="png")||
			($ext=="gif") ) {
		
			return true;
		} else {
			return false;
		}
	}
	
	function isJPEG($mime,$ext) {
		if (($mime=="image/jpeg")||($mime=="image/jpg")||($mime=="image/pjpeg")||($ext=="jpg")||($ext=="jpeg")) {
			return true; 
		} else {
			return false;
		}
	}

	function isGIF($mime,$ext) {
		if (($mime=="image/gif")||($ext=="gif")) {
			return true; 
		} else {
			return false;
		}
	}
	
	function isPNG($mime,$ext) {
		if (($mime=="image/png")||($ext=="png")) {
			return true; 
		} else {
			return false;
		}
	}	
	
	function getExtension($filename) {
		//Get Extension
		$ext=""; 
		$lastpos=strrpos($this->filename,'.'); 
		if ($lastpos!==false) $ext=substr($this->filename,($lastpos+1));
		return strtolower($ext);
	}
	
	function resizeFromJPEG($file) {
		if (function_exists("imagecreatefromjpeg")) {
			$img=@imagecreatefromjpeg($this->real_cwd.'/'.$this->filename);
			return (($img)?$this->resizeImage($img):false);
		} else { return false; }
	}
	
	function resizeFromGIF($file) {
		if (function_exists("imagecreatefromgif")) {
			$img=@imagecreatefromgif($this->real_cwd.'/'.$this->filename);
			return (($img)?$this->resizeImage($img):false);
		} else { return false; }
	}
	
	function resizeFromPNG($file) {
		if (function_exists("imagecreatefrompng")) {
			$img=@imagecreatefrompng($this->real_cwd.'/'.$this->filename);
			return (($img)?$this->resizeImage($img):false);
		} else { return false; }
	}
	
	function resizeImage($img) {
		//Get size for thumbnail
		$width=imagesx($img); $height=imagesy($img);
		if ($width>$height) { $n_height=$height*(96/$width); $n_width=96; } else { $n_width=$width*(96/$height); $n_height=96; }
		
		$x=0;$y=0;
		if ($n_width<96) $x=round((96-$n_width)/2);
		if ($n_height<96) $y=round((96-$n_height)/2);
		
		$thumb=imagecreatetruecolor(96,96);
		
		#Background colour fix by:
		#Ben Lancaster (benlanc@ster.me.uk)
		$bgcolor = imagecolorallocate($thumb,255,255,255);
		imagefill($thumb, 0, 0, $bgcolor);
		
		if (function_exists("imagecopyresampled")) {
			if (!($result=@imagecopyresampled($thumb,$img,$x,$y,0,0,$n_width,$n_height,$width,$height))) {
				$result=imagecopyresized($thumb,$img,$x,$y,0,0,$n_width,$n_height,$width,$height);
			}	
		} else {
			$result=imagecopyresized($thumb,$img,$x,$y,0,0,$n_width,$n_height,$width,$height);
		}

		return ($result)?$thumb:false;
	}
}

?>