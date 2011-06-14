/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: execTreeWithMenu.js,v 1.4.4.1 2010/11/21 16:54:44 asimon83 Exp $


Created using EXT JS examples.
This code has following features:

- tree will created ONLY using COMPLETE TREE Description.
  This description is taken from global Javascript object treeCfg,
  usign property children -> treeCfg.children

- Drag & Drop is not supported

This code has been used in following TL features

- Test plan printing
- Test Execution assignment on test plans

Author: franciscom - 20080525

@TODO: (20080821 - franciscom) 
       today this code is copy of tcTree.js, that's not good for mantainance.
       Need to find a way to create common library

rev:
    20101121 - asimon - BUGID 4042: "Expand/Collapse" Button for Trees
    20080620 - franciscom - added code to save/restore tree state
                            using example found on Ext JS forum
    Ext JS Forums > Ext JS General Forums > Ext: Examples and Extras > Saving tree state example
                            
                            
*/
function TreePanelState(mytree,cookiePrefix) 
{
    this.mytree = mytree;
    this.cookiePrefix = cookiePrefix;
}

TreePanelState.prototype.init = function() 
{
    this.cp = new Ext.state.CookieProvider();
    // this.state = this.cp.get('TLExecTreePanelState_' + this.mytree.id, new Array() );
    this.state = this.cp.get(this.cookiePrefix + this.mytree.id, new Array() );

}

TreePanelState.prototype.saveState = function(newState) 
{
    this.state = newState;
    // this.cp.set('TLExecTreePanelState_' + this.mytree.id, this.state);
    this.cp.set(this.cookiePrefix + this.mytree.id, this.state);
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
        	// BUGID 4196 - tree not properly restored
            var path = stateToRestore[i];
            this.mytree.expandPath(path);
        } 
        catch(e) 
        {
            // ignore invalid path, seems to be remove in the datamodel
            // TODO fix state at this point
        }
    }
}
Ext.BLANK_IMAGE_URL = fRoot+extjsLocation+'/images/default/s.gif';

Ext.onReady(function(){

    // to manage drag & drop
    var oldPosition = null;
    var oldNextSibling = null;
    
    var json_tree = treeCfg.children;


    // shorthand
    var Tree = Ext.tree;
    
    tree = new Tree.TreePanel({
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
    
    // 20080821 - franciscom
    var treeState = new TreePanelState(tree,treeCfg.cookiePrefix);                     
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