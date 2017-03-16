{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* @filesource frmInner.tpl *}
{* Purpose: smarty template - inner frame for workarea *}
<!DOCTYPE html>
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

<frameset cols="{$treewidth|default:"30%"},*" border="5" frameborder="10" framespacing="1">
	<frame src="{$treeframe}" name="treeframe" scrolling="auto" />
	<frame src="{$workframe}" name="workframe" scrolling="auto" />
</frameset>

</html>
