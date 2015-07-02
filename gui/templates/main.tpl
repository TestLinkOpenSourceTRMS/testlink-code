{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: main.tpl,v 1.8 2009/06/29 10:45:24 havlat Exp $ *}
{* Purpose: smarty template - main frame *}
{*******************************************************************}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
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

<frameset rows="70,*" frameborder="0" framespacing="0">
	<frame src="{$gui->titleframe}" name="titlebar" scrolling="no" noresize="noresize" />
	<frame src="{$gui->mainframe}" scrolling='auto' name='mainframe' />
	<noframes>
		<body>TestLink required a frames supporting browser.</body>
	</noframes>
</frameset>

</html>
