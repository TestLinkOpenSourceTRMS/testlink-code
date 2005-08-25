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
 * File Name: fck.js
 * 	Creation and initialization of the "FCK" object. This is the main object
 * 	that represents an editor instance.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

// FCK represents the active editor instance
var FCK = new Object() ;
FCK.Name			= FCKURLParams[ 'InstanceName' ] ;

FCK.Status			= FCK_STATUS_NOTLOADED ;
FCK.EditMode		= FCK_EDITMODE_WYSIWYG ;

// There is a bug on IE... getElementById returns any META tag that has the
// name set to the ID you are looking for. So the best way in to get the array
// by names and look for the correct one.

var aElements = window.parent.document.getElementsByName( FCK.Name ) ;
var i = 0;
while ( FCK.LinkedField = aElements[i++] )
{
	if ( FCK.LinkedField.tagName == 'INPUT' || FCK.LinkedField.tagName == 'TEXTAREA' )
		break ;
}

var FCKTempBin = new Object() ;
FCKTempBin.Elements = new Array() ;

FCKTempBin.AddElement = function( element )
{
	var iIndex = FCKTempBin.Elements.length ;
	FCKTempBin.Elements[ iIndex ] = element ;
	return iIndex ;
}

FCKTempBin.RemoveElement = function( index )
{
	var e = FCKTempBin.Elements[ index ] ;
	FCKTempBin.Elements[ index ] = null ;
	return e ;
}

FCKTempBin.Reset = function()
{
	var i = 0 ;
	while ( i < FCKTempBin.Elements.length )
		FCKTempBin.Elements[ i++ ] == null ;
	FCKTempBin.Elements.length = 0 ;
}