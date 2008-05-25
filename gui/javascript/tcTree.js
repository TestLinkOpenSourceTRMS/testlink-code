/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcTree.js,v 1.1 2008/05/25 14:43:33 franciscom Exp $

Created using EXT JS examples
Definion for tree used to show test cases specification
Author: franciscom - 20080525

*/
Ext.BLANK_IMAGE_URL = fRoot+'third_party/ext-2.0/images/default/s.gif';
Ext.onReady(function(){
    // shorthand
    var Tree = Ext.tree;
    
    var tree = new Tree.TreePanel({
        el:treeCfg.tree_div_id,
        useArrows:true,
        autoScroll:true,
        animate:true,
        enableDD:true,
        containerScroll: true, 
        loader: new Tree.TreeLoader({
            dataUrl:treeCfg.loader
        })
    });

    // set the root node
    var root = new Tree.AsyncTreeNode({
        text: treeCfg.root_name,
        draggable:false,
        id:treeCfg.root_id,
        href:treeCfg.root_href
    });
    tree.setRootNode(root);

    // render the tree
    tree.render();
    root.expand();
});