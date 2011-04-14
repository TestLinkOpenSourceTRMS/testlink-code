/*  
TestLink Open Source Project - http://testlink.sourceforge.net/

Configure FCKEditor
See: http://docs.fckeditor.net/ and 
     Configuration_of_FCKEditor_and_CKFinder.pdf in docs folder
     for more information
*/

// General configuration

/* Disable server browsing
   To enable server browsing/file upload check Configuration_of_FCKEditor_and_CKFinder.pdf 
   in docs folder */
FCKConfig.LinkBrowser = false;
FCKConfig.ImageBrowser = false;
FCKConfig.FlashBrowser = false;

//disable quick upload to avoid unsorted files.
FCKConfig.LinkUpload = false ;
FCKConfig.ImageUpload = false ;
FCKConfig.FlashUpload = false ;

//use Testlink stylesheet
//FCKConfig.EditorAreaCSS = FCKConfig.BasePath + '../../../gui/themes/default/css/testlink.css' ;

//Set Skin
FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/default/';
//FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/office2003/';
//FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/silver/';


//Toolbar configuration
FCKConfig.ToolbarSets["full"] = [
	['Source','DocProps','-','Save','NewPage','Preview','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent','Blockquote','CreateDiv'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['Image','Flash','Table','Rule','Smiley','SpecialChar','PageBreak'],
	'/',
	['Style','FontFormat','FontName','FontSize'],
	['TextColor','BGColor'],
	['FitWindow','ShowBlocks','-','About']
] ;

FCKConfig.ToolbarSets["tl_default"] = [
	['Cut','Copy','Paste','PasteText','PasteWord','Find','Replace','SelectAll','-',
	'Anchor','Bold','Italic','Underline','OrderedList','UnorderedList','JustifyLeft'],
	'/',['FontName','FontSize','TextColor','BGColor','-','Link','Unlink','Image','Table','Rule']
] ;

FCKConfig.ToolbarSets["tl_mini"] = [
	['Anchor','Bold','Italic','Underline','OrderedList','UnorderedList','JustifyLeft'],
	'/',['FontName','FontSize','TextColor','BGColor','-','Table','Rule']
] ;

/* CKFINDER configuration
   You need to modify third_party/ckfinder/config.php - $baseUrl and set it to fckeditor_upload_area path
   refer to Configuration_of_FCKEditor_and_CKFinder.pdf */

//FCKConfig.LinkBrowserURL = '../../../ckfinder/ckfinder.html';
//FCKConfig.ImageBrowserURL = '../../../ckfinder/ckfinder.html?type=Images';
//FCKConfig.FlashBrowserURL = '../../../ckfinder/ckfinder.html?type=Flash';
//FCKConfig.LinkUploadURL = '../../../ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
//FCKConfig.ImageUploadURL = '../../../ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
//FCKConfig.FlashUploadURL = '../../../ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';