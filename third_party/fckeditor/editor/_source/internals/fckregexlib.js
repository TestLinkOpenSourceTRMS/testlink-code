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
 * File Name: fckregexlib.js
 * 	These are some Regular Expresions used by the editor.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (www.fckeditor.net)
 * 		Alfonso Martinez de Lizarrondo - Uritec (alfonso at uritec dot net)
 */

var FCKRegexLib = 
{
// This is the Regular expression used by the SetHTML method for the "&apos;" entity.
AposEntity		: /&apos;/gi ,

// Used by the Styles combo to identify styles that can't be applied to text.
ObjectElements	: /^(?:IMG|TABLE|TR|TD|TH|INPUT|SELECT|TEXTAREA|HR|OBJECT|A|UL|OL|LI)$/i ,

// List all named commands (commands that can be interpreted by the browser "execCommand" method.
NamedCommands	: /^(?:Cut|Copy|Paste|Print|SelectAll|RemoveFormat|Unlink|Undo|Redo|Bold|Italic|Underline|StrikeThrough|Subscript|Superscript|JustifyLeft|JustifyCenter|JustifyRight|JustifyFull|Outdent|Indent|InsertOrderedList|InsertUnorderedList|InsertHorizontalRule)$/i ,

BodyContents	: /([\s\S]*\<body[^\>]*\>)([\s\S]*)(\<\/body\>[\s\S]*)/i ,

// Temporary text used to solve some browser specific limitations.
ToReplace		: /___fcktoreplace:([\w]+)/ig ,

// Get the META http-equiv attribute from the tag.
MetaHttpEquiv	: /http-equiv\s*=\s*["']?([^"' ]+)/i ,

HasBaseTag		: /<base /i ,

HtmlOpener		: /<html\s?[^>]*>/i ,
HeadOpener		: /<head\s?[^>]*>/i ,
HeadCloser		: /<\/head\s*>/i ,

// Temporary classes (Tables without border, Anchors with content) used in IE
FCK_Class		: /(\s*FCK__[A-Za-z]*\s*)/ ,

// Validate element names (it must be in lowercase).
ElementName		: /(^[a-z_:][\w.\-:]*\w$)|(^[a-z_]$)/ ,

// Used in conjuction with the FCKConfig.ForceSimpleAmpersand configuration option.
ForceSimpleAmpersand : /___FCKAmp___/g ,

// Get the closing parts of the tags with no closing tags, like <br/>... gets the "/>" part.
SpaceNoClose	: /\/>/g ,

EmptyParagraph	: /^<(p|div)>\s*<\/\1>$/i ,

TagBody			: /></ ,

StrongOpener	: /<STRONG([ \>])/gi ,
StrongCloser	: /<\/STRONG>/gi ,
EmOpener		: /<EM([ \>])/gi ,
EmCloser		: /<\/EM>/gi ,
//AbbrOpener		: /<ABBR([ \>])/gi ,
//AbbrCloser		: /<\/ABBR>/gi ,

GeckoEntitiesMarker : /#\?-\:/g ,

// We look for the "src" and href attribute with the " or ' or whithout one of
// them. We have to do all in one, otherwhise we will have problems with URLs
// like "thumbnail.php?src=someimage.jpg" (SF-BUG 1554141).
ProtectUrlsImg	: /(?:(<img(?=\s).*?\ssrc=)("|')(.*?)\2)|(?:(<img\s.*?src=)([^"'][^ >]+))/gi ,
ProtectUrlsA	: /(?:(<a(?=\s).*?\shref=)("|')(.*?)\2)|(?:(<a\s.*?href=)([^"'][^ >]+))/gi ,

Html4DocType	: /HTML 4\.0 Transitional/i ,
DocTypeTag		: /<!DOCTYPE[^>]*>/i ,

// These regex are used to save the original event attributes in the HTML.
TagsWithEvent	: /<[^\>]+ on\w+[\s\r\n]*=[\s\r\n]*?('|")[\s\S]+?\>/g ,
EventAttributes	: /\s(on\w+)[\s\r\n]*=[\s\r\n]*?('|")([\s\S]*?)\2/g,
ProtectedEvents : /\s\w+_fckprotectedatt="([^"]+)"/g,

StyleProperties : /\S+\s*:/g
} ;

// Test have shown that check for the existence of a key in an object is the
// most efficient list entry check (10x faster that regex). Example:
//		if ( FCKListsLib.<ListName>[key] != null )
var FCKListsLib =
{
	// We are not handling <ins> and <del> as block elements, for now.
	BlockElements : { address:1,blockquote:1,div:1,dl:1,fieldset:1,form:1,h1:1,h2:1,h3:1,h4:1,h5:1,h6:1,hr:1,noscript:1,ol:1,p:1,pre:1,script:1,table:1,ul:1 },

	// Block elements that may be filled with &nbsp; if empty.
	NonEmptyBlockElements : { p:1,div:1,h1:1,h2:1,h3:1,h4:1,h5:1,h6:1,address:1,pre:1,ol:1,ul:1,li:1,td:1,th:1 },
	
	// Elements that may be considered the "Block boundary" in an element path.
	PathBlockElements : { address:1,blockquote:1,div:1,dl:1,h1:1,h2:1,h3:1,h4:1,h5:1,h6:1,p:1,pre:1,ol:1,ul:1,li:1,dt:1,de:1 },

	// Inline elements which MUST have child nodes.
	InlineChildReqElements : { abbr:1,acronym:1,b:1,bdo:1,big:1,cite:1,code:1,del:1,dfn:1,em:1,i:1,ins:1,label:1,kbd:1,q:1,samp:1,small:1,span:1,strong:1,sub:1,sup:1,tt:1,'var':1 },
	
	// Elements marked as empty "Empty" in the XHTML DTD.
	EmptyElements : { base:1,meta:1,link:1,hr:1,br:1,param:1,img:1,area:1,input:1 }
} ;