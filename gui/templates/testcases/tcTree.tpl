{* 
   TestLink Open Source Project - http://testlink.sourceforge.net/ 
   $Id: tcTree.tpl,v 1.24 2010/08/09 19:49:19 franciscom Exp $ 
   Purpose: smarty template - show test specification tree menu 

rev: 
     20100428 - asimon - BUGID 3301 - removed old filter/settings form/panel and replaced
                         them with new included template inc_tc_filter_panel.tpl,
                         also added filtering by custom fields
     20091210 - franciscom - exec type filter 
     20080831 - franciscom - treeCfg
                             manage testlink_node_type, useBeforeMoveNode
     20080805 - franciscom - BUGID 1656
     20080525 - franciscom - use only ext js tree type.
                             no change to configure a different tree menu type 
     20070217 - franciscom - added test suite filter
                             changes to form method to allow automatic refresh
                             without browser warning
*}
{lang_get var="labels"
          s="caption_nav_filter_settings,testsuite,do_auto_update,keywords_filter_help,
             button_update_tree,no_tc_spec_av,keyword,execution_type"}


    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}

	{* BUGID 3301 *}
	{* Ext Collapsible Panel *}
	<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
	{literal}
	<script type="text/javascript">
		treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
		           loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
		Ext.onReady(function() {
			Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	
			// Use a collapsible panel for filter settings
			// and place a help icon in ther header
			var settingsPanel = new Ext.ux.CollapsiblePanel({
				id: 'tl_exec_filter',
				applyTo: 'settings_panel',
				tools: [{
					id: 'help',
					handler: function(event, toolEl, panel) {
						show_help(help_localized_text);
					}
				}]
			});
			var filtersPanel = new Ext.ux.CollapsiblePanel({
				id: 'tl_exec_settings',
				applyTo: 'filter_panel'
			});
		});
	</script>
	{/literal}	
	
    {if $gui->ajaxTree->loader == ''}
        {literal}
        <script type="text/javascript">
        treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
                   loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
        </script>
        {/literal}

        <script type="text/javascript">
        treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
        treeCfg.root_id={$gui->ajaxTree->root_node->id};
        treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
        treeCfg.children={$gui->ajaxTree->children};
        </script>
        <script type="text/javascript" src='gui/javascript/execTree.js'></script>
    
    {else}
        {literal}
        <script type="text/javascript">
        treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
                   root_testlink_node_type:'',useBeforeMoveNode:false,
                   loader:"", enableDD:false, dragDropBackEndUrl:''};
        </script>
        {/literal}
        
        <script type="text/javascript">
	        treeCfg.loader='{$gui->ajaxTree->loader}';
	        treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape}';
	        treeCfg.root_id={$gui->ajaxTree->root_node->id};
	        treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
	        treeCfg.enableDD='{$gui->ajaxTree->dragDrop->enabled}';
	        treeCfg.dragDropBackEndUrl='{$gui->ajaxTree->dragDrop->BackEndUrl}';
	        treeCfg.cookiePrefix='{$gui->ajaxTree->cookiePrefix}';
	        treeCfg.root_testlink_node_type='{$gui->ajaxTree->root_node->testlink_node_type}';
	        treeCfg.useBeforeMoveNode='{$gui->ajaxTree->dragDrop->useBeforeMoveNode}';
	        </script>
        <script type="text/javascript" src='gui/javascript/treebyloader.js'>
        </script>
    {/if}


{* BUGID 3301 - js include file for simpler code, filter refactoring/redesign *}
{include file='inc_filter_panel_js.tpl'}

{* 
 * !!!!! IMPORTANT !!!!!
 * Above included file closes <head> tag and opens <body>, so this is not done here.
 *}


<h1 class="title">{$gui->treeHeader}</h1>

{* BUGID 3301: include file for filter panel *}
{include file='inc_filter_panel.tpl'}

<div id="tree" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>

</body>
</html>