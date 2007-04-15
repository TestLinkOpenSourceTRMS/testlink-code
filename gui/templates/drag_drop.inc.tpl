{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
$Id: drag_drop.inc.tpl,v 1.1 2007/04/15 10:59:18 franciscom Exp $   

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
--->     
<script type="text/javascript" src="{$basehref}{$smarty.const.TL_DRAG_DROP_JS_DIR}ajax.js"></script>
<script type="text/javascript" src="{$basehref}{$smarty.const.TL_DRAG_DROP_JS_DIR}context-menu.js"></script>
<script type="text/javascript" src="{$basehref}{$smarty.const.TL_DRAG_DROP_JS_DIR}drag-drop-folder-tree.js"></script>