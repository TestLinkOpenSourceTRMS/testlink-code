{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_jsTree.tpl,v 1.3 2007/10/18 20:06:29 schlundus Exp $ *}
{* Purpose: smarty template - include tree menu javascripts and css *}
{if $treeKind == "DTREE"} 
	<link rel="stylesheet" href="third_party/dtree/dtree.css" type="text/css" />
	<script type="text/javascript" src="third_party/dtree/dtree.js"></script>
{elseif $treeKind == "JTREE"} 
	<style media="all" type="text/css">@import "third_party/jtree/tree.css";</style>
	<script type="text/javascript" src='third_party/jtree/tree.js'></script>
	<script type="text/javascript" src='third_party/jtree/tree_tpl.js'></script>
{elseif $treeKind == "LAYERSMENU"} 
	<style type="text/css">@import url("{$basehref}{$smarty.const.TL_TREEMENU_CSS}");</style>
 	<style type="text/css">@import url("third_party/phplayersmenu/layerstreemenu-hidden.css");</style>
 	<script type="text/javascript" src="third_party/phplayersmenu/libjs/layersmenu-browser_detection.js"></script>
 	<script type="text/javascript" src="third_party/phplayersmenu/libjs/layerstreemenu-cookies.js"></script>
{/if}