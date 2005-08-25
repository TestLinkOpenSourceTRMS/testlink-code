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
 * File Name: fcktools.js
 * 	Utility functions.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKTools = new Object() ;

//**
// FCKTools.GetLinkedFieldValue: Gets the value of the hidden INPUT element
// that is associated to the editor. This element has its ID set to the 
// editor's instance name so the user reffers to the instance name when getting
// the posted data.
FCKTools.GetLinkedFieldValue = function()
{
	return FCK.LinkedField.value ;
}

//**
// FCKTools.SetLinkedFieldValue: Sets the value of the hidden INPUT element
// that is associated to the editor. This element has its ID set to the 
// editor's instance name so the user reffers to the instance name when getting
// the posted data.
FCKTools.SetLinkedFieldValue = function( value )
{
	if ( FCKConfig.FormatOutput )
		FCK.LinkedField.value = FCKCodeFormatter.Format( value ) ;
	else
		FCK.LinkedField.value = value ;
}

//**
// FCKTools.AttachToLinkedFieldFormSubmit: attaches a function call to the 
// submit event of the linked field form. This function us generally used to
// update the linked field value before submitting the form.
FCKTools.AttachToLinkedFieldFormSubmit = function( functionPointer )
{
	// Gets the linked field form
	var oForm = FCK.LinkedField.form ;
	
	// Return now if no form is available
	if (!oForm) return ;

	// Attaches the functionPointer call to the onsubmit event
	if ( FCKBrowserInfo.IsIE )
		oForm.attachEvent( "onsubmit", functionPointer ) ;
	else
		oForm.addEventListener( 'submit', functionPointer, true ) ;
	
	//**
	// Attaches the functionPointer call to the submit method 
	// This is done because IE doesn't fire onsubmit when the submit method is called
	// BEGIN --
	
	// Creates a Array in the form object that will hold all Attached function call
	// (in the case there are more than one editor in the same page)
	if (! oForm.updateFCKeditor) oForm.updateFCKeditor = new Array() ;
	
	// Adds the function pointer to the array of functions to call when "submit" is called
	oForm.updateFCKeditor[oForm.updateFCKeditor.length] = functionPointer ;

	// Switches the original submit method with a new one that first call all functions
	// on the above array and the call the original submit
	// IE sees it oForm.submit function as an 'object'.
	if (! oForm.originalSubmit && ( typeof( oForm.submit ) == 'function' || ( !oForm.submit.tagName && !oForm.submit.length ) ) )
	{
		// Creates a copy of the original submit
		oForm.originalSubmit = oForm.submit ;
		
		// Creates our replacement for the submit
		oForm.submit = FCKTools_SubmitReplacer ;
	}
	// END --
}

function FCKTools_SubmitReplacer()
{
	if (this.updateFCKeditor)
	{
		// Calls all functions in the functions array
		for (var i = 0 ; i < this.updateFCKeditor.length ; i++)
			this.updateFCKeditor[i]() ;
	}
	// Calls the original "submit"
	this.originalSubmit() ;
}

//**
// FCKTools.AddSelectOption: Adds a option to a SELECT element.
FCKTools.AddSelectOption = function( targetDocument, selectElement, optionText, optionValue )
{
	var oOption = targetDocument.createElement("OPTION") ;

	oOption.text	= optionText ;
	oOption.value	= optionValue ;	

	selectElement.options.add(oOption) ;

	return oOption ;
}

FCKTools.RemoveAllSelectOptions = function( selectElement )
{
	for ( var i = selectElement.options.length - 1 ; i >= 0 ; i-- )
	{
		selectElement.options.remove(i) ;
	}
}

FCKTools.SelectNoCase = function( selectElement, value, defaultValue )
{
	var sNoCaseValue = value.toString().toLowerCase() ;
	
	for ( var i = 0 ; i < selectElement.options.length ; i++ )
	{
		if ( sNoCaseValue == selectElement.options[i].value.toLowerCase() )
		{
			selectElement.selectedIndex = i ;
			return ;
		}
	}
	
	if ( defaultValue != null ) FCKTools.SelectNoCase( selectElement, defaultValue ) ;
}

FCKTools.HTMLEncode = function( text )
{
	if ( !text )
		return '' ;

	text = text.replace( /&/g, "&amp;" ) ;
	text = text.replace( /"/g, "&quot;" ) ;
	text = text.replace( /</g, "&lt;" ) ;
	text = text.replace( />/g, "&gt;" ) ;
	text = text.replace( /'/g, "&#39;" ) ;

	return text ;
}

//**
// FCKTools.GetResultingArray: Gets a array from a string (where the elements 
// are separated by a character), a fuction (that returns a array) or a array.
FCKTools.GetResultingArray = function( arraySource, separator )
{
	switch ( typeof( arraySource ) )
	{
		case "string" :
			return arraySource.split( separator ) ;
		case "function" :
			return separator() ;
		default :
			if ( isArray( arraySource ) ) return arraySource ;
			else return new Array() ;
	}
}

FCKTools.GetElementPosition = function( el )
{
	// Initializes the Coordinates object that will be returned by the function.
	var c = { X:0, Y:0 } ;
	
	// Loop throw the offset chain.
	while ( el )
	{
		c.X += el.offsetLeft ;
		c.Y += el.offsetTop ;
		
		el = el.offsetParent ;
	}
	
	// Return the Coordinates object
	return c ;
}

FCKTools.GetElementAscensor = function( element, ascensorTagName )
{
	var e = element.parentNode ;

	while ( e )
	{
		if ( e.nodeName == ascensorTagName )
			return e ;

		e = e.parentNode ;
	}
}

FCKTools.Pause = function( miliseconds )
{
	var oStart = new Date() ;

	while (true)
	{ 
		var oNow = new Date() ;
		if ( miliseconds < oNow - oStart ) 
			return ;
	}
}

FCKTools.ConvertStyleSizeToHtml = function( size )
{
	return size.endsWith( '%' ) ? size : parseInt( size ) ;
}

FCKTools.ConvertHtmlSizeToStyle = function( size )
{
	return size.endsWith( '%' ) ? size : ( size + 'px' ) ;
}