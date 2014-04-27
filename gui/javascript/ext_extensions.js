/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Erik Eloff
 * @copyright   2009,2014 TestLink community
 * @filesource  ext_extensions.js
 * @link        http://www.testlink.org
 * @since       1.9
 *
 *
 * Extensions and customizations to Ext-js classes.
 * In Ext-js , User eXtensions are placed in the Ext.ux namespace
 * @link http://www.extjs.com/learn/Extension:NameSpace
 *
 * @internal revisions:
 **/

/**
 * CollapsiblePanel is a class that extends an ordinary Ext.Panel. 
 * This extension is a panel with collapse/expand enabled and it stores 
 * the current state via Ext:s state manager.
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
 * Extends Ext.state.CookieProvider but uses json format for storing state.
 * Json format uses less space than the default format
 */
Ext.ux.JsonCookieProvider = Ext.extend(Ext.state.CookieProvider, {
	decodeValue: function (value) {
		try {
			return Ext.util.JSON.decode(value);
		}
		catch (e) {
			return null;
		}
	},
	encodeValue: function (value) {
		return Ext.util.JSON.encode(value);
	}
});


/**
 * Implementation that removes unneccessary state information. 
 * This object does only include grouping, sorting and 
 * hidden columns (not column width) in saved state. 
 * This is made to keep cookie size small.
 */
Ext.ux.SlimGridPanel = Ext.extend(Ext.grid.GridPanel, {
    /**
     * applyState() get the state to be applied from the cookie. But this
     * GridPanel may have another set of columns, we must sanitize the input
     * from the cookie by removing saved state that is not applicable on
     * this GridPanel.
     *
     * If group column from cookie is not present, then remove it before
     * applying. The same behaviour is implemented for sort.
     */
    applyState: function (state) {
        var config = this.colModel.config;
        // Remove group by state if that column is missing
        if (state.group) {
            var groupColAvailable = false;
            for (var i=0; i < config.length; i++) {
                if (config[i].dataIndex === state.group) {
                    groupColAvailable = true;
                }
            }
            if (!groupColAvailable) {
                delete state.group;
            }
        }
        // Remove sort column state if that column is missing
        if (state.sort) {
            var sortColAvailable = false;
            for (var i=0; i < config.length; i++) {
                if (config[i].dataIndex === state.sort.field) {
                    sortColAvailable = true;
                }
            }
            if (!sortColAvailable) {
                delete state.sort;
            }
        }
		Ext.ux.SlimGridPanel.superclass.applyState.call(this, state);
    },
    /**
     * getState() is overidden to remove redundant state information to keep
     * cookie size small.
     */
	getState : function(){
		var obj = Ext.ux.SlimGridPanel.superclass.getState.call(this);
		for (var idx = 0; idx < obj.columns.length; idx++) {
			// delete info on visible columns
			if (!obj.columns[idx].hidden) {
				delete obj.columns[idx];
			}
		}
		return obj;
	}
});

/**
 * TableToolbar is a class used to populate the toolbar for ext tables used
 * in TestLink.
 *
 * It will create the following buttons:
 *		- expand/collapse groups
 *		- show all columns
 *		- reset to default state
 *		- refresh button (reloads page)
 *
 *
 *	<code>
 *	tbar = new Ext.ux.Toolbar({
 *		table_id: 'tc_table_results_tc',
 *		showExpandCollapseGroupsButton: true,
 *		labels: {
 *			expand_collapse_groups: 'Expand/collapse groups'
 *		}
 *	});
 *	</code>
 */
Ext.ux.TableToolbar = Ext.extend(Ext.Toolbar, {
	constructor: function (config) {
		Ext.apply(this, {
			table_id: null,
			showExpandCollapseGroupsButton: true,
			showAllColumnsButton: true,
			showDefaultStateButton: true,
			showRefreshButton: true
		});

		Ext.ux.TableToolbar.superclass.constructor.apply(this, arguments);

		Ext.applyIf(this.labels, {
				button_refresh: "localize",
				default_state: "localize",
				expand_collapse_groups: "localize",
				show_all_columns: "localize",
				show_all_columns_tooltip: "localize"
			});


		if (this.table_id === null) {
			throw "table_id not set in config";
		}
		var table_id = this.table_id;
		if (this.showExpandCollapseGroupsButton) {
			this.add({
				text: this.labels.expand_collapse_groups,
				last_state: 'expanded',
				iconCls: 'x-group-by-icon',
				handler: function () {
					var g = grid[table_id];
					if (this.last_state == 'expanded') {
						g.getView().collapseAllGroups();
						this.last_state = 'collapsed';
					} else {
						g.getView().expandAllGroups()
						this.last_state = 'expanded';
					}
				}
			});
		}

		if (this.showAllColumnsButton) {
			this.add({
				text: this.labels.show_all_columns,
				tooltip: this.labels.show_all_columns_tooltip,
				tooltipType: 'title',
				iconCls: 'x-cols-icon',
				handler: function (button, state) {
					var my_grid = grid[table_id];
					var my_store = store[table_id];
					var cm = my_grid.getColumnModel();
					for (var i=0;i<cm.getColumnCount();i++) {
						//do not show grouped column if hideGroupedColumn is true
						var dataIndex = cm.config[i].dataIndex;
						if (my_grid.getView().hideGroupedColumn === false ||
							my_store.groupField != dataIndex) {
							cm.setHidden(i, false);
						}
					}
				}
			});
		}

		if (this.showDefaultStateButton) {
			this.add({
				text: this.labels.default_state,
				iconCls: 'tbar-default-state',
				handler: function (button, state) {
					Ext.state.Manager.clear(grid[table_id].getStateId());
					window.location = window.location;
				}
			});
		}

		if (this.showRefreshButton) {
			this.add({
				text: this.labels.button_refresh,
				iconCls: 'x-tbar-loading',
				handler: function (button, state) {
					window.location = window.location;
				}
			});
		}
	}
});

/**
 * This function makes sure the user is still logged in (has a valid session)
 * before submitting a form.
 *
 * Needed to avoid data loss if session has timed out in background.
 * It operates by making an ajax call to login.php?action=ajaxcheck and 
 * gets a response whether the session is still valid or not. 
 * If the session is valid submit the form. 
 * Otherwise show a login form in a popup to let the user renew the session before submitting.
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
			timeout_info = obj['timeout_info'];
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
			title: timeout_info + '<br>&nbsp;',
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

/**
 * Allows list filtering on status value. Status is a special column type
 * and its value is a JS-object. The objects 'value' attribute (p/n/b/n) is
 * used in filtering.
 * (The standard ListFilter uses the raw object itself treating it like a string.)
 *
 * Introduced to fix BUGID 4125
 * @author Eloff
 */
Ext.ux.grid.filter.StatusFilter = Ext.extend(Ext.ux.grid.filter.ListFilter, {
    validateRecord: function (record) {
        var status = record.get(this.dataIndex).value;
        return ( this.getValue().indexOf(status) > -1);
    }
});

/**
 * Allows list filtering on priority.
 * (The standard ListFilter uses the raw object itself treating it like a string.)
 */
Ext.ux.grid.filter.PriorityFilter = Ext.extend(Ext.ux.grid.filter.ListFilter, {
    validateRecord: function (record) {
        var priority = record.get(this.dataIndex);
        return ( this.getValue().indexOf(priority) > -1);
    }
});

Ext.ux.grid.filter.ImportanceFilter = Ext.extend(Ext.ux.grid.filter.ListFilter, {
    validateRecord: function (record) {
        var item = record.get(this.dataIndex);
        return ( this.getValue().indexOf(item) > -1);
    }
});


Ext.ux.grid.filter.ListSimpleMatchFilter = Ext.extend(Ext.ux.grid.filter.ListFilter, {
    validateRecord: function (record) {
        var value = record.get(this.dataIndex);
        var filterArray= this.getValue();
        var match = false;
        for (var idx = 0; idx < filterArray.length; idx++) {
            if (value.search(filterArray[idx]) > -1) {
                match = true;
                break;
            }
        }
        return (match);
    }
});