{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: frmInner.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - inner frame for workarea *}
{*******************************************************************}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$pageCharset}" />
	<meta http-equiv="Content-language" content="en" />
	<meta http-equiv="expires" content="-1" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="generator" content="testlink" />
	<meta name="author" content="Martin Havlat" />
	<meta name="copyright" content="GNU" />
	<meta name="robots" content="NOFOLLOW" />
	<base href="{$basehref}" />
	<title>TestLink Inner Frame</title>
	<style media="all" type="text/css">@import "{$css}";</style>
</head>

<frameset cols="{$treewidth|default:"30%"},*" border="1" frameborder="1" framespacing="0">
	<frame src="{$treeframe}" name="treeframe" scrolling="auto" />
	<frame src="{$workframe}" name="workframe" scrolling="auto" />
</frameset>

</html>
