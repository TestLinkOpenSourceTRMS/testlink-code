{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcTree.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - show test specification tree menu *}
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
	<input type="button" value="Refresh" onClick="javascript: parent.treeframe.location.reload();" />
</form>

</body>
</html>