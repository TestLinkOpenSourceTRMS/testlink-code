/*  
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	treebyloader.js

Created using EXT JS examples.
Ext JS Forums > Ext JS General Forums > Ext: Examples and Extras > Saving tree state example

This code has following features:

- tree will created using a tree loader code, that get tree information
  step by step (on user request) using AJAX calls.
  
- Drag & Drop is supported

This code has been used in following TL features

- Test Specification Tree
- Test Plan Design (test case assignment/link to test plan)


Context Menu logic adapted from:
http://remotetree.extjs.eu/
Ext.ux.tree.RemoteTreePanel by Saki - ver.: 1.0


Author: franciscom - 20080525

@internal revisions

*/


/*
  function: checkCtrlKey()

  args: dropEventObject
  
  beforenodedrop
    public event beforenodedrop
    Fires when a DD object is dropped on a node in this tree for preprocessing. 
    Return false to cancel the drop. 
  
  The dropEvent passed to handlers has the following properties:
    tree - The TreePanel
    target - The node being targeted for the drop
    data - The drag data from the drag source
    point - The point of the drop - append, above or below
    source - The drag source
    rawEvent - Raw mouse event
    dropNode - Drop node(s) provided by the source OR you can supply node(s) to be inserted by setting them on this object.
    cancel - Set this to true to cancel the drop.
  
  Subscribers will be called with the following parameters:
    dropEvent : Object
  This event is defined by TreePanel.  
  
 
  returns: 

  rev: 
*/
function checkCtrlKey(dropEventObject)
{
    var status=true;
		// dumpProps(dropEventObject.dropNode.attributes);
		
		if (dropEventObject.rawEvent.ctrlKey) 
		{
      dropEventObject.tree.initialConfig.copyOrMove = 'copy';

      // Ext.Ajax.request({
      //     url: treeCfg.dragDropBackEndUrl,
      //     params: {doAction: 'copyNode', nodeid: nodeid, 
      //              oldparentid: oldparentid, newparentid: newparentid,                   
      //              top_or_bottom: 'bottom'}
      // });



		  // dropEventObject.dropNode = copyDropNode(dropEventObject.dropNode);
      dropEventObject.dropNode = new Ext.tree.AsyncTreeNode(dropEventObject.dropNode.attributes);





		  // status=false;
		}
		else
		{
      dropEventObject.tree.initialConfig.copyOrMove = 'move';
		}  
    
    // alert('checkCtrlKey ' + dropEventObject.tree.initialConfig.copyOrMove);
    
    return status;
}



/*
  function: checkMovement()
            check if node can be move to new parent.
            called by beforemovenode() event

  args: same interface that beforemovenode()
  
  returns: true -> movement can be made.

  @internal revisions
  @since 1.9.7
  20130407 - franciscom - added config for testcase, testsuite
*/
function checkMovement(newparent,node,oldparentid,newparentid,nodeorder)
{
    var status=true;
    var newparent_node_type =newparent.attributes.testlink_node_type;
    var node_type = node.attributes.testlink_node_type;
    
    switch(node_type)
    {
      case 'requirement':
      case 'requirement_spec':
        if( node.attributes.forbidden_parent == newparent_node_type )
        {                                                            
          status=false;                                              
        }                                                            
      break;

      case 'testcase':
      case 'testsuite':
        if( node.attributes.forbidden_parent == newparent_node_type )
        {                                                            
          status=false;                                              
        }                                                            
      break;
    }
    return status;
}


/*
  function: writeNodePositionToDB()

  args: same interface that beforemovenode()
  
  returns: 

  rev: 
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
/* 20100602 */
function TreePanelState(mytree,cookiePrefix) 
{
    this.mytree = mytree;
    this.cookiePrefix = cookiePrefix;
    this.contextMenu = new Ext.menu.Menu({id: 'mainContext'});
    this.clickedNode = null;
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

TreePanelState.prototype.menuShow = function(node,eventObj) 
{

  this.clickedNode = node;
  switch(node.attributes.testlink_node_type)
  {
    case 'testsuite':
      this.contextMenu.show(node.ui.getEl());           
    break;

    case 'testcase':
      this.contextMenu.show(node.ui.getEl());           
    break;
  }

}



//    
// function displayAPI(item,eventObject,treeObj)
// {
//   alert('this' + this);
//   // dumpProps(this);
//   alert(treeObj.clickedNode);
//   alert(treeObj.clickedNode.id);
//   alert('displayAPI');
//   alert(item.text);
// 
// }
// 
   
/*
  function: dumpProps() 
            utility for debug

  args: 
  
  returns: 

  rev: 
*/
function dumpProps(obj, parent) {
   // Go through all the properties of the passed-in object
   for (var i in obj) {
      // if a parent (2nd parameter) was passed in, then use that to
      // build the message. Message includes i (the object's property name)
      // then the object's property value on a new line
      if (parent) { var msg = parent + "." + i + "\n" + obj[i]; } else { var msg = i + "\n" + obj[i]; }
      // Display the message. If the user clicks "OK", then continue. If they
      // click "CANCEL" then quit this level of recursion
      if (!confirm(msg)) { return; }
      // If this property (i) is an object, then recursively process the object
      if (typeof obj[i] == "object") {
         if (parent) { dumpProps(obj[i], parent + "." + i); } else { dumpProps(obj[i], i); }
      }
   }
}
// 
   
   
Ext.BLANK_IMAGE_URL = fRoot+extjsLocation+'/images/default/s.gif';
Ext.onReady(function(){

    // to manage drag & drop
    var oldPosition = null;
    var oldNextSibling = null;

    // shorthand
    var TreeWidget = Ext.tree;
    
    // added config option copyOrMove, can be used (RW) with this access path
    // .initialConfig.copyOrMove
    //
    tree = new TreeWidget.TreePanel({
        el:treeCfg.tree_div_id,
        useArrows:true,
        autoScroll:true,
        animate:true,
        contextMenu:true,  // is really needed ?
        enableDD:treeCfg.enableDD,
        containerScroll: true,
        copyOrMove: 'move', 
        loader: new TreeWidget.TreeLoader({
            dataUrl:treeCfg.loader
        })
    });

    // set the root node
    var root = new TreeWidget.AsyncTreeNode({
        text: treeCfg.root_name,
        draggable:false,
        id:treeCfg.root_id,
        href:treeCfg.root_href,
        testlink_node_type:treeCfg.root_testlink_node_type
    });
    
    
    tree.setRootNode(root);
    tree.render();

    var treeState = new TreePanelState(tree,treeCfg.cookiePrefix);                     
    
    // from http://www.extjs.com/learn/Tutorial:Ext_Menu_Widget#More_Handy_Stuff
    // If you need to call a javaScript function with a parameter use the createDelegate  to pass the parameters
    // Example:
    //
    // var ratesMenu = new Ext.menu.Menu({});
    // ratesMenu.add({text:'FCL Rates',tooltip:'FCL',handler:display_report.createDelegate(this, ['fclrates'])});
    // ratesMenu.add({text:'LCL Rates',handler:display_report.createDelegate(this, ['lclrates'])});
    //  
    // function display_report(report) {
    //     alert(report);
    // }
    // treeState.contextMenu.add(new Ext.menu.TextItem({id:'title', text: ''}));
    //
    // Hint from: Ext.ux.tree.RemoteTreePanel by Saki - ver.: 1.0
    treeState.contextMenu.add(new Ext.menu.TextItem({id:'static', text: '', 
                                                     style:'font-weight:bold;margin:0px 4px 0px 27px;line-height:18px'}));
    treeState.contextMenu.add(new Ext.menu.TextItem({id:'api', text: '', 
                                                     style:'margin:0px 4px 0px 27px;line-height:18px'}));

    treeState.contextMenu.add(new Ext.menu.TextItem({id:'dummy', text: ''}));

    // treeState.contextMenu.add(new Ext.menu.Item({id:'api', text: 'API', handler: displayAPI.createDelegate(this,treeState,true)}));
    
    // var treeState = new TreePanelState(tree,treeCfg.cookiePrefix,nodeMenu);                     
    
    
    
    treeState.init();                                             

        
    // initialize event handlers                                  
    // tree.on('contextmenu', treeState.menuShow, treeState);                                                   
    tree.on({contextmenu:{scope:treeState, fn:treeState.menuShow, stopEvent:true}});


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
    
    // 20110529 - franciscom 
    // Will comment till will be ready
    //
    // 20100908 - work on CTRL+Drag&Drop for copy (just started)
    // tree.on(
    //        'beforenodedrop', 
    //        function(dropEvent)
    //        { 
    //              return checkCtrlKey(dropEvent);                    
    //        }
    // );                                          
    
    
    
    // restore last state from tree or to the root node as default
    treeState.restoreState(tree.root.getPath());                  
    
    //
    // Thanks to :
    // http://remotetree.extjs.eu/
    // Ext.ux.tree.RemoteTreePanel by Saki - ver.: 1.0
    //    
    treeState.contextMenu.on({
				hide:{scope:treeState, fn:function() {
					treeState.clickedNode = null;
				}}
				,show:{scope:treeState, fn:function() {
					var node = treeState.clickedNode;
					var text = node.text;
					var len = text.length;
					var xx = this.contextMenu.items.get('static');
					xx.el.update(text.substr(0,len-'( )'.length));
					
					xx = this.contextMenu.items.get('api');
					xx.el.update('API ID:' + node.id);
					treeState.contextMenu.el.shadow.hide();
					treeState.contextMenu.el.shadow.show(this.contextMenu.el);
				}}
			});

    
    
    //root.expand();
    
});