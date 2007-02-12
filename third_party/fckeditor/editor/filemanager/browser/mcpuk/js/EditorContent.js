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
 * File Name: EditorContent.js
 * 	(!)
 * 
 * File Authors:
 * 		Grant French (grant@mcpuk.net)
 */
var mode='resize';

function setMode(newMode) {
	if (newMode=="resize" || newMode=="crop" || newMode=="rotate" || newMode=="flip") {
		mode=newMode;
		alert("New Mode :: "+mode);
	} else {
		alert("Image Editor :: Invalid mode selected.");
	}
}

function mouseMove(e) {

}
