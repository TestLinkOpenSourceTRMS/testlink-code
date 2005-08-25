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
 * File Name: fckcontextmenu_ie.js
 * 	Context Menu operations. (IE specific implementations)
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

function FCKContextMenu_OnContextMenu() { return false ; }

FCKContextMenu.Show = function( x, y )
{
	// Create the Popup used to show the menu (this is a IE 5.5+ feature).
	if ( ! this._Popup )
	{
		this._Popup = window.createPopup() ;
		this._Document = this._Popup.document ;
		this._Document.createStyleSheet( FCKConfig.SkinPath + 'fck_contextmenu.css' ) ;
		this._Document.oncontextmenu = FCKContextMenu_OnContextMenu ;

		aCleanupDocs[ aCleanupDocs.length ] = this._Document ;
	}

	// Create the context menu if needed.
	if ( !this._IsLoaded )
	{
		this.Reload() ;
		this._Div.style.visibility = '' ;
	}

	this.RefreshState() ;

	// IE doens't get the offsetWidth and offsetHeight values if the element is not visible.
	// So the Popup must be "shown" with no size to be able to get these values.
	this._Popup.show( x, y, 0, 0 ) ;

	// This was the previous solution. It works well to.
	// So a temporary element is created to get this for us.
	/*
	if ( !this._DivCopy )
	{
		this._DivCopy = document.createElement( 'DIV' ) ;
		this._DivCopy.className			= 'CM_ContextMenu' ;
		this._DivCopy.style.position	= 'absolute' ;
		this._DivCopy.style.visibility	= 'hidden' ;
		document.body.appendChild( this._DivCopy );
	}

	this._DivCopy.innerHTML = this._Div.innerHTML ;
	*/

	// Show the Popup at the specified location.
	this._Popup.show( x, y, this._Div.offsetWidth, this._Div.offsetHeight ) ;
}

FCKContextMenu.Hide = function()
{
	if ( this._Popup )
		this._Popup.hide() ;
}