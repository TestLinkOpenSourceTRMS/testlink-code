/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcTree.js,v 1.5 2008/06/23 06:24:50 franciscom Exp $

Created using EXT JS examples
Definion for tree used to show test cases specification
This tree is used in different TL features, where sometimes drag & drop can not be used.
Author: franciscom - 20080525

rev:
    20080607 - franciscom -
    20080603 - franciscom - drag & drop disabled
    20080528 - franciscom - added code to save/restore tree state
                            using example found on Ext JS forum
    Ext JS Forums > Ext JS General Forums > Ext: Examples and Extras > Saving tree state example
                            
                            
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

/* Improvement - cookie name prefix
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
    // this.cp.set('TreePanelState_' + this.mytree.id, this.state);
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
        href:treeCfg.root_href
    });
    
    
    tree.setRootNode(root);

    // render the tree
    tree.render();
    
    // 20080622 - franciscom
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
                    
                    /*
                    alert('oldParent:'+ oldParent.id + ' newParent:'+ newParent.id + 
                          ' nodeId:'+node.id + '\n nodePosition:' + newNodePosition+ 
                          'oldPosition:' + oldPosition + 
                          '\n oldNextSibling:' +  oldNextSibling +
                          '\n nextSibling:' +  node.nextSibling);
                    */      
                    writeNodePositionToDB(newParent,node.id,oldParent.id,newParent.id,newNodePosition);                    
                    });                                          
    //
                                                                 
    // restore last state from tree or to the root node as default
    treeState.restoreState(tree.root.getPath());                  
    
    //root.expand();
    
});