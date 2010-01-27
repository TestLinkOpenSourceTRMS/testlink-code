/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009, TestLink community
 * @version CVS: $Id: ext_extensions.js,v 1.2 2010/01/27 08:14:22 erikeloff Exp $
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
 * 20100124 - eloff - BUGID3088 - added requireSessionAndSubmit()
 * 20100109 - eloff - inital commit of this file
 *                    BUGID 2800: CollapsiblePanel
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

/**
 * This function makes sure the user is still logged in (has a valid session)
 * before submitting a form.
 *
 * This is needed to avoid data loss if session has timed out in background.
 * If operates by making an ajax call to login.php?action=ajaxcheck and gets a
 * response whether the session is still valid or not. If the session is valid
 * submit the form. Otherwise show a login form in a popup to let the user
 * renew the session before submitting.
 *
 * Usage:
 * function validateForm(my_form) {
 *    // Do some validation
 *    // ...
 *    return Ext.ux.requireSessionAndSubmit(my_form);
 * }
 *
 * @see BUGID 1192, 1598, 2482, 2675, 2978
 */
Ext.ux.requireSessionAndSubmit = function(form) {
	var username_label, password_label, login_label;
	Ext.Ajax.request({
		url: 'login.php?action=ajaxcheck',
		method: 'GET',
		success: function(result, request) {
			obj = Ext.util.JSON.decode(result.responseText);
			// Get localized login form strings from ajax call.
			// This makes translation easier from scripts using this function
			username_label = obj['username_label'];
			password_label = obj['password_label'];
			login_label = obj['login_label'];
			if (obj["validSession"] == true) {
				form.submit();
			} else {
				showLoginForm();
			}
		},
		failure: function(result, request) {
			showLoginForm();
		}
	});

	function showLoginForm() {
		var loginForm = new Ext.form.FormPanel({
			url: 'login.php?action=ajaxlogin',
			defaultType: 'textfield',
			frame: true,
			items: [{
					fieldLabel: username_label,
					name: 'tl_login'
				},{
					fieldLabel: password_label,
					inputType:'password',
					name: 'tl_password'
				}]
		});
		var win = new Ext.Window({
			title: login_label,
			layout: 'form',
			width: 300,
			modal: true,
			items: [loginForm],
			defaultButton: 0,
			buttons:[{
				text: login_label,
				handler:function() {
					// Do the login
					loginForm.getForm().submit({
						method:'POST',
						success: function() {
							// If login is successful submit the original form
							form.submit();
						},
						failure: function(form, action) {
							obj = Ext.util.JSON.decode(action.response.responseText);
							Ext.Msg.alert('', obj.reason);
						}
					});
				}
			}]
		});
		win.show();
	}
	return false;
}
