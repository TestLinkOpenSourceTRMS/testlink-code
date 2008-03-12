{*
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: drag_drop.inc.tpl,v 1.3 2008/03/12 21:27:37 schlundus Exp $

Drag & drop CSS and JS pieces.

Code from:  (C) www.dhtmlgoodies.com, July 2006

Update log:
This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.

Terms of use:
You are free to use this script as long as the copyright message is kept intact.
For more detailed license information, see http://www.dhtmlgoodies.com/index.html?page=termsOfUse
Thank you!
www.dhtmlgoodies.com
Alf Magne Kalleland


*}
<style media="all" type="text/css">@import "{$basehref}{$smarty.const.TL_DRAG_DROP_FOLDER_CSS}";</style>
<style media="all" type="text/css">@import "{$basehref}{$smarty.const.TL_DRAG_DROP_CONTEXT_MENU_CSS}";</style>

<!--
Important:
include order of these JS files is very important:
     1) ajax.js
     2) context-menu.js
     3) drag-drop-folder-tree.js
-->
<script type="text/javascript" src="{$basehref}{$smarty.const.TL_DRAG_DROP_JS_DIR}ajax.js"></script>
<script type="text/javascript" src="{$basehref}{$smarty.const.TL_DRAG_DROP_JS_DIR}context-menu.js"></script>
<script type="text/javascript" src="{$basehref}{$smarty.const.TL_DRAG_DROP_JS_DIR}drag-drop-folder-tree.js"></script>

{literal}
<script type="text/javascript">
/*
  function:

  args :
         basehref: needed to create absolute URL to images
         tree_id : html of ul used to implement tree with drag and drop

  returns:

*/
var treeObj;

function init_drag_and_drop(basehref,tree_id)
{
	treeObj = new JSDragDropTree(basehref);
	treeObj.setTreeId(tree_id);
	treeObj.setMaximumDepth(7);

	// If you want to show a message when maximum depth is reached, i.e. on drop.
	treeObj.setMessageMaximumDepthReached('Maximum depth reached');
	treeObj.initTree();
	treeObj.expandAll();
}

/*
  function: dnd_save_tree

  args :

  returns:

*/
function dnd_save_tree(form_id,order_container_name,elem_name)
{
  var f=document.getElementById(form_id);
	f.elements[order_container_name].value = treeObj.getNodeOrders();
	f.elements[elem_name].disabled = '';
	f.elements[elem_name].value = 1;
	f.submit();

}
</script>
{/literal}
