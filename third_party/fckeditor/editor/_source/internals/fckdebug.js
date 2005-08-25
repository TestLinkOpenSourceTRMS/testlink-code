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
 * File Name: fckdebug.js
 * 	Debug window control and operations.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKDebug = new Object() ;

if ( FCKConfig.Debug )
{
	FCKDebug.Output = function( message, color )
	{
		if ( ! FCKConfig.Debug ) return ;
		
		if ( message != null && isNaN( message ) )
			message = message.replace(/</g, "&lt;") ;

		if ( !this.DebugWindow || this.DebugWindow.closed )
			this.DebugWindow = window.open( 'fckdebug.html', 'FCKeditorDebug', 'menubar=no,scrollbars=no,resizable=yes,location=no,toolbar=no,width=600,height=500', true ) ;
		
		if ( this.DebugWindow.Output)
			this.DebugWindow.Output( message, color ) ;
	}
}
else
	FCKDebug.Output = function() {}
