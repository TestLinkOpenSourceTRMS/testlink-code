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
 * File Name: fckcodeformatter.js
 * 	Format the HTML.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKCodeFormatter ;

if ( !( FCKCodeFormatter = NS.FCKCodeFormatter ) )
{
	FCKCodeFormatter = NS.FCKCodeFormatter = new Object() ;

	FCKCodeFormatter.Regex = new Object() ;

	// Regex for line breaks.
	FCKCodeFormatter.Regex.BlocksOpener = /\<(P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI|TITLE|META|LINK|BASE|SCRIPT|LINK|TD|AREA|OPTION)[^\>]*\>/gi ;
	FCKCodeFormatter.Regex.BlocksCloser = /\<\/(P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI|TITLE|META|LINK|BASE|SCRIPT|LINK|TD|AREA|OPTION)[^\>]*\>/gi ;

	FCKCodeFormatter.Regex.NewLineTags	= /\<(BR|HR)[^\>]\>/gi ;

	FCKCodeFormatter.Regex.MainTags = /\<\/?(HTML|HEAD|BODY|FORM|TABLE|TBODY|THEAD|TR)[^\>]*\>/gi ;

	FCKCodeFormatter.Regex.LineSplitter = /\s*\n+\s*/g ;

	// Regex for indentation.
	FCKCodeFormatter.Regex.IncreaseIndent = /^\<(HTML|HEAD|BODY|FORM|TABLE|TBODY|THEAD|TR|UL|OL)[ \/\>]/i ;
	FCKCodeFormatter.Regex.DecreaseIndent = /^\<\/(HTML|HEAD|BODY|FORM|TABLE|TBODY|THEAD|TR|UL|OL)[ \>]/i ;
	FCKCodeFormatter.Regex.FormatIndentatorRemove = new RegExp( FCKConfig.FormatIndentator ) ;

	FCKCodeFormatter.Format = function( html )
	{
		// Line breaks.
		var sFormatted	= html.replace( this.Regex.BlocksOpener, '\n$&' ) ; ;
		sFormatted		= sFormatted.replace( this.Regex.BlocksCloser, '$&\n' ) ;
		sFormatted		= sFormatted.replace( this.Regex.NewLineTags, '$&\n' ) ;
		sFormatted		= sFormatted.replace( this.Regex.MainTags, '\n$&\n' ) ;
		
		// Indentation.
		var sIndentation = '' ;
		
		var asLines = sFormatted.split( this.Regex.LineSplitter ) ;
		sFormatted = '' ;
		
		for ( var i = 0 ; i < asLines.length ; i++ )
		{
			var sLine = asLines[i] ;
			
			if ( sLine.length == 0 )
				continue ;
			
			if ( this.Regex.DecreaseIndent.test( sLine ) )
				sIndentation = sIndentation.replace( this.Regex.FormatIndentatorRemove, '' ) ;

			sFormatted += sIndentation + sLine + '\n' ;
			
			if ( this.Regex.IncreaseIndent.test( sLine ) )
				sIndentation += FCKConfig.FormatIndentator ;
		}

		return sFormatted.trim() ;
	}
}