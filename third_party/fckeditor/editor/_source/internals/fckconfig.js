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
 * File Name: fckconfig.js
 * 	Creates and initializes the FCKConfig object.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKConfig = FCK.Config = new Object() ;

// Editor Base Path
if ( document.location.protocol == 'file:' )
{
	FCKConfig.BasePath = document.location.pathname.substr(1) ;
	FCKConfig.BasePath = FCKConfig.BasePath.replace( /\\/gi, '/' ) ;
	FCKConfig.BasePath = 'file://' + FCKConfig.BasePath.substring(0,FCKConfig.BasePath.lastIndexOf('/')+1) ;
}
else
{
	FCKConfig.BasePath = document.location.pathname.substring(0,document.location.pathname.lastIndexOf('/')+1) ;
	FCKConfig.FullBasePath = document.location.protocol + '//' + document.location.host + FCKConfig.BasePath ;
}

FCKConfig.EditorPath = FCKConfig.BasePath.replace( /editor\/$/, '' ) ;

// Override the actual configuration values with the values passed throw the 
// hidden field "<InstanceName>___Config".
FCKConfig.ProcessHiddenField = function()
{
	this.PageConfig = new Object() ;

	// Get the hidden field.
	var oConfigField = window.parent.document.getElementById( FCK.Name + '___Config' ) ;
	
	// Do nothing if the config field was not defined.
	if ( ! oConfigField ) return ;

	var aCouples = oConfigField.value.split('&') ;

	for ( var i = 0 ; i < aCouples.length ; i++ )
	{
		if ( aCouples[i].length == 0 )
			continue ;

		var aConfig = aCouples[i].split( '=' ) ;
		var sKey = unescape( aConfig[0] ) ;
		var sVal = unescape( aConfig[1] ) ;

		if ( sKey == 'CustomConfigurationsPath' )	// The Custom Config File path must be loaded immediately.
			FCKConfig[ sKey ] = sVal ;
			
		else if ( sVal.toLowerCase() == "true" )	// If it is a boolean TRUE.
			this.PageConfig[ sKey ] = true ;
			
		else if ( sVal.toLowerCase() == "false" )	// If it is a boolean FALSE.
			this.PageConfig[ sKey ] = false ;
			
		else if ( ! isNaN( sVal ) )					// If it is a number.
			this.PageConfig[ sKey ] = parseInt( sVal ) ;
			
		else										// In any other case it is a string.
			this.PageConfig[ sKey ] = sVal ;
	}
}

FCKConfig.LoadPageConfig = function()
{
	for ( var sKey in this.PageConfig )
		FCKConfig[ sKey ] = this.PageConfig[ sKey ] ;
}

// Define toolbar sets collection.
FCKConfig.ToolbarSets = new Object() ;

// Defines the plugins collection.
FCKConfig.Plugins = new Object() ;
FCKConfig.Plugins.Items = new Array() ;

FCKConfig.Plugins.Add = function( name, langs, path )
{
	FCKConfig.Plugins.Items.addItem( [name, langs, path] ) ;
}