{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* @filesource frmInner.tpl *}
{* Purpose: smarty template - inner frame for workarea *}
<!DOCTYPE html>
<html>
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
    	<link rel="stylesheet" type="text/css" href="{$basehref}{$tlCfg->theme_dir}/css/frame.css">
    	<script type="text/javascript" src="{$basehref}third_party/jquery/{$smarty.const.TL_JQUERY}" language="javascript"></script>
    	<script type="text/javascript" src="{$basehref}third_party/chosen/chosen.jquery.js"></script>
    	{include file="bootstrap.inc.tpl"}
    </head>
    <body>
      <iframe src="{$treeframe}" name="treeframe" id="treeframe" class="treeframe"></iframe>
      <iframe src="{$workframe}" name="workframe" id="workframe" class="workframe"></iframe>
    </body>
</html>
