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
 * File Name: fckpanel_ie.js
 * 	FCKPanel Class: Creates and manages floating panels in IE Browsers.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKPanel = function( parentWindow )
{
	this.Window = parentWindow ? parentWindow : window ;
}

function FCKPanel_OnContextMenu() { return false ; }

FCKPanel.prototype.Create = function()
{
	// Create the Popup that will hold the panel.
	this._Popup = this.Window.createPopup() ;
	
	this.Document = this._Popup.document ;
	
	aCleanupDocs[ aCleanupDocs.length ] = this.Document ;
	
	this.Document.oncontextmenu = FCKPanel_OnContextMenu ;

	if ( this.StyleSheet )
		FCKTools.AppendStyleSheet( this.Document, this.StyleSheet ) ;
		
	// Create the main DIV that is used as the panel base.
	this.PanelDiv = this.Document.body.appendChild( this.Document.createElement('DIV') ) ;
	this.PanelDiv.className = 'FCK_Panel' ;
	
	this.Created = true ;
}

FCKPanel.prototype.Show = function( panelX, panelY, relElement, width, height, autoSize )
{
	if ( ! this.Created )
		this._Create() ;
	
	// The offsetWidth and offsetHeight properties are not available if the 
	// element is not visible. So we must "show" the popup with no size to
	// be able to use that values in the second call.
	this._Popup.show( panelX, panelY, 0, 0, relElement ) ;

	if ( width == null || ( autoSize && width > this.PanelDiv.offsetWidth ) )
		var iWidth = this.PanelDiv.offsetWidth ;
	else
		var iWidth = width ;

	if ( height == null || ( autoSize && height > this.PanelDiv.offsetHeight ) )
		var iHeight = this.PanelDiv.offsetHeight ;
	else
		var iHeight = height ;

	this.PanelDiv.style.height = iHeight ;

	// Second call: Show the Popup at the specified location.
	this._Popup.show( panelX, panelY, iWidth, iHeight, relElement ) ;
}

FCKPanel.prototype.Hide = function()
{
	if ( this._Popup )
		this._Popup.hide() ;
}