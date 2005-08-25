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
 * File Name: fckregexlib.js
 * 	These are some Regular Expresions used by the editor.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKRegexLib = new Object() ;

// This is the Regular expression used by the SetHTML method for the "&apos;" entity.
FCKRegexLib.AposEntity		= /&apos;/gi ;

// Used by the Styles combo to identify styles that can't be applied to text.
FCKRegexLib.ObjectElements	= /^(?:IMG|TABLE|TR|TD|INPUT|SELECT|TEXTAREA|HR|OBJECT)$/i ;

// Block Elements.
FCKRegexLib.BlockElements	= /^(?:P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI|TD)$/i ;

// Elements marked as empty "Empty" in the XHTML DTD.
FCKRegexLib.EmptyElements	= /^(?:BASE|META|LINK|HR|BR|PARAM|IMG|AREA|INPUT)$/i ;

// List all named commands (commands that can be interpreted by the browser "execCommand" method.
FCKRegexLib.NamedCommands	= /^(?:Cut|Copy|Paste|Print|SelectAll|RemoveFormat|Unlink|Undo|Redo|Bold|Italic|Underline|StrikeThrough|Subscript|Superscript|JustifyLeft|JustifyCenter|JustifyRight|JustifyFull|Outdent|Indent|InsertOrderedList|InsertUnorderedList|InsertHorizontalRule)$/i ;

FCKRegexLib.BodyContents	= /([\s\S]*\<body[^\>]*\>)([\s\S]*)(\<\/body\>[\s\S]*)/i ;

// Temporary text used to solve some browser specific limitations.
FCKRegexLib.ToReplace		= /___fcktoreplace:([\w]+)/ig ;

// Get the META http-equiv attribute from the tag.
FCKRegexLib.MetaHttpEquiv	= /http-equiv\s*=\s*["']?([^"' ]+)/i ;

FCKRegexLib.HasBaseTag		= /<base /i ;

FCKRegexLib.HeadCloser		= /<\/head\s*>/i ;

FCKRegexLib.TableBorderClass = /\s*FCK__ShowTableBorders\s*/ ;

// Validate element names.
FCKRegexLib.ElementName = /^[A-Za-z_:][\w.-:]*$/ ;

// Used in conjuction with the FCKConfig.ForceSimpleAmpersand configuration option.
FCKRegexLib.ForceSimpleAmpersand = /___FCKAmp___/g ;

// Get the closing parts of the tags with no closing tags, like <br/>... gets the "/>" part.
FCKRegexLib.SpaceNoClose = /\/>/g ;

FCKRegexLib.EmptyParagraph = /^<(p|div)>\s*<\/\1>$/i ;

FCKRegexLib.TagBody = /></ ;