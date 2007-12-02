{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_tinymce_init.tpl,v 1.1 2007/12/02 17:36:30 franciscom Exp $
Purpose: include files for:
         Ext JS Library - Copyright(c) 2006-2007, Ext JS, LLC.
         licensing@extjs.com - http://www.extjs.com/license


rev :
     20071201 - franciscom - 
*}
{literal}
<script language="javascript" type="text/javascript">

tinyMCE.init({
	mode : "textareas",
  plugins : "table",
	button_tile_map : true, 
	theme : "advanced",
  theme_advanced_buttons1 : "fontselect,fontsizeselect,bold,italic,underline,strikethrough",
  theme_advanced_buttons2 : "bullist,numlist,|,backcolor,forecolor,|,undo,redo",
  theme_advanced_buttons3 : "tablecontrols",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left" 
});


/*
tinyMCE.init({
	mode : "textareas",
  plugins : "table",
	button_tile_map : true, 
	theme : "advanced",
  theme_advanced_buttons1 : "fontselect,fontsizeselect,bold,italic,underline,strikethrough," +
                            "|,bullist,numlist,|,backcolor,forecolor,|,undo,redo",
  theme_advanced_buttons2 : "tablecontrols",
  theme_advanced_buttons3 : "",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left" 
});
*/

/*
tinyMCE.init({
	mode : "textareas",
  plugins : "table",
	button_tile_map : true, 
	theme : "simple",
});
*/

</script>
{/literal}
