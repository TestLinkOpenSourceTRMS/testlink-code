{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcTree.tpl,v 1.4 2005/08/26 21:01:27 schlundus Exp $ *}
{* Purpose: smarty template - show test specification tree menu *}
{*
	20050821 - scs - localized the refresh button
*}
{include file="inc_head.tpl" jsTree="yes"}

<body>

<h1>{$treeHeader}</h1>

<div class="tree" id="tree">
{if $tree eq ''}
{lang_get s='no_tc_spec_av'}
{/if}
{$tree}
</div>

<form style="margin-left: 20px;">
	<input type="button" value="{lang_get s='button_refresh'}" onClick="javascript: parent.treeframe.location.reload();" />
</form>

</body>
</html>