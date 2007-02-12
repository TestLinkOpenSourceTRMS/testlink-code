/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 * 
 * == BEGIN LICENSE ==
 * 
 * Licensed under the terms of any of the following licenses at your
 * choice:
 * 
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 * 
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 * 
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 * 
 * == END LICENSE ==
 * 
 * File Name: fckurlparams.js
 * 	Defines the FCKURLParams object that is used to get all parameters
 * 	passed by the URL QueryString (after the "?").
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (www.fckeditor.net)
 */

// #### URLParams: holds all URL passed parameters (like ?Param1=Value1&Param2=Value2)
var FCKURLParams = new Object() ;

(function()
{
	var aParams = document.location.search.substr(1).split('&') ;
	for ( var i = 0 ; i < aParams.length ; i++ )
	{
		var aParam = aParams[i].split('=') ;
		var sParamName  = decodeURIComponent( aParam[0] ) ;
		var sParamValue = decodeURIComponent( aParam[1] ) ;

		FCKURLParams[ sParamName ] = sParamValue ;
	}
})();