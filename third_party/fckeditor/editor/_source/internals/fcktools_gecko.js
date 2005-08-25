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
 * File Name: fcktools_gecko.js
 * 	Utility functions. (Gecko version).
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

// Appends a CSS file to a document.
FCKTools.AppendStyleSheet = function( documentElement, cssFileUrl )
{
	var e = documentElement.createElement( 'LINK' ) ;
	e.rel	= 'stylesheet' ;
	e.type	= 'text/css' ;
	e.href	= cssFileUrl ;
	documentElement.getElementsByTagName("HEAD")[0].appendChild( e ) ;
	return e ;
}

// Removes all attributes and values from the element.
FCKTools.ClearElementAttributes = function( element )
{
	// Loop throw all attributes in the element
	for ( var i = 0 ; i < element.attributes.length ; i++ )
	{
		// Remove the element by name.
		element.removeAttribute( element.attributes[i].name, 0 ) ;	// 0 : Case Insensitive
	}
}

// Returns an Array of strings with all defined in the elements inside another element.
FCKTools.GetAllChildrenIds = function( parentElement )
{
	// Create the array that will hold all Ids.
	var aIds = new Array() ;
	
	// Define a recursive function that search for the Ids.
	var fGetIds = function( parent )
	{
		for ( var i = 0 ; i < parent.childNodes.length ; i++ )
		{
			var sId = parent.childNodes[i].id ;
			
			// Check if the Id is defined for the element.
			if ( sId && sId.length > 0 ) aIds[ aIds.length ] = sId ;
			
			// Recursive call.
			fGetIds( parent.childNodes[i] ) ;
		}
	}
	
	// Start the recursive calls.
	fGetIds( parentElement ) ;

	return aIds ;
}

FCKTools.RemoveOuterTags = function( e )
{
	var oFragment = e.ownerDocument.createDocumentFragment() ;
			
	for ( var i = 0 ; i < e.childNodes.length ; i++ )
		oFragment.appendChild( e.childNodes[i] ) ;
			
	e.parentNode.replaceChild( oFragment, e ) ;
}

FCKTools.CreateXmlObject = function( object )
{
	switch ( object )
	{
		case 'XmlHttp' :
			return new XMLHttpRequest() ;
		case 'DOMDocument' :
			return document.implementation.createDocument( '', '', null ) ;
	}
}