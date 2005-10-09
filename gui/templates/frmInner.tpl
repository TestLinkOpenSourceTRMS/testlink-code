{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: frmInner.tpl,v 1.4 2005/10/09 18:13:48 schlundus Exp $ *}
{* Purpose: smarty template - inner frame for workarea *}
{*
 20050810 - am - added frameborder/border for displaying a border fix for 0000138
*}
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

<frameset cols="{$treewidth|default:"30%"},*" border="5" 
          frameborder="10" framespacing="1">
	<frame src="{$treeframe}" name="treeframe" scrolling="auto" />
	<frame src="{$workframe}" name="workframe" scrolling="auto" />
</frameset>

</html>
