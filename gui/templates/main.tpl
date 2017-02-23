{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* @filesource main.tpl *}
{* Purpose: smarty template - main frame *}
{*******************************************************************}
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$pageCharset}" />
	<meta http-equiv="Content-language" content="en" />
	<meta name="generator" content="testlink" />
	<meta name="author" content="TestLink Development Team" />
	<meta name="copyright" content="TestLink Development Team" />
	<meta name="robots" content="NOFOLLOW" />
	<title>TestLink {$tlVersion|escape}</title>
	<meta name="description" content="TestLink - {$gui->title|default:"Main page"}" />
	<link rel="icon" href="{$basehref}{$smarty.const.TL_THEME_IMG_DIR}favicon.ico" type="image/x-icon" />
</head>

<frameset rows="{$gui->navbar_height},*" frameborder="0" framespacing="0">
	<frame src="{$gui->titleframe}" name="titlebar" scrolling="no" noresize="noresize" />
	<frame src="{$gui->mainframe}" scrolling='auto' name='mainframe' />
	<noframes>
		<body>TestLink required a frames supporting browser.</body>
	</noframes>
</frameset>

</html>
