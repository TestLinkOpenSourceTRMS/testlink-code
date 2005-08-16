{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_htmlArea.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - set precondition for htmlArea *}

<script type="text/javascript">
	_editor_url = "{$basehref}third_party/htmlarea/";
	_editor_lang = "en";
</script>
<script type="text/javascript" src="{$basehref}third_party/htmlarea/htmlarea.js"></script>
<script type="text/javascript">
	var config = new HTMLArea.Config(); // create a new configuration object
	config.toolbar = [
		[ "fontname", "space", "fontsize", "space",
		  "formatblock", "space",
		  "bold", "italic", "underline", "separator",
		  "subscript", "superscript", "separator",
		  "undo", "redo" ],
				
		[ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator",
		  "insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator",
		  "forecolor", "hilitecolor", "separator",
		  "inserthorizontalrule", "createlink", "inserttable", "separator",
		  "htmlmode", "popupeditor" ]
	];	
</script>
