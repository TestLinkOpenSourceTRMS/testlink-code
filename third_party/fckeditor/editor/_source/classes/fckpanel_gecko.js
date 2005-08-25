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
 * File Name: fckpanel_gecko.js
 * 	FCKPanel Class: Creates and manages floating panels in Gecko Browsers.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKPanel = function( parentWindow )
{
	if ( parentWindow )
		this.Window = parentWindow ;
	else
	{
		this.Window = window ;

		while ( this.Window != window.top )
		{
			// Try/Catch must be used to avoit an error when using a frameset
			// on a different domain:
			// "Permission denied to get property HTMLDocument.Body".
			try
			{
				if ( this.Window.parent.document.body.tagName == 'FRAMESET' )
					break ;
			}
			catch (e)
			{
				break ;
			}

			this.Window = this.Window.parent ;
		}
	}
}

FCKPanel.prototype.Create = function()
{
	this._IFrame = this.Window.document.body.appendChild( this.Window.document.createElement('iframe') ) ;
	this._IFrame.src = 'about:blank' ;
    this._IFrame.frameBorder		= '0';
    this._IFrame.scrolling			= 'no' ;
    this._IFrame.style.left			= '0px' ;
	this._IFrame.style.top			= '0px' ;
    this._IFrame.width				= 10 ;
	this._IFrame.height				= 10 ;
    this._IFrame.style.position		= 'absolute';
	this._IFrame.style.visibility	= 'hidden' ;

	this._IFrame.IsFCKPanel	= true ;
	this._IFrame.Panel		= this ;

	this.Document = this._IFrame.contentWindow.document ;

	// Initialize the IFRAME document body.
	this.Document.open() ;
	this.Document.write( '<html><head></head><body><\/body><\/html>' ) ;
	this.Document.close() ;

	// Remove the default margins.
	this.Document.body.style.margin = this.Document.body.style.padding = '0px' ;

	// Add the defined Style Sheet to the document.
	if ( this.StyleSheet )
		FCKTools.AppendStyleSheet( this.Document, this.StyleSheet ) ;


	this.OuterDiv = this.Document.body.appendChild( this.Document.createElement('DIV') ) ;
	this.OuterDiv.style.cssFloat = 'left' ;

	this.PanelDiv = this.OuterDiv.appendChild( this.Document.createElement('DIV') ) ;
	this.PanelDiv.className = 'FCK_Panel' ;

	this.Created = true ;
}

FCKPanel.prototype.Show = function( panelX, panelY, relElement, width, height, autoSize  )
{
	if ( ! this.Created )
		this.Create() ;

	if ( width != null && autoSize && width < this.OuterDiv.offsetWidth )
		this.PanelDiv.style.width = width ;

	if ( height != null && autoSize && height < this.PanelDiv.offsetHeight )
		this.PanelDiv.style.height = height + 'px' ;

	var oPos = this.GetElementPosition( relElement ) ;

	panelX += oPos.X ;
	panelY += oPos.Y ;

	if ( panelX + this.OuterDiv.offsetWidth > this.Window.innerWidth )
	{
		// The following line aligns the panel to the other side of the refElement.
		// panelX = oPos.X - ( this.PanelDiv.offsetWidth - relElement.offsetWidth ) ;

		panelX -= panelX + this.OuterDiv.offsetWidth - this.Window.innerWidth ;
	}

	// Set the context menu DIV in the specified location.
	this._IFrame.style.left	= panelX + 'px' ;
	this._IFrame.style.top	= panelY + 'px' ;

	// Watch the "OnClick" event for all windows to close the Context Menu.
	function SetOnClickListener( targetWindow, targetFunction )
	{
		// Try/Catch must be used to avoit an error when using a frameset
		// on a different domain:
		// "Permission denied to get property Window.frameElement".
		try
		{
			if ( targetWindow == null || ( targetWindow.frameElement && targetWindow.frameElement.IsFCKPanel ) )
				return ;

			targetWindow.document.addEventListener( 'click', targetFunction, false ) ;
		}
		catch (e) {}

		for ( var i = 0 ; i < targetWindow.frames.length ; i++ )
			SetOnClickListener( targetWindow.frames[i], targetFunction ) ;
	}
	SetOnClickListener( window.top, FCKPanelEventHandlers.OnDocumentClick ) ;

	this._IFrame.width	= this.OuterDiv.offsetWidth ;
	this._IFrame.height = this.OuterDiv.offsetHeight ;

	// Show it.
	this._IFrame.style.visibility = '' ;
}

FCKPanel.prototype.GetElementPosition = function( el )
{
	// Initializes the Coordinates object that will be returned by the function.
	var c = { X:0, Y:0 } ;

	// Loop throw the offset chain.
	while ( el )
	{
		c.X += el.offsetLeft ;
		c.Y += el.offsetTop ;

		if ( el.offsetParent == null && el.ownerDocument.defaultView != this.Window )
			el = el.ownerDocument.defaultView.frameElement ;
		else
			el = el.offsetParent ;
	}

	// Return the Coordinates object
	return c ;
}

FCKPanel.prototype.Hide = function()
{
	// There is a bug on Firefox over Mac. It doesn't hide the Panel
	// scrollbars, so we must force it.
	this.PanelDiv.style.overflow = 'visible' ;

	this._IFrame.style.visibility = 'hidden' ;
//	this._IFrame.style.left = this._IFrame.style.top = '0px' ;
}

var FCKPanelEventHandlers = new Object() ;

FCKPanelEventHandlers.OnDocumentClick = function( e )
{
	var oWindow = e.target.ownerDocument.defaultView ;

	if ( ! oWindow.IsFCKPanel )
	{
		function RemoveOnClickListener( targetWindow )
		{
			if ( targetWindow == null )
				return ;

			// Try/Catch must be used to avoit an error when using a frameset
			// on a different domain:
			// "Permission denied to get property Window.frameElement".
			try
			{
				if ( targetWindow.frameElement && targetWindow.frameElement.IsFCKPanel )
					targetWindow.frameElement.Panel.Hide() ;
				else
					targetWindow.document.removeEventListener( 'click', FCKPanelEventHandlers.OnDocumentClick, false ) ;
			}
			catch (e) {}

			for ( var i = 0 ; i < targetWindow.frames.length ; i++ )
				RemoveOnClickListener( targetWindow.frames[i] ) ;
		}
		RemoveOnClickListener( window.top ) ;
	}
}