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
 * File Name: fckstyledef_ie.js
 * 	FCKStyleDef Class: represents a single stylke definition. (IE specific)
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

FCKStyleDef.prototype.ApplyToSelection = function()
{
	var oSelection = FCK.EditorDocument.selection ;
	
	if ( oSelection.type == 'Text' )
	{
		var oRange = oSelection.createRange() ;
		
		// Create the main element.
		var e = document.createElement( this.Element ) ;
		e.innerHTML = oRange.htmlText ;
		
		// Set the attributes.
		this._AddAttributes( e ) ;
		
		// Remove the duplicated elements.
		this._RemoveDuplicates( e ) ;
		
		// Replace the selection with the resulting HTML.
		oRange.pasteHTML( e.outerHTML ) ;
	}
	else if ( oSelection.type == 'Control' )
	{
		var oControl = FCKSelection.GetSelectedElement() ;
		if ( oControl.tagName == this.Element )
			this._AddAttributes( oControl ) ;
	}
}

FCKStyleDef.prototype._AddAttributes = function( targetElement )
{
	for ( var a in this.Attributes )
	{
		if ( a.toLowerCase() == 'style' )
			targetElement.style.cssText = this.Attributes[a] ;
		else
			targetElement.setAttribute( a, this.Attributes[a], 0 ) ;
	}
}

FCKStyleDef.prototype._RemoveDuplicates = function( parent )
{
	for ( var i = 0 ; i < parent.children.length ; i++ )
	{
		var oChild = parent.children[i] ;
		this._RemoveDuplicates( oChild ) ;
		
		if ( this.IsEqual( oChild ) )
		{
			oChild.insertAdjacentHTML( 'beforeBegin', oChild.innerHTML ) ;
			oChild.parentElement.removeChild( oChild ) ;
		}
	}
}

FCKStyleDef.prototype.IsEqual = function( e )
{
	if ( e.tagName != this.Element )
		return false ;
	
	for ( var a in this.Attributes )
	{
		switch ( a.toLowerCase() )
		{
			case 'style' :
				if ( e.style.cssText.toLowerCase() != this.Attributes[a].toLowerCase() )
					return false ;
				break ;
			case 'class' :
				if ( e.getAttribute( 'className', 0 ) != this.Attributes[a] )
					return false ;
				break ;
			default :
				if ( e.getAttribute( a, 0 ) != this.Attributes[a] )
					return false ;
		}
	}
	
	return true ;
}

FCKStyleDef.prototype._RemoveMe = function( elementToCheck )
{
	if ( ! elementToCheck )
		return ;

	var oParent = elementToCheck.parentElement ;

	if ( this.IsEqual( elementToCheck ) )
	{
		if ( this.IsObjectElement )
		{
			for ( var a in this.Attributes )
			{
				switch ( a.toLowerCase() )
				{
					case 'class' :
						elementToCheck.removeAttribute( 'className', 0 ) ;
						break ;
					default :
						elementToCheck.removeAttribute( a, 0 ) ;
				}
			}
			return ;
		}
		else
			FCKTools.RemoveOuterTags( elementToCheck ) ;
	}
	
	this._RemoveMe( oParent ) ;
}