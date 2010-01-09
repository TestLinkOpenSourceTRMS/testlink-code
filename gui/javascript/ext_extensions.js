/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009, TestLink community
 * @version CVS: $Id: ext_extensions.js,v 1.1 2010/01/09 13:41:32 erikeloff Exp $
 * @filesource
http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/gui/javascript/ext_extensions.js
 * @link http://www.teamst.org
 * @since 1.9
 *
 *
 * This files includes extensions and customizations to to Ext-js classes.
 * In Ext-js User eXtensions are placed in the Ext.ux namespace
 * @link http://www.extjs.com/learn/Extension:NameSpace
 *
 * @internal revisions:
 * 20100109 - eloff -   inital commit of this file
 *                      BUGID 2800: CollapsiblePanel
 **/

/**
 * CollapsiblePanel is a class that extends an ordinary Ext.Panel. This extension
 * is a panel with collapse/expand enabled and it stores the current state via
 * Ext:s state manager.
 * @link http://www.extjs.com/deploy/dev/docs/?class=Ext.state.Manager
 *
 * Inspired by:
 * http://golfadept-journey.blogspot.com/2008/05/extjs-and-saving-state.html
 *
 * Example usage:
 * JavaScript
 * <code>
 * Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
 *
 * panel = new Ext.CollapsiblePanel({
 *    id: 'tl_exec_filter_panel',     // name of cookie
 *    title: 'Filter panel',
 *    applyTo: 'filter_panel'        // the div to transform into a panel
 * });
 *
 * </code>
 *
 * HTML
 * <code>
 * <div id="filter_panel">
 *     <div class="x-panel-body">This is content</div>
 * </div>
 * </code>
 */
Ext.ux.CollapsiblePanel = Ext.extend(Ext.Panel, {
	applyState: function(state) {this.collapsed = state.collapsed;},
	getState: function() {return{collapsed:this.collapsed}},
	animCollapse: false,
	collapsible: true,
	stateEvents: ['collapse', 'expand'],
	titleCollapse: true
});
