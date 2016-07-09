/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource tcTree.js

*** Created using EXT JS examples.

Code to save/restore tree state using example found on Ext JS forum
Ext JS Forums > Ext JS General Forums > Ext: Examples and Extras > Saving tree state example

This code has following features:

- tree will created using a tree loader code, that get tree information
  step by step (on user request) using AJAX calls.
  
- Drag & Drop is supported

This code has been used in following TL features

- Test Specification Tree
- Test Plan Design (test case assignment/link to test plan)

Author: franciscom - 20080525

@internal revisions

                            
*/

function writeNodePositionToDB(newparent,nodeid,oldparentid,newparentid,nodeorder)
{
    var children=newparent.childNodes
    var serial=new Array(1);
    var idx=0;
    var loopqty=children.length;
    var child;
    var nodelist='';
    
    
    for(idx=0; idx < loopqty; idx++)
    {
       child=children[idx];
       serial[idx]=child.id;
    }
    nodelist=serial.join(',');
    
    if( oldparentid != newparentid )
    {
        Ext.Ajax.request({
            url: treeCfg.dragDropBackEndUrl,
            params: {doAction: 'changeParent', nodeid: nodeid, 
                     oldparentid: oldparentid, newparentid: newparentid,                   
                     top_or_bottom: 'bottom'}
         });
    } 
    else
    {
        Ext.Ajax.request({
            url: treeCfg.dragDropBackEndUrl,
            params: {doAction: 'doReorder', nodeid: nodeid, 
                     oldparentid: oldparentid, newparentid: newparentid, 
                     nodelist: nodelist,
                     nodeorder: nodeorder}
         });

    }
}

/* Improvement - cookie name prefix */
function TreePanelState(mytree,cookiePrefix) 
{
    this.mytree = mytree;
    this.cookiePrefix = cookiePrefix;
}

TreePanelState.prototype.init = function() 
{
    this.cp = new Ext.state.CookieProvider();
    // this.state = this.cp.get('TreePanelState_' + this.mytree.id, new Array() );
    this.state = this.cp.get(this.cookiePrefix + this.mytree.id, new Array() );
}

TreePanelState.prototype.saveState = function(newState) 
{
    this.state = newState;
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


    // shorthand
    var Tree = Ext.tree;
    
    tree = new Tree.TreePanel({
        el:treeCfg.tree_div_id,
        useArrows:true,
        autoScroll:true,
        animate:true,
        enableDD:treeCfg.enableDD,
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

    tree.render();
    
    var treeState = new TreePanelState(tree,treeCfg.cookiePrefix);                     
    treeState.init();                                             
    
    // initialize event handlers                                  

    // Needed to manage save/restore tree state
    tree.on('expandnode', treeState.onExpand, treeState);             
    tree.on('collapsenode', treeState.onCollapse, treeState);         
    // 

    // Needed to manage drag and drop back-end actions
    tree.on('startdrag', function(tree, node, event){
                                  oldPosition = node.parentNode.indexOf(node);
                                  oldNextSibling = node.nextSibling;
    });
    
    tree.on('movenode', function(tree,node,oldParent,newParent,newNodePosition ){ 
              writeNodePositionToDB(newParent,node.id,oldParent.id,newParent.id,newNodePosition);                    
    });                                          
    
    // restore last state from tree or to the root node as default
    treeState.restoreState(tree.root.getPath());                  
});