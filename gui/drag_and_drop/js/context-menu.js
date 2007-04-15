DHTMLGoodies_menuModel = function()
{
	var menuItems;
	this.menuItems = new Array();
	
	
}

/************************************************************************************************************
*	DHTML menu model class
*
*	Created:						October, 30th, 2006
*	@class Purpose of class:		Saves menu item data
*			
*
*	Demos of this class:			demo-menu-strip.html
*
* 	Update log:
*
************************************************************************************************************/


/**
* @constructor
* @class Purpose of class:	Organize menu items for different menu widgets. demos of menus: (<a href="../../demos/demo-menu-strip.html" target="_blank">Demo</a>)
* @version 1.0
* @author	Alf Magne Kalleland(www.dhtmlgoodies.com)
*/


DHTMLGoodies_menuModel.prototype = {
	// {{{ addItem()
    /**
     *	Add separator (special type of menu item)
     *
     *  @param int id of menu item
     *  @param string itemText = text of menu item
     *  @param string itemIcon = file name of menu icon(in front of menu text. Path will be imagePath for the DHTMLSuite + file name)
     *  @param string url = Url of menu item
     *  @param int parent id of menu item     
     *  @param String jsFunction Name of javascript function to execute. It will replace the url param. The function with this name will be called and the element triggering the action will be 
     *					sent as argument. Name of the element which triggered the menu action may also be sent as a second argument. That depends on the widget. The context menu is an example where
     *					the element triggering the context menu is sent as second argument to this function.    
     *
     * @public	
     */			
	addItem : function(id,itemText,itemIcon,url,parentId,jsFunction)
	{
		this.menuItems[id] = new Array();
		this.menuItems[id]['id'] = id;
		this.menuItems[id]['itemText'] = itemText;
		this.menuItems[id]['itemIcon'] = itemIcon;
		this.menuItems[id]['url'] = url;
		this.menuItems[id]['parentId'] = parentId;
		this.menuItems[id]['separator'] = false;
		this.menuItems[id]['jsFunction'] = jsFunction;
		
	}	
	,
	// {{{ addSeparator()
    /**
     *	Add separator (special type of menu item)
     *
     *  @param int id of menu item
     *  @param int parent id of menu item
     * @public	
     */		
	addSeparator : function(id,parentId)
	{
		this.menuItems[id] = new Array();
		this.menuItems[id]['parentId'] = parentId;		
		this.menuItems[id]['separator'] = true;
	}	
	,
	// {{{ init()
    /**
     *	Initilizes the menu model. This method should be called when all items has been added to the model.
     *
     *
     * @public	
     */		
	init : function()
	{
		this.__getDepths();	
		
	}
	// }}}		
	,
	// {{{ __getDepths()
    /**
     *	Create variable for the depth of each menu item.
     * 	
     *
     * @private	
     */		
	getItems : function()
	{
		return this.menuItems;
	}
		
	,
	// {{{ __getDepths()
    /**
     *	Create variable for the depth of each menu item.
     * 	
     *
     * @private	
     */	
    __getDepths : function()
    {    	
    	for(var no in this.menuItems){
    		this.menuItems[no]['depth'] = 1;
    		if(this.menuItems[no]['parentId']){
    			this.menuItems[no]['depth'] = this.menuItems[this.menuItems[no]['parentId']]['depth']+1;
    		}    		
    	}    	
    }	
    ,
	// {{{ __hasSubs()
    /**
     *	Does a menu item have sub elements ?
     * 	
     *
     * @private	
     */	
	// }}}	
	__hasSubs : function(id)
	{
		for(var no in this.menuItems){	// Looping through menu items
			if(this.menuItems[no]['parentId']==id)return true;		
		}
		return false;	
	}
 

}



var referenceToDHTMLSuiteContextMenu;


DHTMLGoodies_contextMenu = function()
{
	var menuModels;
	var menuItems;	
	var menuObject;			// Reference to context menu div
	var layoutCSS;
	var menuUls;			// Array of <ul> elements
	var width;				// Width of context menu
	var srcElement;			// Reference to the element which triggered the context menu, i.e. the element which caused the context menu to be displayed.
	var indexCurrentlyDisplayedMenuModel;	// Index of currently displayed menu model.
	var imagePath;

	this.menuModels = new Array();
	this.menuObject = false;
	this.menuUls = new Array();
	this.width = 100;
	this.srcElement = false;
	this.indexCurrentlyDisplayedMenuModel = false;
	this.imagePath = '../../gui/drag_and_drop/images/';
	
	
}

DHTMLGoodies_contextMenu.prototype = 
{
	
	setWidth : function(newWidth)
	{
		this.width = newWidth;
	}
	// }}}
	,	
	// {{{ setLayoutCss()
    /**
     *	Add menu items
     *
     *  @param String cssFileName Name of css file 	
     *
     * @public	
     */		
	setLayoutCss : function(cssFileName)
	{
		this.layoutCSS = cssFileName;	
	}	
	// }}}	
	,	
	// {{{ attachToElement()
    /**
     *	Add menu items
     *
     *  @param Object HTML Element = Reference to html element
     *  @param String elementId = String id of element(optional). An alternative to HTML Element	
     *
     * @public	
     */		
	attachToElement : function(element,elementId,menuModel)
	{
		window.refToThisContextMenu = this;
		if(!element && elementId)element = document.getElementById(elementId);
		if(!element.id){
			element.id = 'context_menu' + Math.random();
			element.id = element.id.replace('.','');
		}
		this.menuModels[element.id] = menuModel;
		element.oncontextmenu = this.__displayContextMenu;
		//element.onmousedown = function() { window.refToThisContextMenu.__setReference(window.refToThisContextMenu); };
		document.documentElement.onclick = this.__hideContextMenu;
		
	}	
	// }}}
	,
	// {{{ __setReference()
    /**
     *	Creates a reference to current context menu object. (Note: This method should be deprecated as only one context menu object is needed)
     *
     *  @param Object context menu object = Reference to context menu object
     *
     * @private	
     */		
	__setReference : function(obj)
	{	
		referenceToDHTMLSuiteContextMenu = obj;	
	}
	,
	// {{{ __displayContextMenu()
    /**
     *	Displays the context menu
     *
     *  @param Event e
     *
     * @private	
     */		
	__displayContextMenu : function(e)
	{
		if(document.all)e = event;		
		var ref = referenceToDHTMLSuiteContextMenu;
		ref.srcElement = ref.getSrcElement(e);
		
		if(!ref.indexCurrentlyDisplayedMenuModel || ref.indexCurrentlyDisplayedMenuModel!=this.id){		
				
			if(ref.indexCurrentlyDisplayedMenuModel){
				ref.menuObject.innerHTML = '';				
			}else{
				ref.__createDivs();
			}
			ref.menuItems = ref.menuModels[this.id].getItems();			
			ref.__createMenuItems();	
		}
		ref.indexCurrentlyDisplayedMenuModel=this.id;
		
		ref.menuObject.style.left = (e.clientX + Math.max(document.body.scrollLeft,document.documentElement.scrollLeft)) + 'px';
		ref.menuObject.style.top = (e.clientY + Math.max(document.body.scrollTop,document.documentElement.scrollTop)) + 'px';
		ref.menuObject.style.display='block';
		return false;
			
	}
	// }}}
	,
	// {{{ __displayContextMenu()
    /**
     *	Add menu items
     *
     *  @param Event e
     *
     * @private	
     */		
	__hideContextMenu : function()
	{
		var ref = referenceToDHTMLSuiteContextMenu;
		if(ref.menuObject)ref.menuObject.style.display = 'none';
		
		
	}
	// }}}
	,
	// {{{ __createDivs()
    /**
     *	Creates general divs for the menu
     *
     *
     * @private	
     */		
	__createDivs : function()
	{
		this.menuObject = document.createElement('DIV');
		this.menuObject.className = 'DHTMLSuite_contextMenu';
		this.menuObject.style.backgroundImage = 'url(\'' + this.imagePath + 'context-menu-gradient.gif' + '\')';
		this.menuObject.style.backgroundRepeat = 'repeat-y';
		if(this.width)this.menuObject.style.width = this.width + 'px';
		document.body.appendChild(this.menuObject);
	}
	// }}}
	,
	
	// {{{ __mouseOver()
    /**
     *	Display mouse over effect when moving the mouse over a menu item
     *
     *
     * @private	
     */		
	__mouseOver : function()
	{
		this.className = 'DHTMLSuite_item_mouseover';	
		if(!document.all){
			this.style.backgroundPosition = 'left center';
		}
									
	}
	// }}}
	,
	// {{{ __mouseOut()
    /**
     *	Remove mouse over effect when moving the mouse away from a menu item
     *
     *
     * @private	
     */		
	__mouseOut : function()
	{
		this.className = '';
		if(!document.all){
			this.style.backgroundPosition = '1px center';
		}		
	}
	// }}}
	,
	// {{{ __createMenuItems()
    /**
     *	Create menu items
     *
     *
     * @private	
     */		
	__evalUrl : function()
	{
		var js = this.getAttribute('jsFunction');
		if(!js)js = this.jsFunction;
		if(js)eval(js);
		
	}
	// }}}
	,
	// {{{ __createMenuItems()
    /**
     *	Create menu items
     *
     *
     * @private	
     */		
	__createMenuItems : function()
	{
		window.refToContextMenu = this;	// Reference to menu strip object
		this.menuUls = new Array();
		for(var no in this.menuItems){	// Looping through menu items		
			if(!this.menuUls[0]){	// Create main ul element
				this.menuUls[0] = document.createElement('UL');
				this.menuObject.appendChild(this.menuUls[0]);
			}
			
			if(this.menuItems[no]['depth']==1){

				if(this.menuItems[no]['separator']){
					var li = document.createElement('DIV');
					li.className = 'DHTMLSuite_contextMenu_separator';
				}else{				
					var li = document.createElement('LI');
					if(this.menuItems[no]['jsFunction']){
						this.menuItems[no]['url'] = this.menuItems[no]['jsFunction'] + '(this,referenceToDHTMLSuiteContextMenu.srcElement)';
					}
					if(this.menuItems[no]['itemIcon']){
						li.style.backgroundImage = 'url(\'' + this.menuItems[no]['itemIcon'] + '\')';
						if(!document.all)li.style.backgroundPosition = '1px center';

					}
					
					if(this.menuItems[no]['url']){
						var url = this.menuItems[no]['url'] + '';
						var tmpUrl = url + '';
						li.setAttribute('jsFunction',url);
						li.jsFunction = url;
						li.onclick = this.__evalUrl;

					}
					
					li.innerHTML = '<a href="#" onclick="return false">' + this.menuItems[no]['itemText'] + '</a>';
					li.onmouseover = this.__mouseOver;
					li.onmouseout = this.__mouseOut;
				}				
				this.menuUls[0].appendChild(li);			
			}		
		}		
	}

	,
	
	// {{{ getSrcElement()
    /**
     *
     *  Returns a reference to the element which triggered an event.
     *	@param Event e = Event object
     *
     * 
     * @private
     */	       
    getSrcElement : function(e)
    {
    	var el;
		// Dropped on which element
		if (e.target) el = e.target;
			else if (e.srcElement) el = e.srcElement;
			if (el.nodeType == 3) // defeat Safari bug
				el = el.parentNode;
		return el;	
    }
    	
}