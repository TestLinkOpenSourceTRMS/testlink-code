/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tl_fckeditor_config.js,v 1.3 2009/06/07 22:03:51 havlat Exp $

Configure FCKEditor
See: http://docs.fckeditor.net/ for more information
*/

FCKConfig.ToolbarSets["tl_default"] = [
	['Cut','Copy','Paste','PasteText','PasteWord','Find','Replace','SelectAll','-',
	'Anchor','Bold','Italic','Underline','OrderedList','UnorderedList','JustifyLeft'],
	'/',['FontName','FontSize','TextColor','BGColor','-','Link','Unlink','Image','Table','Rule']
] ;

/* Just an example */
FCKConfig.ToolbarSets["tl_mini"] = [
	['Anchor','Bold','Italic','Underline','OrderedList','UnorderedList','JustifyLeft'],
	'/',['FontName','FontSize','TextColor','BGColor','-','Table','Rule']
] ;

/* Disable a server browsing */
FCKConfig.LinkBrowser = false;
FCKConfig.LinkUpload = false;
