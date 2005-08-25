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
 * File Name: fckxhtml.js
 * 	Defines the FCKXHtml object, responsible for the XHTML operations.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

var FCKXHtml = new Object() ;

FCKXHtml.CurrentJobNum = 0 ;

FCKXHtml.GetXHTML = function( node, includeNode, format )
{
	// Special blocks are blocks of content that remain untouched during the
	// process. It is used for SCRIPTs and STYLEs.
	FCKXHtml.SpecialBlocks = new Array() ;

	// Create the XML DOMDocument object.
	this.XML = FCKTools.CreateXmlObject( 'DOMDocument' ) ;

	// Add a root element that holds all child nodes.
	this.MainNode = this.XML.appendChild( this.XML.createElement( 'xhtml' ) ) ;

	FCKXHtml.CurrentJobNum++ ;

	if ( includeNode )
		this._AppendNode( this.MainNode, node ) ;
	else
		this._AppendChildNodes( this.MainNode, node, false ) ;

	// Get the resulting XHTML as a string.
	var sXHTML = this._GetMainXmlString() ;

	// Strip the "XHTML" root node.
	sXHTML = sXHTML.substr( 7, sXHTML.length - 15 ).trim() ;
	
	// Remove the trailing <br> added by Gecko.
	if ( FCKBrowserInfo.IsGecko )
		sXHTML = sXHTML.replace( /<br\/>$/, '' ) ;

	// Add a space in the tags with no closing tags, like <br/> -> <br />
	sXHTML = sXHTML.replace( FCKRegexLib.SpaceNoClose, ' />');

	if ( FCKConfig.ForceSimpleAmpersand )
		sXHTML = sXHTML.replace( FCKRegexLib.ForceSimpleAmpersand, '&' ) ;

	if ( format )
		sXHTML = FCKCodeFormatter.Format( sXHTML ) ;

	// Now we put back the SpecialBlocks contents.
	for ( var i = 0 ; i < FCKXHtml.SpecialBlocks.length ; i++ )
	{
		var oRegex = new RegExp( '___FCKsi___' + i ) ;
		sXHTML = sXHTML.replace( oRegex, FCKXHtml.SpecialBlocks[i] ) ;
	}

	this.XML = null ;

	return sXHTML
}

FCKXHtml._AppendAttribute = function( xmlNode, attributeName, attributeValue )
{
	try
	{
		// Create the attribute.
		var oXmlAtt = this.XML.createAttribute( attributeName ) ;

		oXmlAtt.value = attributeValue ? attributeValue : '' ;

		// Set the attribute in the node.
		xmlNode.attributes.setNamedItem( oXmlAtt ) ;
	}
	catch (e)
	{}
}

FCKXHtml._AppendChildNodes = function( xmlNode, htmlNode, isBlockElement )
{
	var iCount = 0 ;
	
	if ( htmlNode.hasChildNodes() )
	{
		// Get all children nodes.
		var oChildren = htmlNode.childNodes ;

		for ( var i = 0 ; i < oChildren.length ; i++ )
		{
			if ( this._AppendNode( xmlNode, oChildren[i] ) )
				iCount++ ;
		}
	}
	
	if ( iCount == 0 )
	{
		if ( isBlockElement && FCKConfig.FillEmptyBlocks )
		{
			this._AppendEntity( xmlNode, 'nbsp' ) ;
			return ;
		}

		// We can't use short representation of empty elements that are not marked
		// as empty in th XHTML DTD.
		if ( !FCKRegexLib.EmptyElements.test( htmlNode.nodeName ) )
			xmlNode.appendChild( this.XML.createTextNode('') ) ;
	}
}

FCKXHtml._AppendNode = function( xmlNode, htmlNode )
{
	switch ( htmlNode.nodeType )
	{
		// Element Node.
		case 1 :
			if ( htmlNode.getAttribute('_fckfakelement') )
				return FCKXHtml._AppendNode( xmlNode, FCK.GetRealElement( htmlNode ) ) ;
		
			// Mozilla insert custom nodes in the DOM.
			if ( FCKBrowserInfo.IsGecko && htmlNode.hasAttribute('_moz_editor_bogus_node') )
				return false ;
			
			if ( htmlNode.getAttribute('_fckdelete') )
				return false ;

			// Create the Element.
			var sNodeName = htmlNode.nodeName ;

			// Check if the node name is valid, otherwise ignore this tag.
			if ( !FCKRegexLib.ElementName.test( sNodeName ) )
				return false ;

			sNodeName = sNodeName.toLowerCase() ;

			if ( FCKBrowserInfo.IsGecko && sNodeName == 'br' && htmlNode.hasAttribute('type') && htmlNode.getAttribute( 'type', 2 ) == '_moz' )
				return false ;

			// The already processed nodes must be marked to avoid then to be duplicated (bad formatted HTML).
			// So here, the "mark" is checked... if the element is Ok, then mark it.
			if ( htmlNode._fckxhtmljob == FCKXHtml.CurrentJobNum )
				return false ;
			else
				htmlNode._fckxhtmljob = FCKXHtml.CurrentJobNum ;

			// If the nodeName starts with a slash, it is a orphan closing tag.
			// On some strange cases, the nodeName is empty, even if the node exists.
//			if ( sNodeName.length == 0 || sNodeName.substr(0,1) == '/' )
//				break ;

			var oNode = this.XML.createElement( sNodeName ) ;

			// Add all attributes.
			FCKXHtml._AppendAttributes( xmlNode, htmlNode, oNode, sNodeName ) ;

			// Tag specific processing.
			var oTagProcessor = FCKXHtml.TagProcessors[ sNodeName ] ;

			if ( oTagProcessor )
			{
				oNode = oTagProcessor( oNode, htmlNode ) ;
				if ( !oNode ) break ;
			}
			else
				this._AppendChildNodes( oNode, htmlNode, FCKRegexLib.BlockElements.test( sNodeName ) ) ;

			xmlNode.appendChild( oNode ) ;

			break ;

		// Text Node.
		case 3 :
			this._AppendTextNode( xmlNode, htmlNode.nodeValue.replaceNewLineChars(' ') ) ;
			break ;

		// Comment
		case 8 :
			xmlNode.appendChild( this.XML.createComment( htmlNode.nodeValue ) ) ;
			break ;

		// Unknown Node type.
		default :
			xmlNode.appendChild( this.XML.createComment( "Element not supported - Type: " + htmlNode.nodeType + " Name: " + htmlNode.nodeName ) ) ;
			break ;
	}
	return true ;
}

// Append an item to the SpecialBlocks array and returns the tag to be used.
FCKXHtml._AppendSpecialItem = function( item )
{
	return '___FCKsi___' + FCKXHtml.SpecialBlocks.addItem( item ) ;
}

if ( FCKConfig.ProcessHTMLEntities )
{
	FCKXHtml._AppendTextNode = function( targetNode, textValue )
	{
		// We can't just replace the special chars with entities and create a
		// text node with it. We must split the text isolating the special chars
		// and add each piece a time.
		var asPieces = textValue.match( FCKXHtmlEntities.EntitiesRegex ) ;

		if ( asPieces )
		{
			for ( var i = 0 ; i < asPieces.length ; i++ )
			{
				if ( asPieces[i].length == 1 )
				{
					var sEntity = FCKXHtmlEntities.Entities[ asPieces[i] ] ;
					if ( sEntity != null )
					{
						this._AppendEntity( targetNode, sEntity ) ;
						continue ;
					}
				}
				targetNode.appendChild( this.XML.createTextNode( asPieces[i] ) ) ;
			}
		}
	}
}
else
{
	FCKXHtml._AppendTextNode = function( targetNode, textValue )
	{
		targetNode.appendChild( this.XML.createTextNode( textValue ) ) ;
	}
}

// An object that hold tag specific operations.
FCKXHtml.TagProcessors = new Object() ;

FCKXHtml.TagProcessors['img'] = function( node )
{
	// The "ALT" attribute is required in XHTML.
	if ( ! node.attributes.getNamedItem( 'alt' ) )
		FCKXHtml._AppendAttribute( node, 'alt', '' ) ;

	return node ;
}

FCKXHtml.TagProcessors['script'] = function( node, htmlNode )
{
	// The "TYPE" attribute is required in XHTML.
	if ( ! node.attributes.getNamedItem( 'type' ) )
		FCKXHtml._AppendAttribute( node, 'type', 'text/javascript' ) ;

	node.appendChild( FCKXHtml.XML.createTextNode( FCKXHtml._AppendSpecialItem( htmlNode.text ) ) ) ;

	return node ;
}

FCKXHtml.TagProcessors['style'] = function( node, htmlNode )
{
	// The "_fcktemp" attribute is used to mark the <STYLE> used by the editor
	// to set some behaviors.
	if ( htmlNode.getAttribute( '_fcktemp' ) )
		return null ;

	// The "TYPE" attribute is required in XHTML.
	if ( ! node.attributes.getNamedItem( 'type' ) )
		FCKXHtml._AppendAttribute( node, 'type', 'text/css' ) ;

	node.appendChild( FCKXHtml.XML.createTextNode( FCKXHtml._AppendSpecialItem( htmlNode.innerHTML ) ) ) ;

	return node ;
}

FCKXHtml.TagProcessors['title'] = function( node, htmlNode )
{
	node.appendChild( FCKXHtml.XML.createTextNode( FCK.EditorDocument.title ) ) ;

	return node ;
}

FCKXHtml.TagProcessors['base'] = function( node, htmlNode )
{
	// The "_fcktemp" attribute is used to mark the <BASE> tag when the editor
	// automatically sets it using the FCKConfig.BaseHref configuration.
	if ( htmlNode.getAttribute( '_fcktemp' ) )
		return null ;

	// IE duplicates the BODY inside the <BASE /> tag (don't ask me why!).
	// This tag processor does nothing... in this way, no child nodes are added
	// (also because the BASE tag must be empty).
	return node ;
}

FCKXHtml.TagProcessors['link'] = function( node, htmlNode )
{
	// The "_fcktemp" attribute is used to mark the fck_internal.css <LINK>
	// reference.
	if ( htmlNode.getAttribute( '_fcktemp' ) )
		return null ;

	return node ;
}

FCKXHtml.TagProcessors['table'] = function( node, htmlNode )
{
	// There is a trick to show table borders when border=0. We add to the
	// table class the FCK__ShowTableBorders rule. So now we must remove it.

	var oClassAtt = node.attributes.getNamedItem( 'class' ) ;

	if ( oClassAtt && FCKRegexLib.TableBorderClass.test( oClassAtt.nodeValue ) )
	{
		var sClass = oClassAtt.nodeValue.replace( FCKRegexLib.TableBorderClass, '' ) ;

		if ( sClass.length == 0 )
			node.attributes.removeNamedItem( 'class' ) ;
		else
			FCKXHtml._AppendAttribute( node, 'class', sClass ) ;
	}

	FCKXHtml._AppendChildNodes( node, htmlNode, false ) ;

	return node ;
}