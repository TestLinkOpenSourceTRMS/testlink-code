/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: treebyloader.js,v 1.2 2008/09/02 16:39:04 franciscom Exp $

Created using EXT JS examples.
This code has following features:

- tree will created using a tree loader code, that get tree information
  step by step (on user request) using AJAX calls.
  
- Drag & Drop is supported

This code has been used in following TL features

- Test Specification Tree
- Test Plan Design (test case assignment/link to test plan)


Author: franciscom - 20080525

rev:
    20080831 - franciscom - added beforemovenode() logic
    
    Ext JS Forums > Ext JS General Forums > Ext: Examples and Extras > Saving tree state example
*/

/*
  function: checkMovement()
            check if node can be move to new parent.
            called by beforemovenode() event

  args: same interface that beforemovenode()
  
  returns: true -> movement can be made.

*/
function checkMovement(newparent,node,oldparentid,newparentid,nodeorder)
{
    var status=true;
    var newparent_node_type =newparent.attributes.testlink_node_type;
    var node_type = node.attributes.testlink_node_type;
    
    switch(node_type)
    {
        case 'requirement':
            switch(newparent_node_type)
            {
                case 'testproject':
                    status=false;
                break;
            }   
        break;

        // While we do not manage unlimited tree depth, need
        // to use this control
        case 'requirement_spec':
            switch(newparent_node_type)
            {
                case 'requirement_spec':
                    status=false;
                break;
            }   
        break;
               
    }
    return status;
    
}


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


    // shorthand
    var Tree = Ext.tree;
    
    var tree = new Tree.TreePanel({
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
        href:treeCfg.root_href,
        testlink_node_type:treeCfg.root_testlink_node_type
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
    
    
    // 20080831 - franciscom
    // Class Ext.tree.TreePanel - event
    // Want to avoid some movements like:
    // A requirement CAN NOT BE direct child of test project
    //
    if( treeCfg.enableDD && treeCfg.useBeforeMoveNode )
    {
        tree.on('beforemovenode', function(tree,node,oldParent,newParent,newNodePosition ){ 
                  return checkMovement(newParent,node,oldParent.id,newParent.id,newNodePosition);                    
        });                                          
    }   
    
    // restore last state from tree or to the root node as default
    treeState.restoreState(tree.root.getPath());                  
    
    //root.expand();
    
});