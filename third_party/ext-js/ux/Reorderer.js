/*!
 * Ext JS Library 3.2.1
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
/**
 * @class Ext.ux.Reorderer
 * @extends Object
 * Generic base class for handling reordering of items. This base class must be extended to provide the
 * actual reordering functionality - the base class just sets up events and abstract logic functions.
 * It will fire events and set defaults, deferring the actual reordering to a doReorder implementation.
 * See Ext.ux.TabReorderer for an example.
 */
Ext.ux.Reorderer = Ext.extend(Object, {
    /**
     * @property defaults
     * @type Object
     * Object containing default values for plugin configuration details. These can be overridden when
     * constructing the plugin
     */
    defaults: {
        /**
         * @cfg animate
         * @type Boolean
         * If set to true, the rearranging of the toolbar items is animated
         */
        animate: true,
        
        /**
         * @cfg animationDuration
         * @type Number
         * The duration of the animation used to move other toolbar items out of the way
         */
        animationDuration: 0.2,
        
        /**
         * @cfg defaultReorderable
         * @type Boolean
         * True to make every toolbar draggable unless reorderable is specifically set to false.
         * This defaults to false
         */
        defaultReorderable: false
    },
    
    /**
     * Creates the plugin instance, applies defaults
     * @constructor
     * @param {Object} config Optional config object
     */
    constructor: function(config) {
        Ext.apply(this, config || {}, this.defaults);
    },
    
    /**
     * Initializes the plugin, stores a reference to the target 
     * @param {Mixed} target The target component which contains the reorderable items
     */
    init: function(target) {
        /**
         * @property target
         * @type Ext.Component
         * Reference to the target component which contains the reorderable items
         */
        this.target = target;
        
        this.initEvents();
        
        var items = this.getItems();
        for (var i=0; i < items.length; i++) {
            this.createIfReorderable(items[i]);
        }
    },
    
    /**
     * Reorders the items in the target component according to the given mapping object. Example:
     * this.reorder({
     *     1: 5,
     *     3: 2
     * });
     * Would move the item at index 1 to index 5, and the item at index 3 to index 2
     * @param {Object} mappings Object containing current item index as key and new index as property
     */
    reorder: function(mappings) {
        var target = this.target;
        
        if (target.fireEvent('before-reorder', mappings, target, this) !== false) {
            this.doReorder(mappings);
            
            target.fireEvent('reorder', mappings, target, this);
        }
    },
    
    /**
     * Abstract function to perform the actual reordering. This MUST be overridden in a subclass
     * @param {Object} mappings Mappings of the old item indexes to new item indexes
     */
    doReorder: function(paramName) {
        throw new Error("doReorder must be implemented in the Ext.ux.Reorderer subclass");
    },
    
    /**
     * Should create and return an Ext.dd.DD for the given item. This MUST be overridden in a subclass
     * @param {Mixed} item The item to create a DD for. This could be a TabPanel tab, a Toolbar button, etc
     * @return {Ext.dd.DD} The DD for the given item
     */
    createItemDD: function(item) {
        throw new Error("createItemDD must be implemented in the Ext.ux.Reorderer subclass");
    },
    
    /**
     * Sets up the given Toolbar item as a draggable
     * @param {Mixed} button The item to make draggable (usually an Ext.Button instance)
     */
    createItemDD: function(button) {
        var el   = button.getEl(),
            id   = el.id,
            tbar = this.target,
            me   = this;
        
        button.dd = new Ext.dd.DD(el, undefined, {
            isTarget: false
        });
        
        button.dd.constrainTo(tbar.getEl());
        button.dd.setYConstraint(0, 0, 0);
        
        Ext.apply(button.dd, {
            b4StartDrag: function() {       
                this.startPosition = el.getXY();
                
                //bump up the z index of the button being dragged but keep a reference to the original
                this.startZIndex = el.getStyle('zIndex');
                el.setStyle('zIndex', 10000);
                
                button.suspendEvents();
            },
            
            onDrag: function(e) {
                //calculate the button's index within the toolbar and its current midpoint
                var buttonX  = el.getXY()[0],
                    deltaX   = buttonX - this.startPosition[0],
                    items    = tbar.items.items,
                    oldIndex = items.indexOf(button),
                    newIndex;
                
                //find which item in the toolbar the midpoint is currently over
                for (var index = 0; index < items.length; index++) {
                    var item = items[index];
                    
                    if (item.reorderable && item.id != button.id) {
                        //find the midpoint of the button
                        var box        = item.getEl().getBox(),
                            midpoint   = (me.buttonXCache[item.id] || box.x) + (box.width / 2),
                            movedLeft  = oldIndex > index && deltaX < 0 && buttonX < midpoint,
                            movedRight = oldIndex < index && deltaX > 0 && (buttonX + el.getWidth()) > midpoint;
                        
                        if (movedLeft || movedRight) {
                            me[movedLeft ? 'onMovedLeft' : 'onMovedRight'](button, index, oldIndex);
                            break;
                        }                        
                    }
                }
            },
            
            /**
             * After the drag has been completed, make sure the button being dragged makes it back to
             * the correct location and resets its z index
             */
            endDrag: function() {
                //we need to update the cache here for cases where the button was dragged but its
                //position in the toolbar did not change
                me.updateButtonXCache();
                
                el.moveTo(me.buttonXCache[button.id], undefined, {
                    duration: me.animationDuration,
                    scope   : this,
                    callback: function() {
                        button.resumeEvents();
                        
                        tbar.fireEvent('reordered', button, tbar);
                    }
                });
                
                el.setStyle('zIndex', this.startZIndex);
            }
        });
    },
    
    /**
     * @private
     * Creates a DD instance for a given item if it is reorderable
     * @param {Mixed} item The item
     */
    createIfReorderable: function(item) {
        if (this.defaultReorderable && item.reorderable == undefined) item.reorderable = true;
        
        if (item.reorderable) {
            if (item.rendered) {
                this.createItemDD(item);                
            } else {
                item.on('render', this.createItemDD.createDelegate(this, [item]), this, {single: true});
            }
        }
    },
    
    /**
     * Returns an array of items which will be made draggable. This defaults to the contents of this.target.items,
     * but can be overridden - e.g. for TabPanels
     * @return {Array} The array of items which will be made draggable
     */
    getItems: function() {
        return this.target.items.items;
    },
    
    /**
     * Adds before-reorder and reorder events to the target component
     */
    initEvents: function() {
        this.target.addEvents(
          /**
           * @event before-reorder
           * Fires before a reorder occurs. Return false to cancel
           * @param {Object} mappings Mappings of the old item indexes to new item indexes
           * @param {Mixed} component The target component
           * @param {Ext.ux.TabReorderer} this The plugin instance
           */
          'before-reorder',
          
          /**
           * @event reorder
           * Fires after a reorder has occured.
           * @param {Object} mappings Mappings of the old item indexes to the new item indexes
           * @param {Mixed} component The target component
           * @param {Ext.ux.TabReorderer} this The plugin instance
           */
          'reorder'
        );
    }
});