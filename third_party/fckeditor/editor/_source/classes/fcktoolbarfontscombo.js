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
 * File Name: fcktoolbarfontscombo.js
 * 	FCKToolbarPanelButton Class: Handles the Fonts combo selector.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKToolbarFontsCombo = function()
{
	this.Command =  FCKCommands.GetCommand( 'FontName' ) ;
}

// Inherit from FCKToolbarSpecialCombo.
FCKToolbarFontsCombo.prototype = new FCKToolbarSpecialCombo ;

FCKToolbarFontsCombo.prototype.GetLabel = function()
{
	return FCKLang.Font ;
}

FCKToolbarFontsCombo.prototype.CreateItems = function( targetSpecialCombo )
{
	var aFonts = FCKConfig.FontNames.split(';') ;
	
	for ( var i = 0 ; i < aFonts.length ; i++ )
		this._Combo.AddItem( aFonts[i], '<span style="font-family: \'' + aFonts[i] + '\'; font-size: 12px;">' + aFonts[i] + '</span>' ) ;
}