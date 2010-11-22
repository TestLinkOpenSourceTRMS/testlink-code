CKEDITOR.editorConfig = function( config )
{

	config.filebrowserBrowseUrl = '/third_party/ckfinder/ckfinder.html';
	config.filebrowserImageBrowseUrl = '/third_party/ckfinder/ckfinder.html?Type=Images';
	config.filebrowserFlashBrowseUrl = '/third_party/ckfinder/ckfinder.html?Type=Flash';
	config.filebrowserUploadUrl = '/third_party/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
	config.filebrowserImageUploadUrl = '/third_party/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
	config.filebrowserFlashUploadUrl = '/third_party/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
	
	//kama
	//office2003
	//v2
	config.skin = 'kama';
	
	config.toolbar_Testlink = 
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