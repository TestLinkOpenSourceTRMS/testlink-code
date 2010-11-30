/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tl_ckeditor_config.js,v 1.6 2010/11/30 08:49:37 mx-julian Exp $

Configure CKEditor
See: http://docs.cksource.com/ for more information

List of all config parameters that can be set here can be found on:
http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html
*/

CKEDITOR.editorConfig = function( config )
{
	// You can use theses definitions if you buy ckfinder
	// more informations on http://ckfinder.com/
	
	//config.filebrowserBrowseUrl = '/third_party/ckfinder/ckfinder.html';
	//config.filebrowserImageBrowseUrl = '/third_party/ckfinder/ckfinder.html?Type=Images';
	//config.filebrowserFlashBrowseUrl = '/third_party/ckfinder/ckfinder.html?Type=Flash';
	//config.filebrowserUploadUrl = '/third_party/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
	//config.filebrowserImageUploadUrl = '/third_party/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
	//config.filebrowserFlashUploadUrl = '/third_party/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
	
	// choose your prefered ckedtior skin
	// available skins: kama, office2003, v2
	config.skin = 'office2003';
	
	// do not check "Replace actual contents" checkbox as default
	config.templates_replaceContent = false;
	
	// default Toolbar
	config.toolbar_Testlink = 
	[
		['Source','Templates','SpellChecker','Find','Undo','Redo','-',
		 'NumberedList','BulletedList','-',
		 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-',
		 'Outdent','Indent','-',
		 'Table','HorizontalRule',],
		 '/',
		 ['Format','Bold','Italic','Underline','Strike','-',
		  'Subscript','Superscript','-','TextColor','BGColor','RemoveFormat','-',
		  'Link','Image','Anchor','SpecialChar']
	];
	
	// Toolbar with all available features - can be used as template for custom toolbars
	// '-' creates toolbar seperator
	// '/' creates a new toolbar "line"
	// [...] defines sub-toolbars
	config.toolbar_Full =
	[
		['Source','-','NewPage','Preview','-','Templates'],
		['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','SpellChecker'],
		['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		'/',
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink','Anchor'],
		['Image','Table','HorizontalRule','PageBreak'],
		'/',
		['Style','Format','Font','FontSize'],
		['TextColor','BGColor'],
		['Maximize','ShowBlocks','-','About']
	];
};