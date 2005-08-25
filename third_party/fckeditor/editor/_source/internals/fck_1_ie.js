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
 * File Name: fck_1_ie.js
 * 	This is the first part of the "FCK" object creation. This is the main
 * 	object that represents an editor instance.
 * 	(IE specific implementations)
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
 */

FCK.Description = "FCKeditor for Internet Explorer 5.5+" ;

// The behaviors should be pointed using the FullBasePath to avoid security
// errors when using a differente BaseHref.
FCK._BehaviorsStyle =
	'<style type="text/css" _fcktemp="true"> \
		INPUT		{ behavior: url(' + FCKConfig.FullBasePath + 'css/behaviors/hiddenfield.htc) ; } \
		INPUT		{ behavior: url(' + FCKConfig.FullBasePath + 'css/behaviors/disablehandles.htc) ; } \
		TEXTAREA	{ behavior: url(' + FCKConfig.FullBasePath + 'css/behaviors/disablehandles.htc) ; } \
		SELECT		{ behavior: url(' + FCKConfig.FullBasePath + 'css/behaviors/disablehandles.htc) ; }' ;

if ( FCKConfig.ShowBorders )
	FCK._BehaviorsStyle += 'TABLE { behavior: url(' + FCKConfig.FullBasePath + 'css/behaviors/showtableborders.htc) ; }' ;

if ( FCKConfig.DisableImageHandles )
	FCK._BehaviorsStyle += 'IMG { behavior: url(' + FCKConfig.FullBasePath + 'css/behaviors/disablehandles.htc) ; }' ;

if ( FCKConfig.DisableTableHandles )
	FCK._BehaviorsStyle += 'TABLE { behavior: url(' + FCKConfig.FullBasePath + 'css/behaviors/disablehandles.htc) ; }' ;

// Disable anchors handles
FCK._BehaviorsStyle += '.FCK__Anchor { behavior: url(' + FCKConfig.FullBasePath + 'css/behaviors/disablehandles.htc) ; }' ;

FCK._BehaviorsStyle += '</style>' ;

function Doc_OnMouseDown()
{
	FCK.Focus() ;

	FCK.EditorWindow.event.cancelBubble	= true ;
	FCK.EditorWindow.event.returnValue	= false ;
}

function Doc_OnPaste()
{
	if ( FCK.Status == FCK_STATUS_COMPLETE )
		return FCK.Events.FireEvent( "OnPaste" ) ;
	else
		return false ;
}

function Doc_OnContextMenu()
{
	var e = FCK.EditorWindow.event ;
	
	FCK.ShowContextMenu( e.screenX, e.screenY ) ;
	return false ;
}

function Doc_OnKeyDown()
{
	var e = FCK.EditorWindow.event ;

	if ( e.keyCode == 13 && FCKConfig.UseBROnCarriageReturn )	// ENTER
	{
		if ( (e.ctrlKey || e.altKey || e.shiftKey) )
			return true ;
		else
		{
			// We must ignore it if we are inside a List.
			if ( FCK.EditorDocument.queryCommandState( 'InsertOrderedList' ) || FCK.EditorDocument.queryCommandState( 'InsertUnorderedList' ) )
				return true ;

			// Insert the <BR> (The &nbsp; must be also inserted to make it work)
			FCK.InsertHtml("<br>&nbsp;") ;

			// Remove the &nbsp;
			var oRange = FCK.EditorDocument.selection.createRange() ;
			oRange.moveStart('character',-1) ;
			oRange.select() ;
			FCK.EditorDocument.selection.clear() ;

			return false ;
		}
	}
	else if ( e.keyCode == 9 && FCKConfig.TabSpaces > 0 && !(e.ctrlKey || e.altKey || e.shiftKey) )	// TAB
	{
		FCK.InsertHtml( window.FCKTabHTML ) ;
		return false ;
	}
	
	return true ;
}

function Doc_OnKeyDownUndo()
{
	if ( !FCKUndo.Typing )
	{
		FCKUndo.SaveUndoStep() ;
		FCKUndo.Typing = true ;
		FCK.Events.FireEvent( "OnSelectionChange" ) ;
	}
	
	FCKUndo.TypesCount++ ;

	if ( FCKUndo.TypesCount > FCKUndo.MaxTypes )
	{
		FCKUndo.TypesCount = 0 ;
		FCKUndo.SaveUndoStep() ;
	}
}

function Doc_OnDblClick()
{
	FCK.OnDoubleClick( FCK.EditorWindow.event.srcElement ) ;
	FCK.EditorWindow.event.cancelBubble = true ;
}

function Doc_OnSelectionChange()
{
	FCK.Events.FireEvent( "OnSelectionChange" ) ;
}

FCK.InitializeBehaviors = function( dontReturn )
{
	// Set the focus to the editable area when clicking in the document area.
	// TODO: The cursor must be positioned at the end.
	this.EditorDocument.attachEvent( 'onmousedown', Doc_OnMouseDown ) ;
	this.EditorDocument.attachEvent( 'onmouseup', Doc_OnMouseDown ) ;

	// Intercept pasting operations
	this.EditorDocument.body.attachEvent( 'onpaste', Doc_OnPaste ) ;

	// Disable Right-Click and shows the context menu.
	this.EditorDocument.attachEvent('oncontextmenu', Doc_OnContextMenu ) ;

	// Check if key strokes must be monitored.
	if ( FCKConfig.UseBROnCarriageReturn || FCKConfig.TabSpaces > 0 )
	{
		// Build the "TAB" key replacement.
		if ( FCKConfig.TabSpaces > 0 )
		{
			window.FCKTabHTML = '' ;
			for ( i = 0 ; i < FCKConfig.TabSpaces ; i++ )
				window.FCKTabHTML += "&nbsp;" ;
		}

		this.EditorDocument.attachEvent("onkeydown", Doc_OnKeyDown ) ;
	}

	this.EditorDocument.attachEvent("onkeydown", Doc_OnKeyDownUndo ) ;

	this.EditorDocument.attachEvent("ondblclick", Doc_OnDblClick ) ;

	// Catch cursor movements
	this.EditorDocument.attachEvent("onselectionchange", Doc_OnSelectionChange ) ;

	//Enable editing
//	this.EditorDocument.body.contentEditable = true ;
}

FCK.Focus = function()
{
	try
	{
		if ( FCK.EditMode == FCK_EDITMODE_WYSIWYG )
			FCK.EditorDocument.body.focus() ;
		else
			document.getElementById('eSourceField').focus() ;
	}
	catch(e) {}
}

FCK.SetHTML = function( html, forceWYSIWYG )
{
	if ( forceWYSIWYG || FCK.EditMode == FCK_EDITMODE_WYSIWYG )
	{
		// TODO: Wait stable version and remove the following commented lines.
		// In IE, if you do document.body.innerHTML = '<p><hr></p>' it throws a "Unknow runtime error".
		// To solve it we must add a fake (safe) tag before it, and then remove it.
		// this.EditorDocument.body.innerHTML = '<div id="__fakeFCKRemove__">&nbsp;</div>' + html.replace( FCKRegexLib.AposEntity, '&#39;' ) ;
		// this.EditorDocument.getElementById('__fakeFCKRemove__').removeNode(true) ;

		var sHtml ;

		if ( FCKConfig.FullPage )
		{
			var sHtml =
				FCK._BehaviorsStyle +
				'<link href="' + FCKConfig.FullBasePath + 'css/fck_internal.css' + '" rel="stylesheet" type="text/css" _fcktemp="true" />' ;

			if ( FCK.TempBaseTag.length > 0 && !FCKRegexLib.HasBaseTag.test( html ) )
				sHtml += FCK.TempBaseTag ;

			sHtml = html.replace( FCKRegexLib.HeadCloser, sHtml + '</head>' ) ;
		}
		else
		{
			sHtml =
				FCKConfig.DocType +
				'<html dir="' + FCKConfig.ContentLangDirection + '"' ;
			
			if ( FCKConfig.IEForceVScroll )
				sHtml += ' style="overflow-y: scroll"' ;
			
			sHtml +=
				'><head><title></title>' +
				'<link href="' + FCKConfig.EditorAreaCSS + '" rel="stylesheet" type="text/css" />' +
				'<link href="' + FCKConfig.FullBasePath + 'css/fck_internal.css' + '" rel="stylesheet" type="text/css" _fcktemp="true" />' ;

			sHtml += FCK._BehaviorsStyle ;
			sHtml += FCK.TempBaseTag ;
			sHtml += '</head><body>' + html  + '</body></html>' ;
		}

		this.EditorDocument.open( '', '_self', '', true ) ;
		this.EditorDocument.write( sHtml ) ;
		this.EditorDocument.close() ;

		this.InitializeBehaviors() ;
		this.EditorDocument.body.contentEditable = true ;

		FCK.OnAfterSetHTML() ;

		// TODO: Wait stable version and remove the following commented lines.
//		this.EditorDocument.body.innerHTML = '' ;
//		if ( html && html.length > 0 )
//			this.EditorDocument.write( html ) ;

//		this.EditorDocument.dir = FCKConfig.ContentLangDirection ;
	}
	else
		document.getElementById('eSourceField').value = html ;
}

FCK.InsertHtml = function( html )
{
	FCK.Focus() ;

	FCKUndo.SaveUndoStep() ;

	// Gets the actual selection.
	var oSel = FCK.EditorDocument.selection ;

	// Deletes the actual selection contents.
	if ( oSel.type.toLowerCase() != "none" )
		oSel.clear() ;

	// Inset the HTML.
	oSel.createRange().pasteHTML( html ) ;
}