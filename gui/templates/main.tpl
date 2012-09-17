{* 
Testlink Open Source Project - http://testlink.sourceforge.net/

main frame

@filesource	main.tpl
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$tlCfg->pageCharset}" />
	<meta http-equiv="Content-language" content="en" />
	<meta name="generator" content="testlink" />
	<meta name="author" content="TestLink Development Team" />
	<meta name="copyright" content="TestLink Development Team" />
	<meta name="robots" content="NOFOLLOW" />
	<title>TestLink {$tlVersion|escape}</title>
	<meta name="description" content="TestLink - {$title|default:"Main page"}" />
	<link rel="icon" href="{$basehref}{$tlImages.favicon}" type="image/x-icon" />
</head>

{* 
Development notes
$titleframe and $mainframe initialized in: index.php, linkto.php
*}
<frameset rows="70,*" frameborder="0" framespacing="0">
	<frame src="{$titleframe}" name="titlebar" scrolling="no" noresize="noresize" />
	<frame src="{$mainframe}" name="mainframe" scrolling='auto'  />
	<noframes>
		<body>TestLink required a frames supporting browser.</body>
	</noframes>
</frameset>
</html>