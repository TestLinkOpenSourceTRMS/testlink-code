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
 * File Name: fcktoolbarfontformatcombo.js
 * 	FCKToolbarPanelButton Class: Handles the Fonts combo selector.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKToolbarFontFormatCombo = function()
{
	this.Command =  FCKCommands.GetCommand( 'FontFormat' ) ;
}

// Inherit from FCKToolbarSpecialCombo.
FCKToolbarFontFormatCombo.prototype = new FCKToolbarSpecialCombo ;

FCKToolbarFontFormatCombo.prototype.GetLabel = function()
{
	return FCKLang.FontFormat ;
}

FCKToolbarFontFormatCombo.prototype.CreateItems = function( targetSpecialCombo )
{
	// Get the format names from the language file.
	var aNames = FCKLang['FontFormats'].split(';') ;
	var oNames = {
		p		: aNames[0],
		pre		: aNames[1],
		address	: aNames[2],
		h1		: aNames[3],
		h2		: aNames[4],
		h3		: aNames[5],
		h4		: aNames[6],
		h5		: aNames[7],
		h6		: aNames[8],
		div		: aNames[9]
	} ;

	// Get the available formats from the configuration file.
	var aTags = FCKConfig.FontFormats.split(';') ;
	
	for ( var i = 0 ; i < aTags.length ; i++ )
	{
		if ( aTags[i] == 'div' && FCKBrowserInfo.IsGecko )
			continue ;
		this._Combo.AddItem( aTags[i], '<' + aTags[i] + '>' + oNames[aTags[i]] + '</' + aTags[i] + '>', oNames[aTags[i]] ) ;
	}
}