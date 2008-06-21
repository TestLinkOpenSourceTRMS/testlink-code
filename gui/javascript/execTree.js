/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: execTree.js,v 1.1 2008/06/21 15:58:56 franciscom Exp $

Created using EXT JS examples
Definion for tree used to show test cases specification
This tree is used in different TL features, where sometimes drag & drop can not be used.
Author: franciscom - 20080525

rev:
    20080620 - franciscom - added code to save/restore tree state
                            using example found on Ext JS forum
    Ext JS Forums > Ext JS General Forums > Ext: Examples and Extras > Saving tree state example
                            
                            
*/
function TreePanelState(mytree) 
{
    this.mytree = mytree;
}

TreePanelState.prototype.init = function() 
{
    this.cp = new Ext.state.CookieProvider();
    this.state = this.cp.get('TLExecTreePanelState_' + this.mytree.id, new Array() );
}

TreePanelState.prototype.saveState = function(newState) 
{
    this.state = newState;
    this.cp.set('TLExecTreePanelState_' + this.mytree.id, this.state);
}

TreePanelState.prototype.onExpand = function(node) 
{
    var currentPath = node.getPath();
    var newState = new Array();
    for (var i = 0; i < this.state.length; ++i) 
    {
        var path = this.state[i];
        if (currentPath.indexOf(path) == -1) 
        {
            // this path does not already exist
            newState.push(path);
        }
    }
    // now ad the new path
    newState.push(currentPath);
    this.saveState(newState);
}

TreePanelState.prototype.onCollapse = function(node)
{
    var closedPath = closedPath = node.getPath();
    var newState = new Array();
    for (var i = 0; i < this.state.length; ++i) 
    {
        var path = this.state[i];
        if (path.indexOf(closedPath) == -1) 
        {
            // this path is not a subpath of the closed path
            newState.push(path);
        }
    }
    if (newState.length == 0) 
    {
        var parentNode = node.parentNode;
        newState.push((parentNode == null ? this.mytree.pathSeparator : parentNode.getPath()));
    }
    this.saveState(newState);
}

TreePanelState.prototype.restoreState = function(defaultPath) 
{
    if (this.state.length == 0) 
    {
        var newState = new Array(defaultPath);
        this.saveState(newState);
        this.mytree.expandPath(defaultPath);
        return;
    }
    var stateToRestore=this.state;
    for (var i = 0; i < stateToRestore.length; ++i) 
    {
        // activate all path strings from the state
        try 
        {
            var path = this.state[i];
            this.mytree.expandPath(path);
        } 
        catch(e) 
        {
            // ignore invalid path, seems to be remove in the datamodel
            // TODO fix state at this point
        }
    }
}

Ext.BLANK_IMAGE_URL = fRoot+'third_party/ext-2.0/images/default/s.gif';

Ext.onReady(function(){

    // to manage drag & drop
    var oldPosition = null;
    var oldNextSibling = null;
    
    var json_tree = treeCfg.children;


    // shorthand
    var Tree = Ext.tree;
    
    var tree = new Tree.TreePanel({
        el:treeCfg.tree_div_id,
        useArrows:true,
        autoScroll:true,
        animate:true,
        enableDD:treeCfg.enableDD,
        containerScroll: true, 
        loader: new Tree.TreeLoader()
    });

    // set the root node
    var root = new Tree.AsyncTreeNode({
        text: treeCfg.root_name,
        draggable:false,
        id:treeCfg.root_id,
        href:treeCfg.root_href,
        children:json_tree
    });
    
    
    tree.setRootNode(root);

    // render the tree
    tree.render();
    
    var treeState = new TreePanelState(tree);                     
    treeState.init();                                             
    
    // initialize event handlers                                  

    // Needed to manage save/restore tree state
    tree.on('expandnode', treeState.onExpand, treeState);             
    tree.on('collapsenode', treeState.onCollapse, treeState);         
    // 
                                                                
    // restore last state from tree or to the root node as default
    treeState.restoreState(tree.root.getPath());                  
    
    //root.expand();
    
});