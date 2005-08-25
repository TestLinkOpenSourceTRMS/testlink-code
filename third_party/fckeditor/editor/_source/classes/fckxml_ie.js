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
 * File Name: fckxml_ie.js
 * 	FCKXml Class: class to load and manipulate XML files.
 * 	(IE specific implementation)
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKXml ;

if ( !( FCKXml = NS.FCKXml ) )
{
	FCKXml = NS.FCKXml = function()
	{}

	FCKXml.prototype.LoadUrl = function( urlToCall )
	{
		var oXmlHttp = FCKTools.CreateXmlObject( 'XmlHttp' ) ;

		oXmlHttp.open( "GET", urlToCall, false ) ;
		
		oXmlHttp.send( null ) ;
		
		if ( oXmlHttp.status == 200 )
			this.DOMDocument = oXmlHttp.responseXML ;
		else if ( oXmlHttp.status == 0 && oXmlHttp.readyState == 4 )
		{
			this.DOMDocument = FCKTools.CreateXmlObject( 'DOMDocument' ) ;
			this.DOMDocument.async = false ;
			this.DOMDocument.resolveExternals = false ;
			this.DOMDocument.loadXML( oXmlHttp.responseText ) ;
		}
		else
			alert( 'Error loading "' + urlToCall + '"' ) ;
	}

	FCKXml.prototype.SelectNodes = function( xpath, contextNode )
	{
		if ( contextNode )
			return contextNode.selectNodes( xpath ) ;
		else
			return this.DOMDocument.selectNodes( xpath ) ;
	}

	FCKXml.prototype.SelectSingleNode = function( xpath, contextNode ) 
	{
		if ( contextNode )
			return contextNode.selectSingleNode( xpath ) ;
		else
			return this.DOMDocument.selectSingleNode( xpath ) ;
	}
}