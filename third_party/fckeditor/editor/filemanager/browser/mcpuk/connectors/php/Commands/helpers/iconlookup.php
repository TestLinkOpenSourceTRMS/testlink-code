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
 * File Name: iconlookup.php
 * 	(!)
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
function iconLookup($mime,$ext) {

	$mimeIcons=array(
			"image"=>"image.jpg",
			"audio"=>"sound.jpg",
			"video"=>"video.jpg",
			"text"=>"document2.jpg",
			"text/html"=>"html.jpg",
			"application"=>"binary.jpg",
			"application/pdf"=>"pdf.jpg",
			"application/msword"=>"document2.jpg",
			"application/postscript"=>"postscript.jpg",
			"application/rtf"=>"document2.jpg",
			"application/vnd.ms-excel"=>"document2.jpg",
			"application/vnd.ms-powerpoint"=>"document2.jpg",
			"application/x-tar"=>"tar.jpg",
			"application/zip"=>"tar.jpg",
			"message"=>"email.jpg",
			"message/html"=>"html.jpg",
			"model"=>"kmplot.jpg",
			"multipart"=>"kmultiple.jpg"
			);
	
	$extIcons=array(
			"pdf"=>"pdf.jpg",
			"ps"=>"postscript.jpg",
			"eps"=>"postscript.jpg",
			"ai"=>"postscript.jpg",
			"ra"=>"real_doc.jpg",
			"rm"=>"real_doc.jpg",
			"ram"=>"real_doc.jpg",
			"wav"=>"sound.jpg",
			"mp3"=>"sound.jpg",
			"ogg"=>"sound.jpg",
			"eml"=>"email.jpg",
			"tar"=>"tar.jpg",
			"zip"=>"tar.jpg",
			"bz2"=>"tar.jpg",
			"tgz"=>"tar.jpg",
			"gz"=>"tar.jpg",
			"rar"=>"tar.jpg",
			"avi"=>"video.jpg",
			"mpg"=>"video.jpg",
			"mpeg"=>"video.jpg",
			"jpg"=>"image.jpg",
			"gif"=>"image.jpg",
			"png"=>"image.jpg",
			"jpeg"=>"image.jpg",
			"nfo"=>"info.jpg",
			"xls"=>"spreadsheet.jpg",
			"csv"=>"spreadsheet.jpg",
			"html"=>"html.jpg",
			"doc"=>"document2.jpg",
			"rtf"=>"document2.jpg",
			"txt"=>"document2.jpg",
			"xla"=>"document2.jpg",
			"xlc"=>"document2.jpg",
			"xlt"=>"document2.jpg",
			"xlw"=>"document2.jpg",
			"txt"=>"document2.jpg"
			);

	if ($mime!="text/plain") {
		//Check specific cases
		$mimes=array_keys($mimeIcons);
		if (in_array($mime,$mimes)) {
			return $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/images/".$mimeIcons[$mime];
		} else {
			//Check for the generic mime type
			$mimePrefix="text";
			$firstSlash=strpos($mime,"/"); 
			if ($firstSlash!==false) $mimePrefix=substr($mime,0,$firstSlash);
			
			if (in_array($mimePrefix,$mimes)) {
				return $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/images/".$mimeIcons[$mimePrefix];
			} else {
				return $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/images/empty.jpg";	
			}
		}
	} else {
		$extensions=array_keys($extIcons);
		if (in_array($ext,$extensions)) {
			return $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/images/".$extIcons[$ext];
		} else {
			return $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/images/empty.jpg";
		}
	}

	return $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/images/empty.jpg";
}

?>