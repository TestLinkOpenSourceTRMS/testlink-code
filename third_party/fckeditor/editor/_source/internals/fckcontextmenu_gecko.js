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
 * File Name: fckcontextmenu_gecko.js
 * 	Context Menu operations. (Gecko specific implementations)
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

// The Context Menu CSS must be added to the parent document.
FCKTools.AppendStyleSheet( window.parent.document, FCKConfig.SkinPath + 'fck_contextmenu.css' ) ;

FCKContextMenu.Show = function( x, y )
{
	if ( ! this._Document )
	{
		this._Document = window.parent.document ;
	}

	// Create the context menu if needed.
	if ( !this._IsLoaded ) 
	{
		this.Reload() ;
		this._Div.style.zIndex = 10000 ;
		this._Div.oncontextmenu = function() { return false ; }
	}
	
	this.RefreshState() ;

	// Get the editor area and editor frames positions.	
	var oCoordsA = FCKTools.GetElementPosition( FCK.EditorWindow.frameElement ) ;
	var oCoordsB = FCKTools.GetElementPosition( window.frameElement ) ;
	
	x += oCoordsA.X + oCoordsB.X ;
	y += oCoordsA.Y + oCoordsB.Y ;

	// Verifies if the context menu is completely visible.
	var iXSpace = x + this._Div.offsetWidth - this._Div.ownerDocument.defaultView.innerWidth ;
	var iYSpace = y + this._Div.offsetHeight - this._Div.ownerDocument.defaultView.innerHeight ;
	
	if ( iXSpace > 0 ) x -= this._Div.offsetWidth ;
	if ( iYSpace > 0 ) y -= this._Div.offsetHeight ;
	
	// Set the context menu DIV in the specified location.
	this._Div.style.left	= x + 'px' ;
	this._Div.style.top		= y + 'px' ;

	// Watch the "OnClick" event for all windows to close the Context Menu.
	var oActualWindow = FCK.EditorWindow ;
	while ( oActualWindow )
	{
		oActualWindow.document.addEventListener( 'click', FCKContextMenu._OnDocumentClick, false ) ;
		if ( oActualWindow != oActualWindow.parent )
			oActualWindow = oActualWindow.parent ;
		else if ( oActualWindow.opener == null ) 
			oActualWindow = oActualWindow.opener ;
		else
			break ;
	}
	
	// Show it.
	this._Div.style.visibility	= '' ;
}

FCKContextMenu._OnDocumentClick = function( event )
{
	var e = event.target ;
	while ( e )
	{
		if ( e == FCKContextMenu._Div ) return ;
		e = e.parentNode ;
	}
	FCKContextMenu.Hide() ;
}

FCKContextMenu.Hide = function()
{
	this._Div.style.visibility = 'hidden' ;
	this._Div.style.left = this._Div.style.top = '1px' ;
}