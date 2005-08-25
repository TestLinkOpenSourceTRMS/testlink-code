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
 * File Name: fcktoolbar.js
 * 	FCKToolbar Class: represents a toolbar. A toolbar is not the complete
 * 	toolbar set visible, but just a strip on it... a group of items.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKToolbar = function()
{
	this.Items = new Array() ;
	
	this.DOMTable = document.createElement( 'table' ) ;
	this.DOMTable.className = 'TB_Toolbar' ;
	with ( this.DOMTable )
	{
		// Sets the toolbar direction. IE uses "styleFloat" and Gecko uses "cssFloat".
		style.styleFloat = style.cssFloat = FCKLang.Dir == 'rtl' ? 'right' : 'left' ;
		
		cellPadding = 0 ;
		cellSpacing = 0 ;
		border = 0 ;
	}

	this.DOMRow = this.DOMTable.insertRow(-1) ;

	var oCell = this.DOMRow.insertCell(-1) ;
	oCell.className = 'TB_Start' ;
	oCell.innerHTML = '<img src="' + FCKConfig.SkinPath + 'images/toolbar.start.gif" width="7" height="21" style="VISIBILITY: hidden" onload="this.style.visibility = \'\';" unselectable="on">' ;

	FCKToolbarSet.DOMElement.appendChild( this.DOMTable ) ;
}

FCKToolbar.prototype.AddItem = function( toolbarItem )
{
	this.Items[ this.Items.length ] = toolbarItem ;
	toolbarItem.CreateInstance( this ) ;
}

FCKToolbar.prototype.AddSeparator = function()
{
	var oCell = this.DOMRow.insertCell(-1) ;
	oCell.unselectable = 'on' ;
	oCell.innerHTML = '<img src="' + FCKConfig.SkinPath + 'images/toolbar.separator.gif" width="5" height="21" style="VISIBILITY: hidden" onload="this.style.visibility = \'\';" unselectable="on">' ;
}

FCKToolbar.prototype.AddTerminator = function()
{
	var oCell = this.DOMRow.insertCell(-1) ;
	oCell.className = 'TB_End' ;
	oCell.innerHTML = '<img src="' + FCKConfig.SkinPath + 'images/toolbar.end.gif" width="12" height="21" style="VISIBILITY: hidden" onload="this.style.visibility = \'\';" unselectable="on">' ;
}
